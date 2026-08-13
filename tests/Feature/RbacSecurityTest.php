<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Applicant']);
        Role::create(['name' => 'HR Administrator']);
        Role::create(['name' => 'Super Admin']);
    }

    public function test_guest_cannot_access_hr_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_applicant_cannot_access_hr_dashboard(): void
    {
        $applicantUser = User::factory()->create();
        $applicantUser->assignRole('Applicant');

        $response = $this->actingAs($applicantUser)->get('/dashboard');
        $response->assertStatus(403);
    }

    public function test_applicant_cannot_access_admin_user_management(): void
    {
        $applicantUser = User::factory()->create();
        $applicantUser->assignRole('Applicant');

        $response = $this->actingAs($applicantUser)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_hr_admin_can_access_hr_dashboard(): void
    {
        $hrUser = User::factory()->create();
        $hrUser->assignRole('HR Administrator');

        $response = $this->actingAs($hrUser)->get('/dashboard');
        $response->assertStatus(200);
    }
}
