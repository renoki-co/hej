Hej! - a Socialite authentication flow boilerplate
==================================================

![](images/hej.png)

![CI](https://github.com/renoki-co/hej/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/renoki-co/hej/branch/master/graph/badge.svg)](https://codecov.io/gh/renoki-co/hej/branch/master)
[![StyleCI](https://github.styleci.io/repos/282196287/shield?branch=master)](https://github.styleci.io/repos/282196287)
[![Latest Stable Version](https://poser.pugx.org/renoki-co/hej/v/stable)](https://packagist.org/packages/renoki-co/hej)
[![Total Downloads](https://poser.pugx.org/renoki-co/hej/downloads)](https://packagist.org/packages/renoki-co/hej)
[![Monthly Downloads](https://poser.pugx.org/renoki-co/hej/d/monthly)](https://packagist.org/packages/renoki-co/hej)
[![License](https://poser.pugx.org/renoki-co/hej/license)](https://packagist.org/packages/renoki-co/hej)

Hej! is a simple authentication boilerplate for Socialite. It helps you handle the logic so you won't have to do it each and every time for all your projects.

Hej! comes pre-packages with migrations and a controller that you can modify to customize your needs.

## 🤝 Supporting

Renoki Co. on GitHub aims on bringing a lot of open source, MIT-licensed projects and helpful projects to the world. Developing and maintaining projects everyday is a harsh work and tho, we love it.

If you are using your application in your day-to-day job, on presentation demos, hobby projects or even school projects, spread some kind words about our work or sponsor our work. Kind words will touch our chakras and vibe, while the sponsorships will keep the open source projects alive.

## 🚀 Installation

You can install the package via composer:

```bash
composer require renoki-co/hej
```

Publish the config:

```bash
$ php artisan vendor:publish --provider="RenokiCo\Hej\HejServiceProvider" --tag="config"
```

Publish the migrations:

```bash
$ php artisan vendor:publish --provider="RenokiCo\Hej\HejServiceProvider" --tag="migrations"
```

## 🙌 Usage

For the user (or any Authenticatable instance) you should add the `HasSocialAccounts` trait and the `Sociable` interface:

```php
use RenokiCo\Hej\Concerns\HasSocialAccounts;
use RenokiCo\Hej\Contracts\Sociable;

class User extends Authenticatable implements Sociable
{
    use HasSocialAccounts;

    //
}
```

Out-of-the-box, it works with any Laravel application.

After you have configured Socialite, the only thing to do is to point your desired redirect and callback paths to the package controller:

```php
Route::get('/social/{provider}/redirect', 'RenokiCo\Hej\Http\Controllers\SocialController@redirect')
Route::get('/social/{provider}/callback', 'RenokiCo\Hej\Http\Controllers\SocialController@callback');
```

The paths can be any, as long as they contain a first parameter which is going to be the provider you try to authenticate with. For example, accessing this link will redirect to Github:

```
https://my-link.com/social/github/redirect
```

## Extending Controllers

Hej! is really flexible and does a lot of things in the background to register or login using Socialite.

However, you need to extend the controller and you will then be able to replace some methods to customize the flow.

```php
use RenokiCo\Hej\Http\Controllers\SocialController;

class MySocialController extends SocialController
{
    //
}
```

Then you should point the routes to the new controller.

## Provider whitelisting

Due to the fact that the endpoints are opened to get any provider, you can whitelist the Socialite provider names that can be used:

```php
/**
 * Whitelist social providers to be used.
 *
 * @var array
 */
protected static $allowedSocialiteProviders = [
    //
];
```

For example, allowing only Facebook and Github should look like this:

```php
protected static $allowedSocialiteProviders = [
    'facebook', 'github',
];
```

If one of the providers accessed via the URL is not whitelisted, a simple redirect is done automatically. However, you can replace it and redirect to your custom path:

```php
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

    session()->flash('social', "The authentication with {$provider} failed!");

    return redirect(route('home'));
}
```

## Custom Redirect & User retrieval

With Socialite, you can use `->redirect()` to redirect the user and `->user()` to retrieve it. You can customize the instances by replacing `getSocialiteRedirect` and `getSocialiteUser`.

Here is the default configuration:

```php
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
```

For example, wanting to generate the redirect and get the user within an API can be done:

```php
protected function getSocialiteRedirect(Request $request, string $provider)
{
    return $this->socialite
        ->driver($provider)
        ->stateless()
        ->redirect();
}

protected function getSocialiteUser(Request $request, string $provider)
{
    return $this->socialite
        ->driver($provider)
        ->stateless()
        ->user();
}
```

## Custom Authenticatable

When trying to login or register, the package uses the default `App\User` as defined in `config/hej.php`. However, this can easily be replaced at the request level:

```php
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
```

For example, you can change the model to authenticate as for different Socialite providers:

```php
public function getAuthenticatable(Request $request, string $provider)
{
    if ($provider === 'medium') {
        return \App\AnotherUser::class;
    }

    return config('hej.default_authenticatable');
}
```

**Keep in mind that the model should also use the Trait and the Interface and be `Authenticatable`.**

## Register new user

When the Social account that the user logged in is not registered within the database, it creates a new authenticatable model, but in order to do this, it should fill it with data.

By default, it fills in using Socialite Provider's given data and sets a random 64-letter word password:

```php
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
        'password' => Hash::make(Str::random(64)),
    ];
}
```

## Filling the Social table

After registration or login, the Socialite data gets created or updated, either the user existed or not.

By default, it's recommended to not get overwritten, excepting for the fact you want to change the table structure and extend the `Social` model that is also set in `config/hej.php`.

```php
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
```

## Authentication Callback

After the business authentication logic finished, it's time to authenticate the model. To do so, your `authenticateModel` method should return a redirect response:

This is how the default method looks like:

```php
/**
 * Handle the user login and redirection.
 *
 * @param  \Illuminate\Database\Eloquent\Model  $model
 * @return \Illuminate\Http\RedirectResponse
 */
protected function authenticateModel($model)
{
    Auth::login($model);

    session()->flash('social', 'Welcome back in your account!');

    return redirect(route('home'));
}
```

## Final Callbacks

Right before the user is authenticated and redirected, there are two callbacks that trigger and you can replace them for some custom logic:

```php
/**
 * Handle the callback after the registration process.
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
 * Handle the callback after the login process.
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
```

## 🐛 Testing

``` bash
vendor/bin/phpunit
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒  Security

If you discover any security related issues, please email alex@renoki.org instead of using the issue tracker.

## 🎉 Credits

- [Alex Renoki](https://github.com/rennokki)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
