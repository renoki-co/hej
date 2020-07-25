<?php

namespace RenokiCo\Hej\Test;

use RenokiCo\Hej\Test\Models\User;

class ProviderTest extends TestCase
{
    public function test_redirect_should_redirect_to_provider_website()
    {
        $this
            ->call('GET', route('redirect', ['provider' => 'github']))
            ->assertStatus(302);
    }

    public function test_should_not_redirect_or_callback_for_unwhitelisted_providers()
    {
        $this
            ->call('GET', route('redirect', ['provider' => 'facebook']))
            ->assertRedirectedToRoute('home');

        $this
            ->call('GET', route('callback', ['provider' => 'facebook']))
            ->assertRedirectedToRoute('home');

        $this
            ->call('GET', route('unlink', ['provider' => 'facebook']))
            ->assertRedirectedToRoute('home');
    }

    public function test_register_if_not_registered()
    {
        $this->mockSocialiteFacade(
            \Laravel\Socialite\Two\GithubProvider::class
        );

        $this
            ->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertNotNull(
            $user = User::whereEmail('test@test.com')->first()
        );

        $this->assertCount(
            1, $user->socials()->get()
        );

        $social = $user->socials()->first();

        $expected = [
            'id' => 1,
            'model_type' => "RenokiCo\Hej\Test\Models\User",
            'model_id' => '1',
            'provider' => 'github',
            'provider_id' => '1234',
            'provider_nickname' => 'rennokki',
            'provider_name' => 'rennokki',
            'provider_email' => 'test@test.com',
            'provider_avatar' => 'https://avatars2.githubusercontent.com/u/21983456?v=4',
            'provider_data' => [
                'login' => 'rennokki',
                'id' => 1234,
                'avatar_url' => 'https://avatars2.githubusercontent.com/u/21983456?v=4',
                'url' => 'https://api.github.com/users/rennokki',
                'email' => 'test@test.com',
                'name' => 'rennokki',
            ],
            'token' => 'token_123',
            'token_secret' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
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

        $this
            ->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $this
            ->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertNotNull(
            $user = User::whereEmail('test@test.com')->first()
        );

        $this->assertCount(
            1, $user->socials()->get()
        );

        $social = $user->socials()->first();

        $expected = [
            'id' => 1,
            'model_type' => "RenokiCo\Hej\Test\Models\User",
            'model_id' => '1',
            'provider' => 'github',
            'provider_id' => '1234',
            'provider_nickname' => 'rennokki',
            'provider_name' => 'rennokki',
            'provider_email' => 'test@test.com',
            'provider_avatar' => 'https://avatars2.githubusercontent.com/u/21983456?v=4',
            'provider_data' => [
                'login' => 'rennokki',
                'id' => 1234,
                'avatar_url' => 'https://avatars2.githubusercontent.com/u/21983456?v=4',
                'url' => 'https://api.github.com/users/rennokki',
                'email' => 'test@test.com',
                'name' => 'rennokki',
            ],
            'token' => 'token_123',
            'token_secret' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ];

        $existingData = $social->setHidden([])->toArray();

        foreach ($expected as $key => $value) {
            $this->assertEquals(
                $existingData[$key], $value
            );
        }
    }

    public function test_register_with_existent_email()
    {
        $this->mockSocialiteFacade(
            \Laravel\Socialite\Two\GithubProvider::class
        );

        $socialModel = config('hej.models.social');

        $user = factory(User::class)->create(['email' => 'test@test.com']);

        $this->assertCount(0, $user->socials()->get());

        $this->json('GET', route('callback', ['provider' => 'github']))
            ->assertRedirectedToRoute('register');

        $this->assertCount(0, $user->socials()->get());

        $this->assertEquals(0, $socialModel::count());
    }

    public function test_link_unused_social_account()
    {
        $user = factory(User::class)->create(['email' => 'test@test.com']);

        $this
            ->actingAs($user)
            ->call('GET', route('link', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertEquals(
            1,
            session('hej_github_1')
        );

        $this->mockSocialiteFacade(
            \Laravel\Socialite\Two\GithubProvider::class
        );

        $this->assertCount(
            0, $user->socials()->get()
        );

        $this
            ->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertNull(session('hej_github_1'));

        $this->assertCount(
            1, $user->socials()->get()
        );


        $this->assertCount(1, User::all());

        $social = $user->socials()->first();

        $expected = [
            'id' => 1,
            'model_type' => "RenokiCo\Hej\Test\Models\User",
            'model_id' => '1',
            'provider' => 'github',
            'provider_id' => '1234',
            'provider_nickname' => 'rennokki',
            'provider_name' => 'rennokki',
            'provider_email' => 'test@test.com',
            'provider_avatar' => 'https://avatars2.githubusercontent.com/u/21983456?v=4',
            'provider_data' => [
                'login' => 'rennokki',
                'id' => 1234,
                'avatar_url' => 'https://avatars2.githubusercontent.com/u/21983456?v=4',
                'url' => 'https://api.github.com/users/rennokki',
                'email' => 'test@test.com',
                'name' => 'rennokki',
            ],
            'token' => 'token_123',
            'token_secret' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ];

        $existingData = $social->setHidden([])->toArray();

        foreach ($expected as $key => $value) {
            $this->assertEquals(
                $existingData[$key], $value
            );
        }
    }

    public function test_link_already_linked_social_account()
    {
        $user = factory(User::class)->create(['email' => 'test@test.com']);
        $user2 = factory(User::class)->create(['email' => 'test2@test.com']);

        $this
            ->actingAs($user)
            ->call('GET', route('link', ['provider' => 'github']))
            ->assertStatus(302);

        $this
            ->actingAs($user2)
            ->call('GET', route('link', ['provider' => 'github']))
            ->assertStatus(302);

        $this->mockSocialiteFacade(
            \Laravel\Socialite\Two\GithubProvider::class
        );

        $this
            ->actingAs($user)
            ->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $response = $this
            ->actingAs($user2)
            ->call('GET', route('callback', ['provider' => 'github']))
            ->assertRedirectedToRoute('home');

        $session = $response->getSession();

        $this->assertEquals(
            'Your Github account is already linked to another account.',
            $session->get('social')
        );
    }

    public function test_unlink_account()
    {
        $user = factory(User::class)->create(['email' => 'test@test.com']);

        $this
            ->actingAs($user)
            ->call('GET', route('link', ['provider' => 'github']))
            ->assertStatus(302);

        $this->mockSocialiteFacade(
            \Laravel\Socialite\Two\GithubProvider::class
        );

        $this
            ->actingAs($user)
            ->call('GET', route('callback', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertCount(
            1, $user->socials()->get()
        );

        $this
            ->actingAs($user)
            ->call('GET', route('unlink', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertCount(
            0, $user->socials()->get()
        );
    }

    public function test_link_already_linked_social_account_by_same_user()
    {
        $user = factory(User::class)->create(['email' => 'test@test.com']);

        $this
            ->actingAs($user)
            ->call('GET', route('link', ['provider' => 'github']))
            ->assertStatus(302);

        $this->assertEquals(
            1,
            session('hej_github_1')
        );

        $this->mockSocialiteFacade(
            \Laravel\Socialite\Two\GithubProvider::class
        );

        $this->assertCount(
            0, $user->socials()->get()
        );

        $user->socials()->create([
            'provider' => 'github',
            'provider_id' => '123',
        ]);

        // Calling it again wont link it.
        $response = $this
            ->call('GET', route('callback', ['provider' => 'github']))
            ->assertRedirectedToRoute('home');

        $session = $response->getSession();

        $this->assertEquals(
            'You already have a Github account linked.',
            $session->get('social')
        );
    }
}
