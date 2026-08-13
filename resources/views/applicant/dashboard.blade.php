@extends('layouts.applicant')

@section('title', 'Applicant Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ $applicant->first_name }}!</h1>
        <p class="mt-1 text-sm text-gray-500">Track your applications and explore new opportunities.</p>
    </div>

    <!-- Vue Application Progress Tracker Widget -->
    @if(isset($recentApplications) && count($recentApplications) > 0)
    <div id="vue-app">
        <applicant-progress-tracker
            status="{{ $recentApplications->first()->status }}"
        ></applicant-progress-tracker>
    </div>
    @endif

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3"><i class="fa-solid fa-file-arrow-up text-indigo-600 text-xl"></i></div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-500">Total Applications</p><p class="text-2xl font-bold text-gray-900">{{ $stats['total_applications'] }}</p></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-100 rounded-lg p-3"><i class="fa-solid fa-hourglass-half text-green-600 text-xl"></i></div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-500">Active Applications</p><p class="text-2xl font-bold text-gray-900">{{ $stats['active_applications'] }}</p></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-amber-100 rounded-lg p-3"><i class="fa-solid fa-calendar-check text-amber-600 text-xl"></i></div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-500">Interviews</p><p class="text-2xl font-bold text-gray-900">{{ $stats['interviews'] }}</p></div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-emerald-100 rounded-lg p-3"><i class="fa-solid fa-user-check text-emerald-600 text-xl"></i></div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-500">Offers</p><p class="text-2xl font-bold text-gray-900">{{ $stats['offers'] }}</p></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- My Applications -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">My Applications</h3>
                <a href="{{ route('applicant.jobs') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Browse more jobs</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applied</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($applications as $application)
                        <tr>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $application->jobPosting->title ?? 'N/A' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-500">{{ $application->created_at->format('M d, Y') }}</td>
                            <td class="px-4 py-4">
                                <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ match($application->status) {
                                    'submitted' => 'bg-gray-100 text-gray-800',
                                    'under_review' => 'bg-blue-100 text-blue-800',
                                    'screening' => 'bg-indigo-100 text-indigo-800',
                                    'shortlisted' => 'bg-amber-100 text-amber-800',
                                    'for_interview' => 'bg-purple-100 text-purple-800',
                                    'interviewed' => 'bg-cyan-100 text-cyan-800',
                                    'assessed' => 'bg-teal-100 text-teal-800',
                                    'recommended' => 'bg-green-100 text-green-800',
                                    'hired' => 'bg-emerald-100 text-emerald-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'withdrawn' => 'bg-gray-100 text-gray-500',
                                    default => 'bg-gray-100 text-gray-800',
                                } }}">{{ str_replace('_', ' ', ucfirst($application->status)) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-4 text-sm text-gray-500">You haven't applied to any jobs yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notifications -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Notifications</h3>
            <ul class="divide-y divide-gray-200">
                @forelse($notifications as $notification)
                <li class="py-3">
                    <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                    <p class="text-xs text-gray-500">{{ $notification->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </li>
                @empty
                <li class="py-3 text-sm text-gray-500">No notifications yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Recommended Jobs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recommended Jobs</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($recommendedJobs as $job)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                <h4 class="font-semibold text-gray-900">{{ $job->title }}</h4>
                <p class="text-sm text-gray-500">{{ $job->department->name ?? 'N/A' }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $job->jobPosition->name ?? 'N/A' }}</p>
                <div class="mt-3">
                    <a href="{{ route('applicant.jobs.show', $job) }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">View Details <i class="fa-solid fa-arrow-right ml-1"></i></a>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 col-span-3">No recommended jobs available right now.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
