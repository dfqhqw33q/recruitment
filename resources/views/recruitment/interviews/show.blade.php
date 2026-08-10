@extends('layouts.app')

@section('title', 'Interview Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Interview Details</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $interview->application->applicant->full_name }} — {{ $interview->application->jobPosting->title }}</p>
        </div>
        <div class="flex space-x-2">
            @can('record_assessments')
            <a href="{{ route('recruitment.interviews.assessment', $interview) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><i class="fa-solid fa-clipboard-check mr-1"></i>Record Assessment</a>
            @endcan
            @can('schedule_interviews')
            <a href="{{ route('recruitment.interviews.edit', $interview) }}" class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">Edit</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <!-- Interview Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Interview Information</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Type</dt><dd class="font-medium text-gray-900 capitalize">{{ $interview->type }}</dd></div>
                    <div><dt class="text-gray-500">Round</dt><dd class="font-medium text-gray-900">Round {{ $interview->round }}</dd></div>
                    <div><dt class="text-gray-500">Scheduled</dt><dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($interview->scheduled_at)->format('M d, Y h:i A') }}</dd></div>
                    <div><dt class="text-gray-500">Duration</dt><dd class="font-medium text-gray-900">{{ $interview->duration_minutes }} minutes</dd></div>
                    <div><dt class="text-gray-500">Interviewer</dt><dd class="font-medium text-gray-900">{{ $interview->interviewer->name }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd class="font-medium capitalize">{{ $interview->status }}</dd></div>
                    @if($interview->location)
                    <div><dt class="text-gray-500">Location</dt><dd class="font-medium text-gray-900">{{ $interview->location }}</dd></div>
                    @endif
                    @if($interview->meeting_link)
                    <div><dt class="text-gray-500">Meeting Link</dt><dd class="font-medium text-indigo-600"><a href="{{ $interview->meeting_link }}" target="_blank">Join Meeting</a></dd></div>
                    @endif
                </dl>
                @if($interview->notes)
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Notes</h4>
                    <p class="text-sm text-gray-600">{{ $interview->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Assessment -->
            @if($interview->assessment)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Assessment Results</h3>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 mb-4">
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold {{ $interview->assessment->overall_score >= 80 ? 'text-green-600' : ($interview->assessment->overall_score >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $interview->assessment->overall_score }}</p>
                        <p class="text-xs text-gray-500">Overall Score</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $interview->assessment->technical_score }}</p>
                        <p class="text-xs text-gray-500">Technical</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $interview->assessment->communication_score }}</p>
                        <p class="text-xs text-gray-500">Communication</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $interview->assessment->cultural_fit_score }}</p>
                        <p class="text-xs text-gray-500">Cultural Fit</p>
                    </div>
                </div>
                <div class="mb-4">
                    <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ $interview->assessment->hiring_recommendation == 'hire' ? 'bg-green-100 text-green-800' : ($interview->assessment->hiring_recommendation == 'consider' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($interview->assessment->hiring_recommendation) }}</span>
                </div>
                @if($interview->assessment->strengths || $interview->assessment->weaknesses)
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @if($interview->assessment->strengths)
                    <div>
                        <h4 class="text-sm font-semibold text-green-700 mb-1">Strengths</h4>
                        <p class="text-sm text-gray-600">{{ $interview->assessment->strengths }}</p>
                    </div>
                    @endif
                    @if($interview->assessment->weaknesses)
                    <div>
                        <h4 class="text-sm font-semibold text-red-700 mb-1">Weaknesses</h4>
                        <p class="text-sm text-gray-600">{{ $interview->assessment->weaknesses }}</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Candidate</h3>
                <p class="font-medium text-gray-900">{{ $interview->application->applicant->full_name }}</p>
                <p class="text-sm text-gray-500">{{ $interview->application->applicant->email }}</p>
                <a href="{{ route('recruitment.applications.show', $interview->application) }}" class="mt-3 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">View Application</a>
            </div>
        </div>
    </div>
</div>
@endsection
