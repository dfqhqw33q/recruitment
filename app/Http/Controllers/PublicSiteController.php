<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobPosting;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    public function home()
    {
        $featuredJobs = JobPosting::with('department', 'jobPosition')
            ->where('status', 'published')
            ->latest()
            ->limit(4)
            ->get();

        return view('public.home', compact('featuredJobs'));
    }

    public function about()
    {
        return view('public.about');
    }

    public function tours()
    {
        return view('public.tours');
    }

    public function destinations()
    {
        return view('public.destinations');
    }

    public function careers(Request $request)
    {
        $query = JobPosting::with('department', 'jobPosition')
            ->where('status', 'published');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                    ->orWhere('summary', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%")
                    ->orWhere('location', 'like', "%{$request->search}%");
            });
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->employment_type) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->location) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        $jobs = $query->paginate(9)->withQueryString();
        $departments = Department::all();

        return view('public.careers', compact('jobs', 'departments'));
    }

    public function showJob(JobPosting $posting)
    {
        abort_unless($posting->status === 'published', 404);
        $posting->load('department', 'jobPosition');
        return view('public.job-show', compact('posting'));
    }

public function contact()
    {
        return view('public.contact');
    }

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        logger()->info('Public contact form submission', [
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
        ]);

        return redirect()->route('public.contact')->with('success', 'Thank you for reaching out! Our team will get back to you shortly.');
    }
}
