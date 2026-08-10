<?php

namespace Database\Seeders;

use App\Models\AiRecommendation;
use App\Models\Applicant;
use App\Models\ApplicantEducation;
use App\Models\ApplicantExperience;
use App\Models\ApplicantSkill;
use App\Models\Application;
use App\Models\Certification;
use App\Models\Interview;
use App\Models\InterviewAssessment;
use App\Models\JobPosting;
use App\Models\OfferLetter;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApplicantSeeder extends Seeder
{
    public function run(): void
    {
        $postings = JobPosting::all();
        if ($postings->isEmpty()) {
            $this->call(JobPostingSeeder::class);
            $postings = JobPosting::all();
        }

        $itPosting = $postings->values()->get(0);
        $hrPosting = $postings->values()->get(1) ?? $itPosting;
        $finPosting = $postings->values()->get(2) ?? $itPosting;

        $applicants = [
            [
                'first_name' => 'Juan', 'last_name' => 'Dela Cruz',
                'email' => 'juan.delacruz@example.com', 'phone' => '09181111111',
'skills' => ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Git', 'Docker'],
                'proficiencies' => ['expert', 'expert', 'advanced', 'advanced', 'advanced', 'intermediate'],
                'education' => [
                    ['institution' => 'University of the Philippines', 'degree' => 'BS Computer Science', 'field_of_study' => 'Computer Science', 'start_date' => '2015-06-01', 'end_date' => '2019-05-30', 'gpa' => 1.45, 'honors' => 'Cum Laude'],
                ],
                'experience' => [
                    ['company' => 'Tech Solutions Inc.', 'job_title' => 'Senior PHP Developer', 'start_date' => '2019-07-01', 'end_date' => null, 'is_current' => true, 'skills_used' => ['PHP', 'Laravel', 'MySQL', 'Docker']],
                    ['company' => 'StartupHub', 'job_title' => 'Web Developer', 'start_date' => '2017-06-01', 'end_date' => '2019-06-30', 'is_current' => false, 'skills_used' => ['PHP', 'JavaScript']],
                ],
                'certifications' => [
                    ['name' => 'AWS Certified Developer', 'issuing_organization' => 'Amazon Web Services', 'issue_date' => '2022-03-01'],
                    ['name' => 'Laravel Certified', 'issuing_organization' => 'Laravel', 'issue_date' => '2021-08-01'],
                ],
                'summary' => 'Senior PHP developer with 5+ years of experience building scalable web applications.',
            ],
            [
                'first_name' => 'Maria', 'last_name' => 'Santos',
                'email' => 'maria.santos@example.com', 'phone' => '09182222222',
'skills' => ['PHP', 'Laravel', 'MySQL', 'JavaScript', 'Git'],
                'proficiencies' => ['advanced', 'advanced', 'advanced', 'intermediate', 'advanced'],
                'education' => [
                    ['institution' => 'De La Salle University', 'degree' => 'BS Information Technology', 'field_of_study' => 'IT', 'start_date' => '2016-06-01', 'end_date' => '2020-05-30', 'gpa' => 1.75],
                ],
                'experience' => [
                    ['company' => 'DigitalWorks PH', 'job_title' => 'Full Stack Developer', 'start_date' => '2020-07-01', 'end_date' => null, 'is_current' => true, 'skills_used' => ['PHP', 'Laravel', 'MySQL', 'JavaScript']],
                ],
                'certifications' => [
                    ['name' => 'Google Cloud Associate Engineer', 'issuing_organization' => 'Google', 'issue_date' => '2023-01-01'],
                ],
                'summary' => 'Full stack developer passionate about clean code and modern web technologies.',
            ],
            [
                'first_name' => 'Carlos', 'last_name' => 'Reyes',
                'email' => 'carlos.reyes@example.com', 'phone' => '09183333333',
'skills' => ['PHP', 'MySQL'],
                'proficiencies' => ['intermediate', 'intermediate'],
                'education' => [
                    ['institution' => 'Polytechnic University', 'degree' => 'BS Computer Engineering', 'field_of_study' => 'CompE', 'start_date' => '2014-06-01', 'end_date' => '2018-05-30', 'gpa' => 2.25],
                ],
                'experience' => [
                    ['company' => 'Local Web Agency', 'job_title' => 'Junior Developer', 'start_date' => '2018-06-01', 'end_date' => '2021-01-01', 'is_current' => false, 'skills_used' => ['PHP', 'MySQL']],
                ],
                'certifications' => [],
                'summary' => 'Junior developer looking to grow and learn new technologies.',
            ],
            [
                'first_name' => 'Ana', 'last_name' => 'Garcia',
                'email' => 'ana.garcia@example.com', 'phone' => '09184444444',
'skills' => ['Recruitment', 'HRIS', 'Communication', 'Onboarding'],
                'proficiencies' => ['expert', 'advanced', 'expert', 'advanced'],
                'education' => [
                    ['institution' => 'University of Santo Tomas', 'degree' => 'BS Psychology', 'field_of_study' => 'Psychology', 'start_date' => '2011-06-01', 'end_date' => '2015-05-30', 'gpa' => 1.8],
                ],
                'experience' => [
                    ['company' => 'Global HR Services', 'job_title' => 'HR Specialist', 'start_date' => '2016-03-01', 'end_date' => null, 'is_current' => true, 'skills_used' => ['Recruitment', 'HRIS', 'Onboarding']],
                ],
                'certifications' => [
                    ['name' => 'HR Management Certification', 'issuing_organization' => 'SHRM', 'issue_date' => '2020-06-01'],
                ],
                'summary' => 'Experienced HR specialist with expertise in recruitment and onboarding.',
            ],
            [
                'first_name' => 'Pedro', 'last_name' => 'Aquino',
                'email' => 'pedro.aquino@example.com', 'phone' => '09185555555',
'skills' => ['Excel', 'Financial Modeling', 'Budgeting', 'SQL'],
                'proficiencies' => ['expert', 'advanced', 'advanced', 'intermediate'],
                'education' => [
                    ['institution' => 'Ateneo de Manila', 'degree' => 'BS Accountancy', 'field_of_study' => 'Accountancy', 'start_date' => '2012-06-01', 'end_date' => '2016-05-30', 'gpa' => 1.6, 'honors' => 'Magna Cum Laude'],
                ],
                'experience' => [
                    ['company' => 'Big 4 Audit Firm', 'job_title' => 'Financial Analyst', 'start_date' => '2016-07-01', 'end_date' => null, 'is_current' => true, 'skills_used' => ['Excel', 'Financial Modeling']],
                ],
                'certifications' => [
                    ['name' => 'CPA', 'issuing_organization' => 'PRC', 'issue_date' => '2017-05-01'],
                ],
                'summary' => 'Certified public accountant with strong financial modeling skills.',
            ],
        ];

        foreach ($applicants as $idx => $data) {
            $email = $data['email'];
            $password = Hash::make('password123');

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $email,
                    'password' => $password,
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]);
                $user->assignRole('Applicant');
            }

