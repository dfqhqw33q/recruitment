@extends('layouts.public')

@section('title', 'Careers — Join Our Team')

@section('content')
{{-- Page header --}}
<section class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 text-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-200"><i class="fa-solid fa-users mr-1"></i> Careers</p>
        <h1 class="mt-3 text-4xl font-bold">Join Our Team</h1>
        <p class="mt-4 max-w-2xl text-lg text-indigo-100">Explore career opportunities and become part of our growing travel and tourism team. We're always looking for passionate individuals to help us create unforgettable experiences.</p>
    </div>
</section>

{{-- Search & filters --}}
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <form method="GET" action="{{ route('public.careers') }}" class="rounded-lg bg-white p-4 shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-500">Search</label>
                <div class="relative mt-1">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search job title, keyword..." class="w-full rounded-md border border-gray-300 py-2 pl-9 pr-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Department</label>
                <select name="department_id" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Employment Type</label>
                <select name="employment_type" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    @foreach(['full_time' => 'Full Time', 'part_time' => 'Part Time', 'contract' => 'Contract', 'temporary' => 'Temporary', 'internship' => 'Internship'] as $val => $label)
                    <option value="{{ $val }}" @selected(request('employment_type') == $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Location</label>
                <input type="text" name="location" value="{{ request('location') }}" placeholder="City..." class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>
        <div class="mt-3 flex items-center gap-3">
            <button type="submit" class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Search Jobs</button>
            <a href="{{ route('public.careers') }}" class="rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100">Clear</a>
        </div>
    </form>
</section>

{{-- Job listings --}}
<section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
    @if($jobs->count() > 0)
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach($jobs as $job)
        <div class="flex flex-col rounded-lg bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $job->title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $job->department->name ?? 'N/A' }}</p>
                </div>
                <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-800">Open</span>
            </div>
            <div class="mt-4 space-y-2 text-sm text-gray-600">
                <p class="flex items-center"><i class="fa-solid fa-location-dot text-indigo-500 mr-2 w-4"></i>{{ $job->location ?? 'On-site' }}</p>
                <p class="flex items-center"><i class="fa-solid fa-briefcase text-indigo-500 mr-2 w-4"></i>{{ ucfirst(str_replace('_', ' ', $job->employment_type)) }}</p>
                @if($job->salary_range)
                <p class="flex items-center"><i class="fa-solid fa-coins text-indigo-500 mr-2 w-4"></i>{{ $job->salary_range }}</p>
                @endif
                <p class="flex items-center"><i class="fa-solid fa-users text-indigo-500 mr-2 w-4"></i>{{ $job->vacancies_count }} vacancy/vacancies</p>
                <p class="flex items-center"><i class="fa-solid fa-calendar-xmark text-indigo-500 mr-2 w-4"></i>Closes {{ $job->closing_date ? \Carbon\Carbon::parse($job->closing_date)->format('M d, Y') : 'Open until filled' }}</p>
            </div>
            <div class="mt-5 border-t border-gray-200 pt-4">
                <a href="{{ route('public.jobs.show', $job) }}" class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">View Job <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-8">
        {{ $jobs->links() }}
    </div>
    @else
    <div class="rounded-lg bg-white p-12 text-center shadow-sm border border-gray-200">
        <i class="fa-solid fa-briefcase text-4xl text-gray-300"></i>
        <h3 class="mt-4 text-lg font-semibold text-gray-900">No open positions found</h3>
        <p class="mt-2 text-sm text-gray-500">We currently don't have any matching open positions. Please check back soon or adjust your search.</p>
    </div>
    @endif
</section>
@endsection
