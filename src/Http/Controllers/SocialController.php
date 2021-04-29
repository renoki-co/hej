<?php

namespace RenokiCo\Hej\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
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
     * @return \Laravel\Socialite\AbstractUser
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
     * Get the key to store into session the authenticatable
     * primary key to be checked on returning from OAuth.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return string
     */
    public function getLinkSessionKey(Request $request, string $provider, $model): string
    {
        return $model ? "hej_{$provider}_{$model->getKey()}" : '';
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
            'password' => Hash::make(Str::random(64)),
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
        ];
    }

    /**
     * Handle the user login and redirection.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function authenticateModel($model)
    {
        Auth::login($model);

        Session::flash('social', 'Welcome back in your account!');

        return $this->redirectToAfterAuthentication($model);
    }

    /**
     * Handle the callback when a provider gets rejected.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function providerRejected(Request $request, $provider)
    {
        $provider = ucfirst($provider);

        Session::flash('social', "The authentication with {$provider} failed!");

        return $this->redirectToAfterProviderIsRejected($request, $provider);
    }

    /**
     * Handle the callback when the user's social account
     * E-Mail address is already used.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function duplicateEmail(Request $request, $provider, $providerUser)
    {
        $provider = ucfirst($provider);

        Session::flash(
            'social', "The E-Mail address associated with your {$provider} account is already used."
        );

        return $this->redirectToAfterDuplicateEmail($request, $provider, $providerUser);
    }

    /**
     * Handle the callback when the user tries
     * to link a social account when it
     * already has one, with the same provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function providerAlreadyLinked(Request $request, $provider, $model)
    {
        $provider = ucfirst($provider);

        Session::flash(
            'social', "You already have a {$provider} account linked."
        );

        return $this->redirectToAfterProviderIsAlreadyLinked($request, $provider, $model);
    }

    /**
     * Handle the callback when the user tries
     * to link a social account that is already existent.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function providerAlreadyLinkedByAnotherAuthenticatable(Request $request, $provider, $model, $providerUser)
    {
        $provider = ucfirst($provider);

        Session::flash(
            'social', "Your {$provider} account is already linked to another account."
        );

        return $this->redirectToAfterProviderAlreadyLinkedByAnotherAuthenticatable(
            $request, $provider, $model, $providerUser
        );
    }

    /**
     * Handle the user redirect after linking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Database\Eloquent\Model  $social
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectAfterLink(Request $request, $model, $social, $providerUser)
    {
        $provider = ucfirst($social->provider);

        Session::flash('social', "The {$provider} account has been linked to your account.");

        return $this->redirectToAfterLink($request, $model, $social, $providerUser);
    }

    /**
     * Handle the user redirect after unlinking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectAfterUnlink(Request $request, $model, string $provider)
    {
        $provider = ucfirst($provider);

        Session::flash('social', "The {$provider} account has been unlinked.");

        return $this->redirectToAfterUnlink($request, $model, $provider);
    }

    /**
     * Handle the callback after the registration process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Database\Eloquent\Model  $social
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return void
     */
    protected function registered(Request $request, $model, $social, $providerUser)
    {
        //
    }

    /**
     * Handle the callback after the login process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Database\Eloquent\Model  $social
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return void
     */
    protected function authenticated(Request $request, $model, $social, $providerUser)
    {
        //
    }

    /**
     * Handle the callback after the linking process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Database\Eloquent\Model  $social
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return void
     */
    protected function linked(Request $request, $model, $social, $providerUser)
    {
        //
    }

    /**
     * Handle the callback after the unlink process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $provider
     * @return void
     */
    protected function unlinked(Request $request, $model, string $provider)
    {
        //
    }

    /**
     * Specify the redirection route after successful authentication.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToAfterAuthentication($model)
    {
        return Redirect::route('home');
    }

    /**
     * Specify the redirection route to let the users know
     * the authentication using the selected provider was rejected.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToAfterProviderIsRejected(Request $request, $provider)
    {
        return Redirect::route('home');
    }

    /**
     * Specify the redirection route to let the users know
     * the E-Mail address used with this social account is
     * already existent as another account. This is most often
     * occuring during registrations with Social accounts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToAfterDuplicateEmail(Request $request, $provider, $providerUser)
    {
        return Redirect::route('home');
    }

    /**
     * Specify the redirection route to let the users know
     * the social account is already associated with their account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToAfterProviderIsAlreadyLinked(Request $request, $provider, $model)
    {
        return Redirect::route('home');
    }

    /**
     * Specify the redirection route to let the users know
     * the social account is associated with another account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $provider
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToAfterProviderAlreadyLinkedByAnotherAuthenticatable(
        Request $request, $provider, $model, $providerUser
    ) {
        return Redirect::route('home');
    }

    /**
     * Specify the redirection route to let the users know
     * they linked the social account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Illuminate\Database\Eloquent\Model  $social
     * @param  \Laravel\Socialite\AbstractUser  $providerUser
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToAfterLink(Request $request, $model, $social, $providerUser)
    {
        return Redirect::route('home');
    }

    /**
     * Specify the redirection route to let the users
     * they have unlinked the social account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToAfterUnlink(Request $request, $model, string $provider)
    {
        return Redirect::route('home');
    }
}
