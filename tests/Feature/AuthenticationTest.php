<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'DepartmentSeeder']);
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
    }

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_with_valid_credentials()
    {
        $user = User::where('email', 'hr@recruit.test')->first();

        $response = $this->post(route('login'), [
            'email' => 'hr@recruit.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $this->post(route('login'), [
            'email' => 'hr@recruit.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_applicant_registration()
    {
        $response = $this->post(route('register'), [
            'name' => 'New Applicant',
            'email' => 'new.applicant@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'first_name' => 'New',
            'last_name' => 'Applicant',
            'phone' => '09190000000',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'new.applicant@example.com',
        ]);

        $user = User::where('email', 'new.applicant@example.com')->first();
        $this->assertTrue($user->hasRole('Applicant'));
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::where('email', 'officer@recruit.test')->first();

        $response = $this->actingAs($user)->post(route('logout'));
        $this->assertGuest();
    }
}
