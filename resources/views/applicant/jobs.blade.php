@extends('layouts.applicant')

@section('title', 'Browse Jobs')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Available Job Vacancies</h1>
        <p class="mt-1 text-sm text-gray-500">Browse and apply for open positions.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($jobs as $job)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $job->title }}</h3>
                    <p class="text-sm text-gray-500">{{ $job->department->name ?? 'N/A' }}</p>
                </div>
                <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-800">Open</span>
            </div>
            <p class="mt-3 text-sm text-gray-600 line-clamp-3">{{ Str::limit($job->description, 120) }}</p>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-xs text-gray-500"><i class="fa-solid fa-location-dot mr-1"></i>{{ $job->location ?? 'On-site' }}</span>
                <a href="{{ route('applicant.jobs.show', $job) }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">View <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
        </div>
        @empty
        <p class="text-sm text-gray-500 col-span-3">No job vacancies available right now.</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $jobs->links() }}
    </div>
</div>
@endsection
