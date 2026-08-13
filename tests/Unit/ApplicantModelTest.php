<?php

namespace Tests\Unit;

use App\Models\Applicant;
use App\Models\ApplicantSkill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicantModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_name_attribute_returns_concatenated_first_and_last_name(): void
    {
        $applicant = new Applicant([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('Jane Doe', $applicant->full_name);
    }

    public function test_skill_names_attribute_returns_array_of_skills(): void
    {
        $user = User::factory()->create();
        $applicant = Applicant::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
        ]);

        ApplicantSkill::create([
            'applicant_id' => $applicant->id,
            'skill' => 'PHP',
        ]);

        ApplicantSkill::create([
            'applicant_id' => $applicant->id,
            'skill' => 'Laravel',
        ]);

        $this->assertEqualsCanonicalizing(['PHP', 'Laravel'], $applicant->fresh()->skill_names);
    }
}
