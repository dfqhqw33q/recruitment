@extends('layouts.app')

@section('title', 'Record Assessment')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Record Interview Assessment</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $interview->application->applicant->full_name }} — {{ $interview->application->jobPosting->title }} ({{ ucfirst($interview->type) }} Interview)</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <ul class="list-disc list-inside text-sm font-medium text-red-800">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('recruitment.interviews.assessment.store', $interview) }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Communication (0-100)</label>
                <input type="number" name="communication_score" min="0" max="100" value="{{ old('communication_score', $interview->assessment->communication_score ?? '') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Technical (0-100)</label>
                <input type="number" name="technical_score" min="0" max="100" value="{{ old('technical_score', $interview->assessment->technical_score ?? '') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Experience (0-100)</label>
                <input type="number" name="experience_score" min="0" max="100" value="{{ old('experience_score', $interview->assessment->experience_score ?? '') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Cultural Fit (0-100)</label>
                <input type="number" name="cultural_fit_score" min="0" max="100" value="{{ old('cultural_fit_score', $interview->assessment->cultural_fit_score ?? '') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Leadership (0-100)</label>
                <input type="number" name="leadership_score" min="0" max="100" value="{{ old('leadership_score', $interview->assessment->leadership_score ?? '') }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Problem Solving (0-100)</label>
                <input type="number" name="problem_solving_score" min="0" max="100" value="{{ old('problem_solving_score', $interview->assessment->problem_solving_score ?? '') }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Hiring Recommendation</label>
            <select name="recommendation" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="hire" @selected(old('recommendation', $interview->assessment->recommendation ?? '') == 'hire')>Hire</option>
                <option value="consider" @selected(old('recommendation', $interview->assessment->recommendation ?? '') == 'consider')>Consider</option>
                <option value="reject" @selected(old('recommendation', $interview->assessment->recommendation ?? '') == 'reject')>Reject</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Strengths</label>
            <textarea name="strengths" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('strengths', $interview->assessment->strengths ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Weaknesses</label>
            <textarea name="weaknesses" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('weaknesses', $interview->assessment->weaknesses ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Comments</label>
            <textarea name="comments" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('comments', $interview->assessment->comments ?? '') }}</textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('recruitment.interviews.show', $interview) }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Submit Assessment</button>
        </div>
    </form>
</div>
@endsection
