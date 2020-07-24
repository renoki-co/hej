<?php

namespace RenokiCo\Hej\Concerns;

use Illuminate\Http\Request;

trait HandlesSocialRequests
{
    /**
     * Whitelist social providers to be used.
     *
     * @var array
     */
    protected static $allowedSocialiteProviders = [
        //
    ];

    /**
     * Redirect the user to the OAuth portal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request, string $provider)
    {
        if ($this->rejectProvider($provider)) {
            return $this->providerRejected($request, $provider);
        }

        return $this->getSocialiteRedirect($request, $provider);
    }

    /**
     * Process the user callback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request, string $provider)
    {
        if ($this->rejectProvider($provider)) {
            return $this->providerRejected($request, $provider);
        }

        $providerUser = $this->getSocialiteUser($request, $provider);

        // If the Social is attached to any authenticatable model,
        // then jump off and login.

        if ($model = $this->getModelBySocialId($provider, $providerUser->getId())) {
            $this->updateSocialInstance($request, $provider, $model, $providerUser);

            $this->authenticated(
                $model, $this->getSocialById($provider, $providerUser->getId()), $providerUser
            );

            return $this->authenticateModel($model);
        }

        // Otherwise, create a new Authenticatable model
        // and attach a Social instance to it.

        $authenticatable = $this->getAuthenticatable($request, $provider);

        $model = $authenticatable::create(
            $this->getRegisterData(
                $request, $provider, $providerUser
            )
        );

        $social = $model->socials()->create([
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
        ]);

        $this->updateSocialInstance($request, $social, $model, $providerUser);

        $this->registered($model, $social, $providerUser);

        return $this->authenticateModel($model);
    }

    /**
     * Get the user by using a social provider's ID.
     *
     * @param  string  $provider
     * @param  mixed  $id
     * @return null|\Illuminate\Eloquent\Database\Model
     */
    protected function getModelBySocialId(string $provider, $id)
    {
        $social = $this->getSocialById($provider, $id);

        return $social ? $social->model : null;
    }

    /**
     * Get a Social instance by Social and ID.
     *
     * @param  string  $provider
     * @param  mixed  $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getSocialById(string $provider, $id)
    {
        $socialModel = config('hej.models.social');

        return $socialModel::whereProvider($provider)
            ->whereProviderId($id)
            ->first();
    }

    /**
     * Update a social account using a Socialite
     * authentication instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|\Illuminate\Database\Eloquent\Model  $provider
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return bool
     */
    protected function updateSocialInstance(Request $request, $provider, $model, $providerUser)
    {
        $social = $provider instanceof Social
            ? $provider
            : $this->getSocialById($provider, $providerUser->getId());

        if (! $social) {
            return false;
        }

        $social->update(
            $this->getSocialData(
                $request, $provider, $model, $providerUser,
            )
        );

        return true;
    }

    /**
     * Wether the provider is rejected by the current
     * whitelist status.
     *
     * @param  string  $provider
     * @return bool
     */
    protected function rejectProvider(string $provider): bool
    {
        if (static::$allowedSocialiteProviders === ['*']) {
            return true;
        }

        return ! in_array($provider, static::$allowedSocialiteProviders);
    }
}
