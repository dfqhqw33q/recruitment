@extends('layouts.app')

@section('title', 'AI Recommendations')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">AI Recommendations</h1>
            <p class="mt-1 text-sm text-gray-500">AI-assisted candidate ranking and match scores.</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 flex flex-wrap gap-3">
        <select name="recommendation" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Recommendations</option>
            <option value="highly_recommended" @selected(request('recommendation') == 'highly_recommended')>Highly Recommended</option>
            <option value="recommended" @selected(request('recommendation') == 'recommended')>Recommended</option>
            <option value="not_recommended" @selected(request('recommendation') == 'not_recommended')>Not Recommended</option>
        </select>
        <select name="job_posting_id" class="rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">All Positions</option>
            @foreach($postings as $posting)
            <option value="{{ $posting->id }}" @selected(request('job_posting_id') == $posting->id)>{{ $posting->title }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
    </form>

    <!-- Ranking -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Candidate Ranking</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Match Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recommendation</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recommendations as $index => $rec)
                <tr>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $index < 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600' }} font-bold text-sm">{{ $index + 1 }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $rec->application->applicant->full_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $rec->jobPosting->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 mr-2 max-w-[100px]">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $rec->match_score }}%"></div>
                            </div>
                            <span class="text-sm font-bold {{ $rec->match_score >= 80 ? 'text-green-600' : ($rec->match_score >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $rec->match_score }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full {{ $rec->recommendation == 'highly_recommended' ? 'bg-green-100 text-green-800' : ($rec->recommendation == 'recommended' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                            {{ str_replace('_', ' ', ucfirst($rec->recommendation)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('recruitment.ai.show', $rec) }}" class="text-indigo-600 hover:text-indigo-500">View Details</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-4 text-sm text-gray-500 text-center">No AI recommendations found. Generate recommendations from a job posting or application.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $recommendations->links() }}
    </div>
</div>
@endsection
