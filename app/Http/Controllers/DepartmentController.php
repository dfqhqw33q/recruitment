<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('jobPositions')->withCount('jobPostings')->get();
        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments',
            'description' => 'nullable|string',
        ]);

        $dept = Department::create($data);
        app(ActivityLogService::class)->log('create', 'Departments', "Department '{$dept->name}' created.", 'Department', $dept->id);
        return back()->with('success', 'Department created.');
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
        ]);
        $department->update($data);
        app(ActivityLogService::class)->log('update', 'Departments', "Department '{$department->name}' updated.", 'Department', $department->id);
        return back()->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return back()->with('success', 'Department deleted.');
    }
}
