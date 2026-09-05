<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_documented_accounts_and_employee_profiles_are_seeded(): void
    {
        $this->assertDatabaseHas('users', ['email' => 'admin@hiraya.com']);
        $this->assertDatabaseHas('users', ['email' => 'hr@hiraya.com']);
        $this->assertDatabaseHas('users', ['email' => 'recruitment@hiraya.com']);
        $this->assertDatabaseHas('users', ['email' => 'carlos@gmail.com']);

        $this->assertSame('Super Admin', User::where('email', 'admin@hiraya.com')->firstOrFail()->roles->first()->name);
        $this->assertSame('Employee', User::where('email', 'carlos@gmail.com')->firstOrFail()->roles->first()->name);

        $profile = EmployeeProfile::where('employee_id', 'EMP-2026-001')->firstOrFail();
        $this->assertSame('carlos@gmail.com', $profile->user->email);
        $this->assertSame('Senior Travel Consultant', $profile->jobPosition->title);
        $this->assertSame('Sales & Travel Consulting', $profile->department->name);
        $this->assertSame('regular', $profile->employment_status);
    }

    public function test_documented_applications_preserve_positions_and_stages(): void
    {
        $expected = [
            'juan.delacruz@example.com' => ['Senior Travel Consultant', 'submitted'],
            'maria.santos@example.com' => ['International Tour Coordinator', 'shortlisted'],
            'carlos.reyes@example.com' => ['Flight Ticketing & GDS Specialist', 'for_interview'],
            'ana.garcia@example.com' => ['Visa & Passport Processing Officer', 'assessed'],
            'pedro.aquino@example.com' => ['Destination Marketing Specialist', 'recommended'],
            'liza.soberano@example.com' => ['Lead Tour Guide & Guest Experience Lead', 'screening'],
            'marco.polo@example.com' => ['Senior Travel Consultant', 'shortlisted'],
            'beatriz.alonzo@example.com' => ['International Tour Coordinator', 'for_interview'],
            'gabriel.concepcion@example.com' => ['Flight Ticketing & GDS Specialist', 'recommended'],
            'patricia.evangelista@example.com' => ['Destination Marketing Specialist', 'screening'],
            'daniel.padilla@example.com' => ['Visa & Passport Processing Officer', 'submitted'],
        ];

        foreach ($expected as $email => [$position, $status]) {
            $application = Application::whereHas('applicant', fn ($query) => $query->where('email', $email))
                ->with('jobPosting.jobPosition')
                ->firstOrFail();

            $this->assertSame($position, $application->jobPosting->jobPosition->title);
            $this->assertSame($status, $application->status);
        }

        $this->assertSame(11, Applicant::count());
        $this->assertSame(11, Application::count());
    }

    public function test_seed_is_idempotent_and_major_roles_can_log_in(): void
    {
        $counts = [
            'users' => User::count(),
            'applicants' => Applicant::count(),
            'applications' => Application::count(),
            'employee_profiles' => EmployeeProfile::count(),
        ];

        $this->seed('CompleteDemoSeeder');

        $this->assertSame($counts['users'], User::count());
        $this->assertSame($counts['applicants'], Applicant::count());
        $this->assertSame($counts['applications'], Application::count());
        $this->assertSame($counts['employee_profiles'], EmployeeProfile::count());

        $this->post('/login', ['email' => 'hr@hiraya.com', 'password' => 'password123'])
            ->assertRedirect('/dashboard');
        $this->post('/logout');
        $this->post('/login', ['email' => 'carlos@gmail.com', 'password' => 'password123'])
            ->assertRedirect('/employee/dashboard');
        $this->post('/logout');
        $this->post('/login', ['email' => 'juan.delacruz@example.com', 'password' => 'password123'])
            ->assertRedirect('/applicant/dashboard');
    }
}
