<?php

namespace RenokiCo\Hej\Concerns;

use Illuminate\Http\Request;
use RenokiCo\Hej\Social;

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
     * Redirect to link a social account
     * for the current authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function link(Request $request, string $provider)
    {
        if ($this->rejectProvider($provider)) {
            return $this->providerRejected($request, $provider);
        }

        $model = $request->user();

        if ($model->hasSocial($provider)) {
            return $this->providerAlreadyLinked(
                $request, $provider, $model
            );
        }

        $sessionKey = $this->getLinkSessionKey($request, $provider, $model);

        session()->put($sessionKey, $model->getKey());

        return $this->getSocialiteRedirect($request, $provider);
    }

    /**
     * Try to unlink a social account
     * for the current authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unlink(Request $request, string $provider)
    {
        if ($this->rejectProvider($provider)) {
            return $this->providerRejected($request, $provider);
        }

        $model = $request->user();

        if ($social = $model->getSocial($provider)) {
            $social->delete();
        }

        $this->unlinked($request, $model, $provider);

        return $this->redirectAfterUnlink($request, $model, $provider);
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

        // If the user tried to link the account, handle different logic.

        $sessionKey = $this->getLinkSessionKey($request, $provider, $request->user());

        if ($authenticatableKey = session()->pull($sessionKey)) {
            return $this->linkCallback($request, $provider, $authenticatableKey, $providerUser);
        }

        // If the Social is attached to any authenticatable model,
        // then jump off and login.

        if ($model = $this->getModelBySocialId($request, $provider, $providerUser->getId())) {
            $social = $this->updateSocialInstance($request, $provider, $model, $providerUser);

            $this->authenticated(
                $request, $model, $social, $providerUser
            );

            return $this->authenticateModel($model);
        }

        // Otherwise, create a new Authenticatable model
        // and attach a Social instance to it.

        $authenticatable = $this->getAuthenticatable($request, $provider);

        if ($this->emailAlreadyExists($provider, $authenticatable, $providerUser)) {
            return $this->duplicateEmail($request, $provider, $providerUser);
        }

        $model = $authenticatable::create(
            $this->getRegisterData(
                $request, $provider, $providerUser
            )
        );

        $social = $model->socials()->create([
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
        ]);

        $social = $this->updateSocialInstance($request, $social, $model, $providerUser);

        $this->registered($request, $model, $social, $providerUser);

        return $this->authenticateModel($model);
    }

    /**
     * Handle the link callback to attach to an authenticatable ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  string  $authenticatableId
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function linkCallback(Request $request, string $provider, string $authenticatableId, $providerUser)
    {
        $authenticatableModel = $this->getAuthenticatable($request, $provider);

        $model = $authenticatableModel::find($authenticatableId);

        // Check if user has already a Social account with the provider.

        if ($model->hasSocial($provider)) {
            return $this->providerAlreadyLinked(
                $request, $provider, $model
            );
        }

        // Make sure that there are not two same authenticatables
        // that are linked to same social account.

        if ($this->getSocialById($request, $provider, $providerUser->getId())) {
            return $this->providerAlreadyLinkedByAnotherAuthenticatable(
                $request, $provider, $model, $providerUser
            );
        }

        $social = $model->socials()->create([
            'provider' => $provider,
            'provider_id' => $providerUser->getId(),
        ]);

        $social = $this->updateSocialInstance(
            $request, $social, $model, $providerUser
        );

        $this->linked($request, $model, $social, $providerUser);

        return $this->redirectAfterLink(
            $request, $model, $social, $providerUser
        );
    }

    /**
     * Get the user by using a social provider's ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  mixed  $id
     * @return null|\Illuminate\Eloquent\Database\Model
     */
    protected function getModelBySocialId(Request $request, string $provider, $id)
    {
        $social = $this->getSocialById($request, $provider, $id);

        return $social ? $social->model : null;
    }

    /**
     * Get a Social instance by Social and ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  mixed  $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected function getSocialById(Request $request, string $provider, $id)
    {
        $socialModel = config('hej.models.social');

        return $socialModel::whereProvider($provider)
            ->whereProviderId($id)
            ->whereModelType($this->getAuthenticatable($request, $provider))
            ->first();
    }

    /**
     * Check if the E-Mail address already exists.
     *
     * @param  string  $provider
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return bool
     */
    protected function emailAlreadyExists(string $provider, $model, $providerUser): bool
    {
        if (! $model = $model::whereEmail($providerUser->getEmail())->first()) {
            return false;
        }

        return ! $model->socials()
            ->whereProvider($provider)
            ->whereProviderId($providerUser->getId())
            ->exists();
    }

    /**
     * Update a social account using a Socialite
     * authentication instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|\Illuminate\Database\Eloquent\Model  $provider
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function updateSocialInstance(Request $request, $provider, $model, $providerUser)
    {
        $social = $provider instanceof Social
            ? $provider
            : $this->getSocialById($request, $provider, $providerUser->getId());

        if (! $social) {
            return false;
        }

        $social->update(
            $this->getSocialData(
                $request, $provider, $model, $providerUser,
            )
        );

        return $social;
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
