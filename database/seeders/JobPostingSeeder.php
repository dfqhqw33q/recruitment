<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\JobPosition;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JobPostingSeeder extends Seeder
{
    public function run(): void
    {
        $hrAdmin = User::where('email', 'hr@recruit.test')->first();

        $postings = [
            [
                'title' => 'Senior Travel Consultant',
                'position_code' => 'STC01',
                'dept_code' => 'STC',
                'summary' => 'Design custom leisure and corporate travel itineraries for clients, managing domestic and international flight, hotel, and tour packages.',
                'required_skills' => ['Flight Booking', 'Itinerary Planning', 'Customer Advisory', 'Amadeus GDS'],
                'preferred_skills' => ['Corporate Sales', 'Visa Knowledge', 'Destination Expertise'],
                'vacancies_count' => 3,
                'employment_type' => 'full_time',
                'location' => 'Makati City, Philippines',
                'salary_range' => 'PHP 35,000 - 55,000 + Commission',
                'closing_date' => now()->addDays(30),
                'source' => 'Company Website',
                'estimated_cost' => 20000,
            ],
            [
                'title' => 'International Tour Coordinator',
                'position_code' => 'TOR01',
                'dept_code' => 'TOR',
                'summary' => 'Oversee end-to-end group tour operations, ground transportation logistics, overseas tour guide coordination, and supplier negotiations.',
                'required_skills' => ['Tour Logistics', 'Vendor Management', 'Supplier Negotiations', 'Emergency Response'],
                'preferred_skills' => ['Japanese/Korean Language', 'Crisis Management'],
                'vacancies_count' => 2,
                'employment_type' => 'full_time',
                'location' => 'Taguig City, Philippines',
                'salary_range' => 'PHP 40,000 - 65,000',
                'closing_date' => now()->addDays(25),
                'source' => 'LinkedIn',
                'estimated_cost' => 25000,
            ],
            [
                'title' => 'Flight Ticketing & GDS Specialist',
                'position_code' => 'TVS01',
                'dept_code' => 'TVS',
                'summary' => 'Issue, re-issue, and refund airline tickets across global distribution systems (Amadeus, Sabre, Galileo) with high accuracy.',
                'required_skills' => ['Amadeus GDS', 'Sabre', 'Airline Ticketing', 'Fare Quotation'],
                'preferred_skills' => ['IATA Regulations', 'Visa Documentation'],
                'vacancies_count' => 2,
                'employment_type' => 'full_time',
                'location' => 'Pasig City, Philippines',
                'salary_range' => 'PHP 30,000 - 48,000',
                'closing_date' => now()->addDays(20),
                'source' => 'JobStreet',
                'estimated_cost' => 15000,
            ],
            [
                'title' => 'Visa & Passport Processing Officer',
                'position_code' => 'TVS02',
                'dept_code' => 'TVS',
                'summary' => 'Assist clients with embassy visa applications (Schengen, US, Japan, Korea, Australia), document verification, and submission schedules.',
                'required_skills' => ['Visa Documentation', 'Embassy Guidelines', 'Document Verification'],
                'preferred_skills' => ['Notarization', 'Client Counseling'],
                'vacancies_count' => 2,
                'employment_type' => 'full_time',
                'location' => 'Quezon City, Philippines',
                'salary_range' => 'PHP 28,000 - 42,000',
                'closing_date' => now()->addDays(15),
                'source' => 'Company Website',
                'estimated_cost' => 12000,
            ],
            [
                'title' => 'Destination Marketing Specialist',
                'position_code' => 'MDB01',
                'dept_code' => 'MDB',
                'summary' => 'Drive viral social media travel campaigns, craft promotional package deals, collaborate with tourism boards, and curate travel content.',
                'required_skills' => ['Social Media Marketing', 'Content Creation', 'Canva/Photoshop'],
                'preferred_skills' => ['Travel Blogging', 'SEO', 'Video Editing'],
                'vacancies_count' => 1,
                'employment_type' => 'full_time',
                'location' => 'Makati City, Philippines',
                'salary_range' => 'PHP 32,000 - 50,000',
                'closing_date' => now()->addDays(30),
                'source' => 'Facebook',
                'estimated_cost' => 18000,
            ],
            [
                'title' => 'Lead Tour Guide & Guest Experience Lead',
                'position_code' => 'CXS01',
                'dept_code' => 'CXS',
                'summary' => 'Ensure unforgettable guest experiences during group tours, coordinate daily itineraries, resolve guest requests, and manage local guides.',
                'required_skills' => ['Tour Guiding', 'First Aid', 'Guest Relations', 'Problem Solving'],
                'preferred_skills' => ['DOT Accredited Guide', 'Multiple Languages'],
                'vacancies_count' => 4,
                'employment_type' => 'contract',
                'location' => 'Cebu City / Manila Base',
                'salary_range' => 'PHP 30,000 - 45,000 + Daily Allowances',
                'closing_date' => now()->addDays(40),
                'source' => 'Referral',
                'estimated_cost' => 10000,
            ]
        ];

        foreach ($postings as $posting) {
            $position = JobPosition::where('code', $posting['position_code'])->first();
            $dept = Department::where('code', $posting['dept_code'])->first();
            if (!$position || !$dept) continue;

            unset($posting['position_code'], $posting['dept_code']);

            JobPosting::updateOrCreate([
                'title' => $posting['title'],
            ], array_merge($posting, [
                'job_position_id' => $position->id,
                'department_id' => $dept->id,
                'posted_by' => $hrAdmin ? $hrAdmin->id : null,
                'slug' => Str::slug($posting['title'] . '-' . Str::random(4)),
                'description' => $posting['summary'],
                'posted_date' => now(),
                'status' => 'published',
            ]));
        }
    }
}
