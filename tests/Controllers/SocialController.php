<?php

namespace RenokiCo\Hej\Test\Controllers;

use Illuminate\Http\Request;
use RenokiCo\Hej\Http\Controllers\SocialController as BaseSocialController;

class SocialController extends BaseSocialController
{
    /**
     * Get the Authenticatable model data to fill on register.
     * When the user gets created, it will receive these parameters
     * in the `::create()` method.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return array
     */
    protected function getAuthenticatableFillableDataOnRegister(Request $request, string $provider, $providerUser): array
    {
        return [
            'name' => $providerUser->getName(),
            'email' => $providerUser->getEmail(),
            'email_verified_at' => now(),
            'password' => mt_rand(1, 3),
        ];
    }

    /**
     * Get the Socialite direct instance that will redirect
     * the user to the right provider OAuth page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return mixed
     */
    protected function getSocialiteRedirect(Request $request, string $provider)
    {
        return $this->socialite
            ->driver($provider)
            ->scopes(['admin:repo_hook', 'gist'])
            ->redirect();
    }

    /**
     * Get the Socialite User instance that will be
     * given after the OAuth authorization passes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return mixed
     */
    protected function getSocialiteUser(Request $request, string $provider)
    {
        return $this->socialite
            ->driver($provider)
            ->user();
    }

    /**
     * Run logic after the registration process.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Database\Eloquent\Model  $social
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return void
     */
    protected function registered($model, $social, $providerUser)
    {
        //
    }

    /**
     * Run logic after the login process.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Database\Eloquent\Model  $social
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return void
     */
    protected function authenticated($model, $social, $providerUser)
    {
        //
    }
}
