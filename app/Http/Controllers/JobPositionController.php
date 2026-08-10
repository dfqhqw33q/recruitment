<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobPosition;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class JobPositionController extends Controller
{
    public function index(Request $request)
    {
        $query = JobPosition::with('department')
            ->when($request->department_id, fn($q, $d) => $q->where('department_id', $d));
        $positions = $query->get();
        $departments = Department::all();
        return view('admin.job-positions.index', compact('positions', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:job_positions',
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
        ]);
        $position = JobPosition::create($data);
        app(ActivityLogService::class)->log('create', 'Job Positions', "Job position '{$position->title}' created.", 'JobPosition', $position->id);
        return back()->with('success', 'Job position created.');
    }

    public function update(Request $request, JobPosition $position)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:job_positions,code,' . $position->id,
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
        ]);
        $position->update($data);
        app(ActivityLogService::class)->log('update', 'Job Positions', "Job position '{$position->title}' updated.", 'JobPosition', $position->id);
        return back()->with('success', 'Job position updated.');
    }

    public function destroy(JobPosition $position)
    {
        $position->delete();
        return back()->with('success', 'Job position deleted.');
    }
}
