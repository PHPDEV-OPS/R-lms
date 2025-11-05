<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_register()
    {
        $this->startSession();
        
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->from(route('register'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('register.post'), $userData + ['_token' => session()->token()]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $this->startSession();
        
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $response = $this->from(route('login'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('login.post'), $loginData + ['_token' => session()->token()]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $this->startSession();
        
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $loginData = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->from(route('login'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('login.post'), $loginData + ['_token' => session()->token()]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout()
    {
        $this->startSession();
        
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('logout'), ['_token' => session()->token()]);

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
    }

    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->get(route('admin.courses.index'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)
            ->get(route('admin.courses.index'));

        $response->assertStatus(403);
    }

    public function test_registration_requires_valid_data()
    {
        $this->startSession();
        
        $response = $this->from(route('register'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('register.post'), ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_registration_requires_unique_email()
    {
        $this->startSession();
        
        User::factory()->create(['email' => 'john@example.com']);

        $userData = [
            'name' => 'Jane Doe',
            'email' => 'john@example.com', // Duplicate email
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->from(route('register'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('register.post'), $userData + ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['email']);
    }

    public function test_password_must_be_confirmed_during_registration()
    {
        $this->startSession();
        
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->from(route('register'))
            ->withHeader('X-CSRF-TOKEN', session()->token())
            ->post(route('register.post'), $userData + ['_token' => session()->token()]);

        $response->assertRedirect()
            ->assertSessionHasErrors(['password']);
    }
}