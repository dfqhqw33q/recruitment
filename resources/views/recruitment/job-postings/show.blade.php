@extends('layouts.app')

@section('title', $posting->title)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $posting->title }}</h1>
            <div class="mt-2 flex flex-wrap gap-3 text-sm text-gray-500">
                <span><i class="fa-solid fa-building mr-1"></i>{{ $posting->department->name ?? 'N/A' }}</span>
                <span><i class="fa-solid fa-briefcase mr-1"></i>{{ $posting->jobPosition->name ?? 'N/A' }}</span>
                <span><i class="fa-solid fa-location-dot mr-1"></i>{{ $posting->location ?? 'On-site' }}</span>
                <span><i class="fa-solid fa-people-group mr-1"></i>{{ $posting->vacancies_count }} vacancy(s)</span>
            </div>
        </div>
        <div class="flex space-x-2">
@can('edit_postings')
            <a href="{{ route('recruitment.job-postings.edit', $posting->id) }}" class="rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-500">Edit</a>
            @endcan
            @can('generate_ai_recommendations')
            <form method="POST" action="{{ route('recruitment.ai.generate-posting', $posting) }}">
                @csrf
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><i class="fa-solid fa-robot mr-1"></i>Run AI Analysis</button>
            </form>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <!-- Job Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Job Description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $posting->description }}</p>
            </div>

            <!-- Applicants -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Applicants ({{ $posting->applications->count() }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Applicant</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">AI Match</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($posting->applications as $application)
                            <tr>
                                <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $application->applicant->full_name }}</td>
                                <td class="px-4 py-4"><span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 text-gray-800">{{ str_replace('_', ' ', ucfirst($application->status)) }}</span></td>
                                <td class="px-4 py-4 text-sm text-gray-500">{{ $application->aiRecommendation->match_score ?? 'N/A' }}%</td>
                                <td class="px-4 py-4 text-right">
                                    @can('view_applications')
                                    <a href="{{ route('recruitment.applications.show', $application) }}" class="text-indigo-600 hover:text-indigo-500">View</a>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-4 text-sm text-gray-500 text-center">No applicants yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Job Details</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-medium capitalize">{{ $posting->status }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Employment</dt><dd class="font-medium capitalize">{{ str_replace('_', ' ', $posting->employment_type) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Salary</dt><dd class="font-medium">{{ $posting->salary_range ?? 'Competitive' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Posted</dt><dd class="font-medium">{{ \Carbon\Carbon::parse($posting->posted_date)->format('M d, Y') }}</dd></div>
<div class="flex justify-between"><dt class="text-gray-500">Closing</dt><dd class="font-medium">{{ $posting->closing_date ? \Carbon\Carbon::parse($posting->closing_date)->format('M d, Y') : 'Open' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Required Skills</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse(($posting->required_skills ?? []) as $skill)
                    <span class="inline-flex text-xs font-medium px-2 py-1 rounded-full bg-indigo-100 text-indigo-800">{{ $skill }}</span>
                    @empty
                    <span class="text-sm text-gray-500">No required skills listed.</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
