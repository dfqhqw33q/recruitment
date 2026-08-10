<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\JobPosition;
use App\Models\JobPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'DepartmentSeeder']);
    }

    public function test_public_home_page_loads_successfully()
    {
        $response = $this->get(route('public.home'));
        $response->assertStatus(200);
        $response->assertViewIs('public.home');
    }

    public function test_public_about_and_destinations_pages()
    {
        $this->get(route('public.about'))->assertStatus(200);
        $this->get(route('public.destinations'))->assertStatus(200);
        $this->get(route('public.tours'))->assertStatus(200);
    }

    public function test_public_careers_page_lists_published_jobs()
    {
        $dept = Department::first();
        $pos = JobPosition::create([
            'department_id' => $dept->id,
            'title' => 'Software Developer',
            'code' => 'DEV-001',
            'status' => 'active',
        ]);

        $posting = JobPosting::create([
            'department_id' => $dept->id,
            'job_position_id' => $pos->id,
            'posting_number' => 'JOB-2026-001',
            'title' => 'Senior Laravel Engineer',
            'slug' => 'senior-laravel-engineer',
            'employment_type' => 'full_time',
            'location' => 'Manila, Philippines',
            'salary_range' => 'PHP 80,000 - 120,000',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->get(route('public.careers'));
        $response->assertStatus(200);
        $response->assertSee('Senior Laravel Engineer');
    }

    public function test_public_contact_form_submission()
    {
        $response = $this->post(route('public.contact.submit'), [
            'name' => 'John Defense',
            'email' => 'john.defense@example.com',
            'subject' => 'Inquiry regarding opening',
            'message' => 'Hello, I am interested in joining your team.',
        ]);

        $response->assertRedirect(route('public.contact'));
        $response->assertSessionHas('success');
    }
}
