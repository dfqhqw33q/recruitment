<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\JobPosition;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['code' => 'TOR', 'name' => 'Tour Operations & Reservations', 'description' => 'Manages tour packages, itinerary planning, hotel/flight bookings, and travel reservations.'],
            ['code' => 'TVS', 'name' => 'Ticketing & Visa Services', 'description' => 'Handles airline ticketing, passport processing, and international visa assistance.'],
            ['code' => 'STC', 'name' => 'Sales & Travel Consulting', 'description' => 'Corporate travel sales, leisure tour packages, and client travel advisory.'],
            ['code' => 'CXS', 'name' => 'Customer Experience & Support', 'description' => 'Client assistance, emergency travel help, feedback, and tour leader coordination.'],
            ['code' => 'MDB', 'name' => 'Marketing & Destination Branding', 'description' => 'Travel promotions, social media campaigns, destination marketing, and partner deals.'],
            ['code' => 'FIN', 'name' => 'Finance & Accounting', 'description' => 'Travel invoicing, currency exchanges, vendor payments, and tour budgeting.'],
            ['code' => 'HRT', 'name' => 'Human Resources & Training', 'description' => 'Staffing, tour guide accreditation, and hospitality training.'],
            ['code' => 'ITB', 'name' => 'IT & Online Booking Systems', 'description' => 'Travel booking portal, GDS integration (Amadeus/Sabre), and digital infrastructure.'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['code' => $dept['code']], $dept);
        }

        $positions = [
            ['title' => 'Senior Travel Consultant', 'code' => 'STC01', 'department_code' => 'STC', 'required_skills' => ['Flight Booking', 'Itinerary Planning', 'Customer Advisory', 'Amadeus GDS'], 'preferred_skills' => ['Corporate Sales', 'Visa Knowledge', 'Destination Expertise']],
            ['title' => 'International Tour Coordinator', 'code' => 'TOR01', 'department_code' => 'TOR', 'required_skills' => ['Tour Logistics', 'Vendor Management', 'Supplier Negotiations', 'Emergency Response'], 'preferred_skills' => ['Japanese/Korean Language', 'Crisis Management']],
            ['title' => 'Flight Ticketing & GDS Specialist', 'code' => 'TVS01', 'department_code' => 'TVS', 'required_skills' => ['Amadeus GDS', 'Sabre', 'Airline Ticketing', 'Fare Quotation'], 'preferred_skills' => ['IATA Regulations', 'Visa Documentation']],
            ['title' => 'Visa & Passport Processing Officer', 'code' => 'TVS02', 'department_code' => 'TVS', 'required_skills' => ['Visa Documentation', 'Embassy Guidelines', 'Document Verification'], 'preferred_skills' => ['Notarization', 'Client Counseling']],
            ['title' => 'Destination Marketing Specialist', 'code' => 'MDB01', 'department_code' => 'MDB', 'required_skills' => ['Social Media Marketing', 'Content Creation', 'Canva/Photoshop'], 'preferred_skills' => ['Travel Blogging', 'SEO', 'Video Editing']],
            ['title' => 'Lead Tour Guide & Guest Experience Lead', 'code' => 'CXS01', 'department_code' => 'CXS', 'required_skills' => ['Tour Guiding', 'First Aid', 'Guest Relations', 'Problem Solving'], 'preferred_skills' => ['DOT Accredited Guide', 'Multiple Languages']],
            ['title' => 'Travel Operations Analyst', 'code' => 'TOR02', 'department_code' => 'TOR', 'required_skills' => ['Data Analysis', 'Cost Optimization', 'Excel', 'Supplier Auditing'], 'preferred_skills' => ['Power BI', 'SQL']],
            ['title' => 'Travel Systems Engineer (GDS)', 'code' => 'ITB01', 'department_code' => 'ITB', 'required_skills' => ['Amadeus API', 'PHP', 'Laravel', 'MySQL'], 'preferred_skills' => ['Sabre Web Services', 'Redis']],
        ];

foreach ($positions as $pos) {
            $dept = Department::where('code', $pos['department_code'])->first();
            if ($dept) {
                unset($pos['department_code']);
                JobPosition::updateOrCreate(['code' => $pos['code']], array_merge($pos, [
                    'department_id' => $dept->id,
                    'description' => $pos['title'] . ' role',
                    'status' => 'active',
                ]));
            }
        }
    }
}