$applicantData = $data;
            unset($applicantData['skills'], $applicantData['proficiencies'],
                $applicantData['education'], $applicantData['experience'],
                $applicantData['certifications']);

            $applicant = Applicant::updateOrCreate(['email' => $email], array_merge($applicantData, [
                'user_id' => $user->id,
                'date_of_birth' => '1995-01-15',
                'gender' => $idx % 2 === 0 ? 'male' : 'female',
                'nationality' => 'Philippines',
                'address' => '123 Main St',
                'city' => 'Manila',
                'state' => 'NCR',
                'country' => 'Philippines',
                'postal_code' => '1000',
                'status' => 'active',
                'source' => 'website',
            ]));

            // Skills
            foreach ($data['skills'] as $si => $skill) {
ApplicantSkill::updateOrCreate(
                    ['applicant_id' => $applicant->id, 'skill' => $skill],
                    ['proficiency' => $data['proficiencies'][$si] ?? 'intermediate', 'years_of_experience' => max(1, $idx + 1)]
                );
            }

            // Education
            foreach ($data['education'] as $edu) {
                ApplicantEducation::updateOrCreate(
                    ['applicant_id' => $applicant->id, 'institution' => $edu['institution'], 'degree' => $edu['degree']],
                    $edu
                );
            }

            // Experience
            foreach ($data['experience'] as $exp) {
                ApplicantExperience::updateOrCreate(
                    ['applicant_id' => $applicant->id, 'company' => $exp['company'], 'job_title' => $exp['job_title']],
                    $exp
                );
            }

            // Certifications
            foreach ($data['certifications'] as $cert) {
                Certification::updateOrCreate(
                    ['applicant_id' => $applicant->id, 'name' => $cert['name']],
                    $cert
                );
            }

            // Create applications
            $this->createApplications($applicant, $idx, $itPosting, $hrPosting, $finPosting);
        }
    }

    protected function createApplications($applicant, $idx, $itPosting, $hrPosting, $finPosting)
    {
        $statuses = ['submitted', 'under_review', 'screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'hired', 'rejected'];
        $status = $statuses[$idx % count($statuses)];

        $posting = $itPosting;
        if ($idx === 3) $posting = $hrPosting;
        if ($idx === 4) $posting = $finPosting;

        if (!$posting) return;

        $application = Application::updateOrCreate(
            ['applicant_id' => $applicant->id, 'job_posting_id' => $posting->id],
            [
                'status' => $status,
                'applied_at' => now()->subDays($idx + 1),
                'reviewed_at' => in_array($status, ['screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'hired']) ? now()->subDays($idx) : null,
                'screening_date' => in_array($status, ['screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'hired']) ? now()->subDays($idx)->addHours(2) : null,
                'cover_letter' => 'I am excited to apply for this position with my strong background.',
            ]
        );

        // Create interview for advanced statuses
        if (in_array($status, ['interviewed', 'assessed', 'recommended', 'hired'])) {
            $interviewer = User::where('email', 'ithead@recruit.test')->first();
            $interview = Interview::updateOrCreate(
                ['application_id' => $application->id, 'round' => 1],
                [
                    'interviewer_id' => $interviewer ? $interviewer->id : null,
                    'scheduled_by' => $interviewer ? $interviewer->id : null,
                    'scheduled_at' => now()->subDays($idx)->addHours(3),
                    'type' => $idx % 2 === 0 ? 'technical' : 'panel',
                    'round' => 1,
                    'duration_minutes' => 60,
                    'status' => 'completed',
                ]
            );

            $scores = [85, 92, 70, 95, 88];
            $assessment = InterviewAssessment::updateOrCreate(
                ['interview_id' => $interview->id],
                [
                    'assessor_id' => $interviewer ? $interviewer->id : null,
                    'communication_score' => $scores[$idx % count($scores)],
                    'technical_score' => $scores[($idx + 1) % count($scores)],
                    'experience_score' => $scores[($idx + 2) % count($scores)],
                    'cultural_fit_score' => $scores[($idx + 3) % count($scores)],
                    'overall_score' => round(($scores[$idx % count($scores)] + $scores[($idx + 1) % count($scores)]) / 2, 2),
                    'strengths' => 'Strong technical skills, good communication',
                    'weaknesses' => 'Limited experience with cloud',
                    'comments' => 'Candidate shows great potential.',
                    'status' => 'submitted',
                ]
            );
        }

        // AI Recommendation
        $matchScores = [91, 78, 45, 60, 85];
        $matchScore = $matchScores[$idx % count($matchScores)];
        $recommendation = $matchScore >= 85 ? 'highly_recommended' : ($matchScore >= 70 ? 'recommended' : ($matchScore >= 50 ? 'moderately_recommended' : 'not_recommended'));

        AiRecommendation::updateOrCreate(
            ['application_id' => $application->id],
            [
                'job_posting_id' => $posting->id,
                'match_score' => $matchScore,
                'skills_match_percentage' => $matchScore - 5,
                'confidence_score' => $matchScore - 2,
                'recommendation' => $recommendation,
                'missing_skills' => $matchScore < 70 ? ['Docker', 'Kubernetes'] : [],
                'strengths' => ['Relevant experience', 'Strong technical skills', 'Good educational background'],
                'weaknesses' => $matchScore < 70 ? ['Missing required skills'] : ['Limited leadership experience'],
                'explanation' => "Candidate match score is {$matchScore}%. " . ($matchScore >= 85 ? 'Skills exceed job requirements with excellent interview performance.' : 'Candidate has moderate qualifications.'),
                'summary' => 'AI-assisted analysis based on skills, experience, education, and interview performance.',
                'score_breakdown' => ['skills' => $matchScore - 5, 'experience' => $matchScore, 'education' => $matchScore - 10, 'interview' => $matchScore - 3],
                'rank' => $idx + 1,
                'status' => 'generated',
            ]
        );

        // Offer letter for hired
        if ($status === 'hired') {
            OfferLetter::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'job_posting_id' => $posting->id,
                    'prepared_by' => User::where('email', 'hr@recruit.test')->first()?->id,
                    'offer_number' => 'OFF-' . str_pad($application->id, 6, '0', STR_PAD_LEFT),
                    'salary' => 85000,
                    'start_date' => now()->addWeeks(2),
                    'employment_type' => 'full_time',
                    'terms' => 'Standard employment terms apply.',
                    'benefits' => 'HMO, 13th month pay, performance bonus.',
                    'status' => 'accepted',
                ]
            );
        }
    }
}
