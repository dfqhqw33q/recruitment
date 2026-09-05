<?php

namespace Database\Seeders;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\AiRecommendation;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\Interview;
use App\Models\InterviewAssessment;
use App\Models\JobPosition;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompleteDemoSeeder extends Seeder
{
    public const DEMO_PASSWORD = 'password123';

    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        DB::transaction(function (): void {
            $departments = $this->seedDepartments();
            $positions = $this->seedPositions($departments);
            $users = $this->seedUsers();
            $postings = $this->seedPostings($positions, $users['hr']);

            $this->seedEmployeeProfiles($users['employees'], $positions);
            $this->seedApplicants($users['applicants'], $postings, $users['hr']);
        });
    }

    /** @return array<string, Department> */
    private function seedDepartments(): array
    {
        $definitions = [
            'TOR' => 'Tour Operations & Reservations',
            'TVS' => 'Ticketing & Visa Services',
            'STC' => 'Sales & Travel Consulting',
            'CXS' => 'Customer Experience & Support',
            'MDB' => 'Marketing & Destination Branding',
        ];

        $departments = [];
        foreach ($definitions as $code => $name) {
            $departments[$code] = Department::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $name,
                    'status' => 'active',
                ]
            );
        }

        return $departments;
    }

    /** @param array<string, Department> $departments */
    /** @return array<string, JobPosition> */
    private function seedPositions(array $departments): array
    {
        $definitions = [
            'STC01' => ['title' => 'Senior Travel Consultant', 'department' => 'STC'],
            'TOR01' => ['title' => 'International Tour Coordinator', 'department' => 'TOR'],
            'TVS01' => ['title' => 'Flight Ticketing & GDS Specialist', 'department' => 'TVS'],
            'TVS02' => ['title' => 'Visa & Passport Processing Officer', 'department' => 'TVS'],
            'MDB01' => ['title' => 'Destination Marketing Specialist', 'department' => 'MDB'],
            'CXS01' => ['title' => 'Lead Tour Guide & Guest Experience Lead', 'department' => 'CXS'],
        ];

        $positions = [];
        foreach ($definitions as $code => $definition) {
            $positions[$code] = JobPosition::firstOrCreate(
                ['code' => $code],
                [
                    'title' => $definition['title'],
                    'department_id' => $departments[$definition['department']]->id,
                    'description' => $definition['title'],
                    'employment_type' => 'full_time',
                    'status' => 'active',
                ]
            );
        }

        return $positions;
    }

    /** @return array<string, mixed> */
    private function seedUsers(): array
    {
        $accounts = [
            'super_admin' => ['name' => 'Super Admin', 'email' => 'admin@hiraya.com', 'role' => 'Super Admin'],
            'hr' => ['name' => 'HR Administrator', 'email' => 'hr@hiraya.com', 'role' => 'HR Administrator'],
            'recruitment' => ['name' => 'Recruitment Officer', 'email' => 'recruitment@hiraya.com', 'role' => 'Recruitment Officer'],
            'tours_head' => ['name' => 'Tour Operations Head', 'email' => 'tours.head@hiraya.com', 'role' => 'Department Head'],
            'visa_head' => ['name' => 'Ticketing & Visa Head', 'email' => 'visa.head@hiraya.com', 'role' => 'Department Head'],
            'sales_head' => ['name' => 'Sales & Travel Head', 'email' => 'sales.head@hiraya.com', 'role' => 'Department Head'],
        ];

        $users = [];
        foreach ($accounts as $key => $account) {
            $users[$key] = $this->upsertUser($account['name'], $account['email'], $account['role']);
        }

        $employeeAccounts = [
            'carlos' => ['name' => 'Carlos Sainz', 'email' => 'carlos@gmail.com'],
            'samantha' => ['name' => 'Samantha Tan', 'email' => 'samantha.tan@gmail.com'],
            'ramon' => ['name' => 'Ramon Bautista', 'email' => 'ramon.bautista@gmail.com'],
        ];
        $users['employees'] = [];
        foreach ($employeeAccounts as $key => $account) {
            $users['employees'][$key] = $this->upsertUser($account['name'], $account['email'], 'Employee');
        }

        $applicants = [
            ['first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'email' => 'juan.delacruz@example.com'],
            ['first_name' => 'Maria', 'last_name' => 'Santos', 'email' => 'maria.santos@example.com'],
            ['first_name' => 'Carlos', 'last_name' => 'Reyes', 'email' => 'carlos.reyes@example.com'],
            ['first_name' => 'Ana', 'last_name' => 'Garcia', 'email' => 'ana.garcia@example.com'],
            ['first_name' => 'Pedro', 'last_name' => 'Aquino', 'email' => 'pedro.aquino@example.com'],
            ['first_name' => 'Liza', 'last_name' => 'Soberano', 'email' => 'liza.soberano@example.com'],
            ['first_name' => 'Marco', 'last_name' => 'Polo', 'email' => 'marco.polo@example.com'],
            ['first_name' => 'Beatriz', 'last_name' => 'Alonzo', 'email' => 'beatriz.alonzo@example.com'],
            ['first_name' => 'Gabriel', 'last_name' => 'Concepcion', 'email' => 'gabriel.concepcion@example.com'],
            ['first_name' => 'Patricia', 'last_name' => 'Evangelista', 'email' => 'patricia.evangelista@example.com'],
            ['first_name' => 'Daniel', 'last_name' => 'Padilla', 'email' => 'daniel.padilla@example.com'],
        ];

        $users['applicants'] = [];
        foreach ($applicants as $applicant) {
            $users['applicants'][$applicant['email']] = $this->upsertUser(
                $applicant['first_name'] . ' ' . $applicant['last_name'],
                $applicant['email'],
                'Applicant'
            );
        }

        return $users;
    }

    private function upsertUser(string $name, string $email, string $role): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->status = 'active';
        $user->email_verified_at ??= now();

        if (!$user->exists || !Hash::check(self::DEMO_PASSWORD, (string) $user->password)) {
            $user->password = Hash::make(self::DEMO_PASSWORD);
        }

        $user->save();
        $user->syncRoles([$role]);

        return $user;
    }

    /** @param array<string, JobPosition> $positions */
    /** @param array<string, Department> $departments */
    private function seedEmployeeProfiles(array $employees, array $positions): void
    {
        $definitions = [
            'carlos' => ['employee_id' => 'EMP-2026-001', 'position' => 'STC01', 'hire_date' => '2026-01-15'],
            'samantha' => ['employee_id' => 'EMP-2026-002', 'position' => 'TOR01', 'hire_date' => '2026-01-16'],
            'ramon' => ['employee_id' => 'EMP-2026-003', 'position' => 'TVS01', 'hire_date' => '2026-01-17'],
        ];

        foreach ($definitions as $key => $definition) {
            $user = $employees[$key];
            [$firstName, $lastName] = array_pad(explode(' ', $user->name, 2), 2, '');
            $position = $positions[$definition['position']];

            $profile = EmployeeProfile::where('user_id', $user->id)->first();
            $profileByEmployeeId = EmployeeProfile::where('employee_id', $definition['employee_id'])->first();
            if ($profileByEmployeeId && $profileByEmployeeId->user_id && $profileByEmployeeId->user_id !== $user->id) {
                throw new \RuntimeException("Employee ID {$definition['employee_id']} belongs to another user.");
            }
            $profile ??= $profileByEmployeeId ?? new EmployeeProfile();
            $profile->fill([
                'department_id' => $position->department_id,
                'job_position_id' => $position->id,
                'employee_id' => $definition['employee_id'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'phone' => null,
                'country' => 'Philippines',
                'nationality' => 'Filipino',
                'hire_date' => $definition['hire_date'],
                'employment_status' => 'regular',
                'status' => 'active',
            ]);
            $profile->save();
        }
    }

    /** @param array<string, JobPosition> $positions */
    private function seedPostings(array $positions, User $hr): array
    {
        $postings = [];
        foreach ($positions as $code => $position) {
            $posting = JobPosting::where('title', $position->title)
                ->where('job_position_id', $position->id)
                ->where('department_id', $position->department_id)
                ->first();
            if (!$posting) {
                $posting = JobPosting::create([
                    'job_position_id' => $position->id,
                    'department_id' => $position->department_id,
                    'posted_by' => $hr->id,
                    'title' => $position->title,
                    'slug' => Str::slug($position->title . '-' . $code),
                    'summary' => $position->title,
                    'description' => $position->title,
                    'vacancies_count' => 1,
                    'employment_type' => 'full_time',
                    'location' => 'Manila, Philippines',
                    'source' => 'Company Website',
                    'posted_date' => now()->toDateString(),
                    'status' => 'published',
                ]);
            }

            $postings[$code] = $posting;
        }

        return $postings;
    }

    /** @param array<string, User> $applicantUsers */
    private function seedApplicants(array $applicantUsers, array $postings, User $hr): void
    {
        $definitions = [
            ['email' => 'juan.delacruz@example.com', 'position' => 'STC01', 'status' => 'submitted'],
            ['email' => 'maria.santos@example.com', 'position' => 'TOR01', 'status' => 'shortlisted'],
            ['email' => 'carlos.reyes@example.com', 'position' => 'TVS01', 'status' => 'for_interview'],
            ['email' => 'ana.garcia@example.com', 'position' => 'TVS02', 'status' => 'assessed'],
            ['email' => 'pedro.aquino@example.com', 'position' => 'MDB01', 'status' => 'recommended'],
            ['email' => 'liza.soberano@example.com', 'position' => 'CXS01', 'status' => 'screening'],
            ['email' => 'marco.polo@example.com', 'position' => 'STC01', 'status' => 'shortlisted'],
            ['email' => 'beatriz.alonzo@example.com', 'position' => 'TOR01', 'status' => 'for_interview'],
            ['email' => 'gabriel.concepcion@example.com', 'position' => 'TVS01', 'status' => 'recommended'],
            ['email' => 'patricia.evangelista@example.com', 'position' => 'MDB01', 'status' => 'screening'],
            ['email' => 'daniel.padilla@example.com', 'position' => 'TVS02', 'status' => 'submitted'],
        ];

        foreach ($definitions as $index => $definition) {
            $user = $applicantUsers[$definition['email']];
            [$firstName, $lastName] = array_pad(explode(' ', $user->name, 2), 2, '');
            $applicant = Applicant::updateOrCreate(
                ['email' => $definition['email']],
                [
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => null,
                    'country' => 'Philippines',
                    'nationality' => 'Filipino',
                    'summary' => 'Applicant for the ' . $definition['position'] . ' recruitment pipeline.',
                    'status' => 'active',
                    'source' => 'website',
                ]
            );

            $posting = $postings[$definition['position']];
            $status = $definition['status'];
            $reviewed = !in_array($status, ['submitted'], true);
            $application = Application::updateOrCreate(
                ['applicant_id' => $applicant->id, 'job_posting_id' => $posting->id],
                [
                    'reference_code' => sprintf('HIRAYA-APP-%03d', $index + 1),
                    'reviewed_by' => $reviewed ? $hr->id : null,
                    'status' => $status,
                    'cover_letter' => 'I am interested in the ' . $posting->title . ' position.',
                    'applied_at' => now()->subDays(12 - min($index, 10)),
                    'screening_date' => $reviewed ? now()->subDays(5) : null,
                    'reviewed_at' => $reviewed ? now()->subDays(4) : null,
                ]
            );

            if (in_array($status, ['for_interview', 'assessed', 'recommended'], true)) {
                $this->seedInterview($application, $hr, $status);
            }

            if (in_array($status, ['assessed', 'recommended'], true)) {
                AiRecommendation::updateOrCreate(
                    ['application_id' => $application->id],
                    [
                        'job_posting_id' => $posting->id,
                        'match_score' => $status === 'recommended' ? 90 : 82,
                        'skills_match_percentage' => $status === 'recommended' ? 88 : 80,
                        'confidence_score' => $status === 'recommended' ? 90 : 82,
                        'recommendation' => $status === 'recommended' ? 'highly_recommended' : 'recommended',
                        'missing_skills' => [],
                        'strengths' => ['Relevant experience', 'Completed interview assessment'],
                        'weaknesses' => [],
                        'qualification_gaps' => [],
                        'explanation' => 'Seeded recommendation based on the documented recruitment stage.',
                        'summary' => 'Seeded AI decision-support record.',
                        'score_breakdown' => ['skills' => 85, 'experience' => 85, 'interview' => 85],
                        'rank' => $status === 'recommended' ? 1 : 2,
                        'status' => 'generated',
                    ]
                );
            }
        }
    }

    private function seedInterview(Application $application, User $hr, string $status): void
    {
        $completed = in_array($status, ['assessed', 'recommended'], true);
        $interview = Interview::updateOrCreate(
            ['application_id' => $application->id, 'round' => 1],
            [
                'interviewer_id' => $hr->id,
                'scheduled_by' => $hr->id,
                'scheduled_at' => $completed ? now()->subDays(2) : now()->addDays(2),
                'type' => 'video',
                'duration_minutes' => 60,
                'status' => $completed ? 'completed' : 'scheduled',
            ]
        );

        if ($completed) {
            InterviewAssessment::updateOrCreate(
                ['interview_id' => $interview->id],
                [
                    'assessor_id' => $hr->id,
                    'communication_score' => 85,
                    'technical_score' => 85,
                    'experience_score' => 85,
                    'cultural_fit_score' => 85,
                    'overall_score' => 85,
                    'strengths' => 'Relevant experience and clear communication.',
                    'comments' => 'Assessment recorded as part of the seeded recruitment workflow.',
                    'status' => 'submitted',
                ]
            );
        }
    }
}
