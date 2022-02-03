<?php

namespace RenokiCo\Hej\Test;

use Laravel\Socialite\Contracts\Factory as Socialite;
use Orchestra\Testbench\BrowserKit\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->resetDatabase();

        $this->loadLaravelMigrations(['--database' => 'sqlite']);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->withFactories(__DIR__.'/database/factories');
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app)
    {
        return [
            \Laravel\Socialite\SocialiteServiceProvider::class,
            \RenokiCo\Hej\HejServiceProvider::class,
            TestServiceProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => __DIR__.'/database.sqlite',
            'prefix'   => '',
        ]);
        $app['config']->set('auth.providers.users.model', Models\User::class);
        $app['config']->set('app.key', 'wslxrEFGWY6GfGhvN9L3wH3KSRJQQpBD');
        $app['config']->set('hej.default_authenticatable', Models\User::class);
        $app['config']->set('services.github', [
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'redirect',
        ]);
    }

    /**
     * Reset the database.
     *
     * @return void
     */
    protected function resetDatabase()
    {
        file_put_contents(__DIR__.'/database.sqlite', null);
    }

    /**
     * Mock the Socialite Factory with a specific provider.
     *
     * @param  string  $provider
     * @return void
     */
    public function mockSocialiteFacade(string $provider)
    {
        $socialiteUser = $this->createMock(\Laravel\Socialite\Two\User::class);

        $methodCallers = [
            'getId' => 1234,
            'getNickname' => 'rennokki',
            'getName' => 'rennokki',
            'getEmail' => 'test@test.com',
            'getAvatar' => 'https://avatars2.githubusercontent.com/u/21983456?v=4',
        ];

        foreach ($methodCallers as $method => $return) {
            $socialiteUser->expects($this->any())
                ->method($method)
                ->willReturn($return);
        }

        $socialiteUser->expects($this->any())
            ->method('getRaw')
            ->willReturn([
                'login' => 'rennokki',
                'id' => 1234,
                'avatar_url' => 'https://avatars2.githubusercontent.com/u/21983456?v=4',
                'url' => 'https://api.github.com/users/rennokki',
                'email' => 'test@test.com',
                'name' => 'rennokki',
            ]);

        $socialiteUser->token = 'token_123';
        $socialiteUser->refreshToken = null;
        $socialiteUser->expiresIn = null;

        $provider = $this->createMock($provider);

        $provider->expects($this->any())
            ->method('user')
            ->willReturn($socialiteUser);

        $stub = $this->createMock(Socialite::class);

        $stub->expects($this->any())
            ->method('driver')
            ->willReturn($provider);

        $this->app->instance(Socialite::class, $stub);
    }
}
