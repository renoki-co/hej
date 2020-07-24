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

        $social = $user->socials->first();

        $expected = [
            'id' => 1,
            "model_type" => "RenokiCo\Hej\Test\Models\User",
            "model_id" => "1",
            "provider" => "github",
            "provider_id" => "1234",
            "provider_nickname" => "rennokki",
            "provider_name" => "rennokki",
            "provider_email" => "test@test.com",
            "provider_avatar" => "https://avatars2.githubusercontent.com/u/21983456?v=4",
            "provider_data" => [
              "login" => "rennokki",
              "id" => 1234,
              "avatar_url" => "https://avatars2.githubusercontent.com/u/21983456?v=4",
              "url" => "https://api.github.com/users/rennokki",
              "email" => "test@test.com",
              "name" => "rennokki",
            ],
            "token" => "token_123",
            "token_secret" => null,
            "refresh_token" => null,
            "token_expires_at" => null,
        ];

        $existingData = $social->setHidden([])->toArray();

        foreach ($expected as $key => $value) {
            $this->assertEquals(
                $existingData[$key], $value
            );
        }
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

        $social = $user->socials->first();

        $expected = [
            'id' => 1,
            "model_type" => "RenokiCo\Hej\Test\Models\User",
            "model_id" => "1",
            "provider" => "github",
            "provider_id" => "1234",
            "provider_nickname" => "rennokki",
            "provider_name" => "rennokki",
            "provider_email" => "test@test.com",
            "provider_avatar" => "https://avatars2.githubusercontent.com/u/21983456?v=4",
            "provider_data" => [
              "login" => "rennokki",
              "id" => 1234,
              "avatar_url" => "https://avatars2.githubusercontent.com/u/21983456?v=4",
              "url" => "https://api.github.com/users/rennokki",
              "email" => "test@test.com",
              "name" => "rennokki",
            ],
            "token" => "token_123",
            "token_secret" => null,
            "refresh_token" => null,
            "token_expires_at" => null,
        ];

        $existingData = $social->setHidden([])->toArray();

        foreach ($expected as $key => $value) {
            $this->assertEquals(
                $existingData[$key], $value
            );
        }
    }
}
