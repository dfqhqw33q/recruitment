<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobPosition;
use App\Models\JobPosting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JobPostingController extends Controller
{
    public function index(Request $request)
    {
        $query = JobPosting::with('department', 'jobPosition', 'applications')
            ->when($request->search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('title', 'like', "%{$s}%")
                        ->orWhere('location', 'like', "%{$s}%")
                        ->orWhereHas('department', fn($d) => $d->where('name', 'like', "%{$s}%"));
                });
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->department_id, fn($q, $d) => $q->where('department_id', $d))
            ->latest();

        $postings = $query->paginate(10);
        $departments = Department::all();

        return view('recruitment.job-postings.index', compact('postings', 'departments'));
    }

    public function create()
    {
        $departments = Department::all();
        $positions = JobPosition::all();
        return view('recruitment.job-postings.create', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'job_position_id' => 'required|exists:job_positions,id',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'vacancies_count' => 'required|integer|min:1',
            'required_skills' => 'nullable|array',
            'preferred_skills' => 'nullable|array',
            'screening_questions' => 'nullable|array',
            'employment_type' => 'required|in:full_time,part_time,contract,internship',
            'location' => 'nullable|string',
            'salary_range' => 'nullable|string',
            'closing_date' => 'nullable|date',
            'status' => 'required|in:draft,published,closed',
        ]);

        $data['slug'] = Str::slug($data['title'] . '-' . Str::random(4));
        $data['posted_by'] = auth()->id();
        $data['posted_date'] = now();

        $posting = JobPosting::create($data);

        app(ActivityLogService::class)->log(
            'create', 'Recruitment',
            "Job posting '{$posting->title}' created.",
            'JobPosting', $posting->id, null, $data
        );

        return redirect()->route('recruitment.job-postings.show', $posting)
            ->with('success', 'Job posting created successfully.');
    }

public function show(JobPosting $posting)
    {
        $posting->load(['department', 'jobPosition', 'applications.applicant', 'applications.aiRecommendation']);
        return view('recruitment.job-postings.show', compact('posting'));
    }

    public function edit(JobPosting $posting)
    {
        $departments = Department::all();
        $positions = JobPosition::all();
        return view('recruitment.job-postings.edit', compact('posting', 'departments', 'positions'));
    }

    public function update(Request $request, JobPosting $posting)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'job_position_id' => 'required|exists:job_positions,id',
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'vacancies_count' => 'required|integer|min:1',
            'required_skills' => 'nullable|array',
            'preferred_skills' => 'nullable|array',
            'screening_questions' => 'nullable|array',
            'employment_type' => 'required|in:full_time,part_time,contract,internship',
            'location' => 'nullable|string',
            'salary_range' => 'nullable|string',
            'closing_date' => 'nullable|date',
            'status' => 'required|in:draft,published,closed',
        ]);

        $old = $posting->toArray();
        $posting->update($data);

        app(ActivityLogService::class)->log(
            'update', 'Recruitment',
            "Job posting '{$posting->title}' updated.",
            'JobPosting', $posting->id, $old, $posting->toArray()
        );

        return redirect()->route('recruitment.job-postings.show', $posting)
            ->with('success', 'Job posting updated successfully.');
    }

    public function destroy(JobPosting $posting)
    {
        app(ActivityLogService::class)->log(
            'delete', 'Recruitment',
            "Job posting '{$posting->title}' deleted.",
            'JobPosting', $posting->id
        );
        $posting->delete();
        return redirect()->route('recruitment.job-postings.index')
            ->with('success', 'Job posting deleted.');
    }

    public function toggleStatus(JobPosting $posting)
    {
        $posting->update(['status' => $posting->status === 'published' ? 'closed' : 'published']);
        return back()->with('success', 'Job posting status updated.');
    }
}
