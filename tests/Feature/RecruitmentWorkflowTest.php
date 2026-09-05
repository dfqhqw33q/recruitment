<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Department;
use App\Models\Interview;
use App\Models\InterviewAssessment;
use App\Models\JobPosition;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_hr_can_view_recruitment_dashboard()
    {
        $hrUser = User::where('email', 'hr@hiraya.com')->first();

        $response = $this->actingAs($hrUser)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_hr_can_create_new_job_posting()
    {
        $hrUser = User::where('email', 'hr@hiraya.com')->first();
        $dept = Department::first();
        $pos = JobPosition::first();

        $response = $this->actingAs($hrUser)->post(route('recruitment.job-postings.store'), [
            'department_id' => $dept->id,
            'job_position_id' => $pos->id,
            'title' => 'Lead AI Developer',
            'employment_type' => 'full_time',
            'location' => 'Quezon City',
            'vacancies_count' => 2,
            'salary_range' => 'PHP 100,000 - 150,000',
            'summary' => 'Responsible for building AI systems.',
            'description' => 'Responsible for building AI systems.',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('job_postings', [
            'title' => 'Lead AI Developer',
        ]);
    }

    public function test_applicant_can_apply_for_job()
    {
        $applicantUser = User::where('email', 'juan.delacruz@example.com')->first();
        $posting = JobPosting::first();

        $response = $this->actingAs($applicantUser)->post(route('applicant.apply', $posting), [
            'cover_letter' => 'I am eager to apply for this job.',
        ]);

        $this->assertDatabaseHas('applications', [
            'applicant_id' => $applicantUser->applicant->id,
            'job_posting_id' => $posting->id,
        ]);
    }

    public function test_hr_can_shortlist_application()
    {
        $hrUser = User::where('email', 'hr@hiraya.com')->first();
        $application = Application::first();

        $response = $this->actingAs($hrUser)->post(route('recruitment.applications.shortlist', ['application' => $application->id]), [
            'notes' => 'Qualified candidate for interview round.',
        ]);

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'shortlisted',
        ]);
    }

    public function test_hr_can_schedule_interview_and_submit_assessment()
    {
        $hrUser = User::where('email', 'hr@hiraya.com')->first();
        $application = Application::first();

        // Schedule Interview
        $response = $this->actingAs($hrUser)->post(route('recruitment.interviews.store'), [
            'application_id' => $application->id,
            'interviewer_id' => $hrUser->id,
            'scheduled_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'type' => 'technical',
            'round' => 1,
            'duration_minutes' => 45,
            'location' => 'Virtual Zoom Meeting',
            'notes' => 'Focus on backend algorithms.',
        ]);

        $this->assertDatabaseHas('interviews', [
            'application_id' => $application->id,
            'type' => 'technical',
        ]);

        $interview = Interview::where('application_id', $application->id)->first();

        // Submit Assessment
        $response = $this->actingAs($hrUser)->post(route('recruitment.interviews.assessment.store', ['interview' => $interview->id]), [
            'communication_score' => 90,
            'technical_score' => 95,
            'experience_score' => 88,
            'cultural_fit_score' => 92,
            'recommendation' => 'hire',
            'strengths' => 'Excellent problem solver',
            'weaknesses' => 'None observed',
            'comments' => 'Strong recommendation for hire.',
        ]);

        $this->assertDatabaseHas('interview_assessments', [
            'interview_id' => $interview->id,
            'communication_score' => 90,
            'technical_score' => 95,
        ]);
    }
}
