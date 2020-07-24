<?php

namespace RenokiCo\Hej\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Contracts\Factory as Socialite;
use RenokiCo\Hej\Concerns\HandlesSocialRequests;

class SocialController extends Controller
{
    use HandlesSocialRequests;

    /**
     * The Socialite factory instance.
     *
     * @var Laravel\Socialite\Contracts\Factory
     */
    protected $socialite;

    /**
     * Initialize the controller.
     *
     * @param  \Laravel\Socialite\Contracts\Factory  $socialite
     * @return void
     */
    public function __construct(Socialite $socialite)
    {
        $this->socialite = $socialite;
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
     * Get the model to login (or register).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return string
     */
    public function getAuthenticatable(Request $request, string $provider)
    {
        return config('hej.default_authenticatable');
    }

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
    protected function getRegisterData(Request $request, string $provider, $providerUser): array
    {
        return [
            'name' => $providerUser->getName(),
            'email' => $providerUser->getEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('test'),
        ];
    }

    /**
     * Get the Social model data to fill on register or login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return array
     */
    protected function getSocialData(Request $request, string $provider, $model, $providerUser): array
    {
        return [
            'provider_nickname' => $providerUser->getNickname(),
            'provider_name' => $providerUser->getName(),
            'provider_email' => $providerUser->getEmail(),
            'provider_avatar' => $providerUser->getAvatar(),
            'token' => $providerUser->token,
            'token_secret' => $providerUser->tokenSecret ?? null,
            'refresh_token' => $providerUser->refreshToken ?? null,
            'token_expires_at' => isset($providerUser->expiresIn) ? now()->addSeconds($providerUser->expiresIn) : null,
            'provider_data' => $providerUser->getRaw(),
        ];
    }

    /**
     * Login the user.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return Illuminate\Http\Redirectresponse
     */
    protected function authenticateModel($model)
    {
        Auth::login($model);

        session()->flash('social', 'Welcome back in your account!');

        return redirect(route('home'));
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
