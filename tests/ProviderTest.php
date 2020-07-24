<?php

namespace RenokiCo\Hej\Test;

use Illuminate\Contracts\Session\Session;
use Laravel\Socialite\Contracts\Factory as Socialite;
use Mockery as m;
use RenokiCo\Hej\Test\Models\User;

class ProviderTest extends TestCase
{
    public function test_redirect_should_redirect_to_provider_website()
    {
        $response = $this
            ->call('GET', route('redirect', ['provider' => 'github']))
            ->assertStatus(302);
    }

    public function test_redirect_should_not_redirect_for_unwhitelisted_providers()
    {
        $response = $this
            ->call('GET', route('redirect', ['provider' => 'facebook']))
            ->assertRedirectedToRoute('home');
    }

    public function test_register_if_not_registered()
    {
        $this->mockSocialiteFacade(
            \Laravel\Socialite\Two\GithubProvider::class
        );

        $response = $this->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertNotNull(
            $user = User::whereEmail('test@test.com')->first()
        );

        $this->assertCount(
            1, $user->socials
        );
    }

    public function test_login_if_already_registered()
    {
        $this->mockSocialiteFacade(
            \Laravel\Socialite\Two\GithubProvider::class
        );

        $response = $this->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $response = $this->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertNotNull(
            $user = User::whereEmail('test@test.com')->first()
        );

        $this->assertCount(
            1, $user->socials
        );
    }
}
