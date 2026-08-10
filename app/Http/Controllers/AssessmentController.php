<?php

namespace App\Http\Controllers;

use App\Models\Interview;
use App\Models\InterviewAssessment;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function create(Interview $interview)
    {
        $interview->load(['application.applicant', 'application.jobPosting', 'assessment']);
        return view('recruitment.interviews.assessment', compact('interview'));
    }

    public function store(Request $request, Interview $interview)
    {
        $data = $request->validate([
            'communication_score' => 'required|numeric|min:0|max:100',
            'technical_score' => 'required|numeric|min:0|max:100',
            'experience_score' => 'required|numeric|min:0|max:100',
            'cultural_fit_score' => 'required|numeric|min:0|max:100',
            'leadership_score' => 'nullable|numeric|min:0|max:100',
            'problem_solving_score' => 'nullable|numeric|min:0|max:100',
            'strengths' => 'nullable|string',
            'weaknesses' => 'nullable|string',
            'comments' => 'nullable|string',
            'recommendation' => 'required|in:hire,consider,reject',
        ]);

        $data['overall_score'] = round(
            ($data['communication_score'] + $data['technical_score'] + $data['experience_score'] + $data['cultural_fit_score']) / 4, 2
        );
        $data['assessor_id'] = auth()->id();
        $data['status'] = 'submitted';

        $assessment = InterviewAssessment::updateOrCreate(
            ['interview_id' => $interview->id],
            $data
        );

        // Mark interview completed
        $interview->update(['status' => 'completed']);

        // Update application status
        $interview->application->update(['status' => 'assessed']);

        app(ActivityLogService::class)->log(
            'submit', 'Interviews',
            "Assessment submitted for interview #{$interview->id}.",
            'InterviewAssessment', $assessment->id
        );

        return redirect()->route('recruitment.interviews.show', $interview)
            ->with('success', 'Assessment submitted successfully.');
    }
}
