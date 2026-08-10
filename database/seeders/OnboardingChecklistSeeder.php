<?php

namespace Database\Seeders;

use App\Models\OnboardingChecklist;
use Illuminate\Database\Seeder;

class OnboardingChecklistSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Submit Government IDs', 'description' => 'Submit valid government-issued IDs (SSS, TIN, PhilHealth, Pag-IBIG)', 'category' => 'document', 'is_required' => true, 'sort_order' => 1],
            ['name' => 'Complete Employment Contract', 'description' => 'Review and sign the employment contract', 'category' => 'document', 'is_required' => true, 'sort_order' => 2],
            ['name' => 'Submit Diploma & Transcript', 'description' => 'Submit certified copies of educational documents', 'category' => 'document', 'is_required' => true, 'sort_order' => 3],
            ['name' => 'Bank Account Setup', 'description' => 'Provide bank account details for payroll', 'category' => 'compliance', 'is_required' => true, 'sort_order' => 4],
            ['name' => 'Orientation Attendance', 'description' => 'Attend company orientation session', 'category' => 'orientation', 'is_required' => true, 'sort_order' => 5],
            ['name' => 'Code of Conduct Training', 'description' => 'Complete code of conduct and ethics training', 'category' => 'training', 'is_required' => true, 'sort_order' => 6],
            ['name' => 'IT Account Creation', 'description' => 'Create company email and system accounts', 'category' => 'system', 'is_required' => true, 'sort_order' => 7],
            ['name' => 'Workstation Setup', 'description' => 'Set up workstation and equipment', 'category' => 'system', 'is_required' => true, 'sort_order' => 8],
            ['name' => 'Health Check Requirement', 'description' => 'Complete pre-employment medical requirements', 'category' => 'compliance', 'is_required' => true, 'sort_order' => 9],
            ['name' => 'Team Introduction', 'description' => 'Complete team introduction and onboarding buddy', 'category' => 'orientation', 'is_required' => false, 'sort_order' => 10],
        ];

        foreach ($items as $item) {
            OnboardingChecklist::updateOrCreate(['name' => $item['name']], $item);
        }
    }
}
