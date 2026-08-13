<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\OfferLetter;
use App\Models\Onboarding;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferAndOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_hr_can_create_offer_letter()
    {
        $hrUser = User::where('email', 'hr@recruit.test')->first();
        $application = Application::where('status', '!=', 'hired')->first();

        $response = $this->actingAs($hrUser)->post(route('recruitment.offers.store'), [
            'application_id' => $application->id,
            'job_posting_id' => $application->job_posting_id,
            'salary' => 95000,
            'start_date' => now()->addWeeks(3)->format('Y-m-d'),
            'employment_type' => 'full_time',
            'terms' => 'Standard probation terms.',
            'benefits' => 'Health insurance, annual leaves.',
        ]);

        $this->assertDatabaseHas('offer_letters', [
            'application_id' => $application->id,
            'salary' => 95000,
        ]);
    }

    public function test_applicant_can_accept_offer_letter()
    {
        $applicantUser = User::where('email', 'juan.delacruz@example.com')->first();
        $application = Application::where('applicant_id', $applicantUser->applicant->id)->first();
        $posting = JobPosting::first();

        $offer = OfferLetter::create([
            'application_id' => $application->id,
            'job_posting_id' => $posting->id,
            'offer_number' => 'OFF-TEST-001',
            'salary' => 90000,
            'start_date' => now()->addMonth(),
            'employment_type' => 'full_time',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($applicantUser)->post(route('applicant.offers.accept', ['offer' => $offer->id]));
        $response->assertRedirect();

        $this->assertDatabaseHas('offer_letters', [
            'id' => $offer->id,
            'status' => 'accepted',
        ]);
    }

    public function test_hr_can_initiate_onboarding_and_create_employee_profile()
    {
        $hrUser = User::where('email', 'hr@recruit.test')->first();
        $hiredApplication = Application::first();
        $hiredApplication->update(['status' => 'hired']);

        $response = $this->actingAs($hrUser)->post(route('recruitment.onboarding.store'), [
            'application_id' => $hiredApplication->id,
            'start_date' => now()->addWeek()->format('Y-m-d'),
            'orientation_date' => now()->addWeek()->addDays(1)->format('Y-m-d'),
            'notes' => 'Prepare workstation and accounts.',
        ]);

        $this->assertDatabaseHas('onboarding', [
            'application_id' => $hiredApplication->id,
        ]);

        $onboarding = Onboarding::where('application_id', $hiredApplication->id)->first();

        // Convert to Employee Profile — controller auto-generates from applicant data
        $response = $this->actingAs($hrUser)
            ->post(route('recruitment.onboarding.employee-profile', ['onboarding' => $onboarding->id]));

        $response->assertRedirect();

        // Assert that an employee profile was created for this applicant
        $this->assertDatabaseHas('employee_profiles', [
            'applicant_id' => $hiredApplication->applicant->id,
        ]);
    }

    public function test_hr_can_manually_update_onboarding_progress_without_forcing_100_percent()
    {
        $hrUser = User::where('email', 'hr@recruit.test')->first();
        $hiredApplication = Application::first();
        $hiredApplication->update(['status' => 'hired']);

        $onboarding = Onboarding::create([
            'application_id' => $hiredApplication->id,
            'assigned_to' => $hrUser->id,
            'start_date' => now()->format('Y-m-d'),
            'orientation_date' => now()->format('Y-m-d'),
            'status' => 'in_progress',
            'progress' => 20,
        ]);

        // Manually update progress to 50% with status in_progress
        $response = $this->actingAs($hrUser)->put(route('recruitment.onboarding.update', $onboarding), [
            'start_date' => now()->format('Y-m-d'),
            'orientation_date' => now()->format('Y-m-d'),
            'progress' => 50,
            'status' => 'in_progress',
        ]);

        $response->assertRedirect();

        $onboarding->refresh();
        $this->assertEquals(50, $onboarding->progress);
        $this->assertEquals('in_progress', $onboarding->status);
    }
}
