@extends('layouts.app')

@section('title', 'Start Onboarding')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Start New Hire Onboarding</h1>
        <p class="mt-1 text-sm text-gray-500">Begin the onboarding process for a hired candidate.</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <ul class="list-disc list-inside text-sm font-medium text-red-800">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    @if($applications->isEmpty())
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 text-sm text-amber-800">No hired candidates available for onboarding. Ensure candidates accept job offers first.</div>
    @endif

    <form method="POST" action="{{ route('recruitment.onboarding.store') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Hired Candidate</label>
            <select name="application_id" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Select candidate</option>
                @foreach($applications as $application)
                <option value="{{ $application->id }}">{{ $application->applicant->full_name }} — {{ $application->jobPosting->title }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Start Date</label>
            <input type="date" name="start_date" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Orientation Date</label>
            <input type="date" name="orientation_date" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Training Start</label>
                <input type="date" name="training_start" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Training End</label>
                <input type="date" name="training_end" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('recruitment.onboarding.index') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Start Onboarding</button>
        </div>
    </form>
</div>
@endsection
