@extends('layouts.public')

@section('title', $posting->title)

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('public.careers') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500"><i class="fa-solid fa-arrow-left mr-2"></i>Back to careers</a>

    <div class="mt-6 rounded-lg bg-white shadow-sm border border-gray-200 p-6 md:p-8">
        <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">{{ $posting->title }}</h1>
                <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm text-gray-500">
                    <span><i class="fa-solid fa-building text-indigo-500 mr-1"></i>{{ $posting->department->name ?? 'N/A' }}</span>
                    <span><i class="fa-solid fa-briefcase text-indigo-500 mr-1"></i>{{ $posting->jobPosition->name ?? 'N/A' }}</span>
                    <span><i class="fa-solid fa-location-dot text-indigo-500 mr-1"></i>{{ $posting->location ?? 'On-site' }}</span>
                </div>
            </div>
            <span class="inline-flex text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-800"><i class="fa-solid fa-circle text-[6px] mr-1 mt-1.5"></i>Open</span>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 border-t border-gray-200 pt-6 sm:grid-cols-4">
            <div class="rounded-lg bg-gray-50 p-3 text-center">
                <p class="text-xs text-gray-500">Employment Type</p>
                <p class="mt-1 text-sm font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $posting->employment_type) }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 text-center">
                <p class="text-xs text-gray-500">Vacancies</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $posting->vacancies_count }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 text-center">
                <p class="text-xs text-gray-500">Posted</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $posting->posted_date ? \Carbon\Carbon::parse($posting->posted_date)->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 p-3 text-center">
                <p class="text-xs text-gray-500">Closes</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $posting->closing_date ? \Carbon\Carbon::parse($posting->closing_date)->format('M d, Y') : 'Open until filled' }}</p>
            </div>
        </div>

        @if($posting->salary_range)
        <div class="mt-4 flex items-center justify-center rounded-lg bg-indigo-50 p-3 text-sm">
            <i class="fa-solid fa-coins text-indigo-500 mr-2"></i>
            <span class="font-medium text-indigo-800">Salary Range: {{ $posting->salary_range }}</span>
        </div>
        @endif

        <div class="mt-8 space-y-8">
            @if($posting->summary)
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Job Summary</h3>
                <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $posting->summary }}</p>
            </div>
            @endif

            @if($posting->description)
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Job Description</h3>
                <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $posting->description }}</p>
            </div>
            @endif

            @if(!empty($posting->requirements))
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Responsibilities & Requirements</h3>
                <ul class="mt-2 space-y-2">
                    @foreach($posting->requirements as $requirement)
                    <li class="text-sm text-gray-700 flex items-start"><i class="fa-solid fa-check text-green-500 mt-1 mr-2"></i>{{ $requirement }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($posting->required_skills))
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Required Skills</h3>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($posting->required_skills as $skill)
                    <span class="inline-flex text-xs font-medium px-3 py-1 rounded-full bg-indigo-100 text-indigo-800">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($posting->preferred_skills))
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Preferred Skills</h3>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($posting->preferred_skills as $skill)
                    <span class="inline-flex text-xs font-medium px-3 py-1 rounded-full bg-gray-100 text-gray-700">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($posting->qualifications))
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Qualifications</h3>
                <ul class="mt-2 space-y-2">
                    @foreach($posting->qualifications as $qualification)
                    <li class="text-sm text-gray-700 flex items-start"><i class="fa-solid fa-award text-indigo-500 mt-1 mr-2"></i>{{ $qualification }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <div class="mt-8 border-t border-gray-200 pt-6">
            @auth
                @if(auth()->user()->roles->pluck('name')->first() == 'Applicant')
                    <a href="{{ route('applicant.jobs.show', $posting) }}" class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 md:w-auto">
                        <i class="fa-solid fa-paper-plane mr-2"></i>Apply Now
                    </a>
                @else
                    <p class="text-sm text-gray-500">You are logged in as a staff member. To apply for this position, please use an applicant account.</p>
                @endif
            @else
                <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 md:w-auto">
                    <i class="fa-solid fa-paper-plane mr-2"></i>Apply Now
                </a>
                <p class="mt-3 text-sm text-gray-500">You'll be asked to log in or create an applicant account to continue.</p>
            @endauth
        </div>
    </div>
</section>
@endsection
