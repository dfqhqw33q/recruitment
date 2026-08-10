@extends('layouts.app')

@section('title', 'Create Offer Letter')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Offer Letter</h1>
        <p class="mt-1 text-sm text-gray-500">Generate a job offer for a recommended candidate.</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <ul class="list-disc list-inside text-sm font-medium text-red-800">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('recruitment.offers.store') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Candidate</label>
            <select name="application_id" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Select candidate</option>
                @foreach($applications as $application)
                <option value="{{ $application->id }}" @selected(request('application_id') == $application->id)>
                    {{ $application->applicant->full_name }} — {{ $application->jobPosting->title }}
                </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Monthly Salary (₱)</label>
            <input type="number" step="0.01" name="salary" min="0" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Start Date</label>
            <input type="date" name="start_date" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Employment Type</label>
            <select name="employment_type" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="full_time">Full Time</option>
                <option value="part_time">Part Time</option>
                <option value="contract">Contract</option>
                <option value="internship">Internship</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Terms & Conditions</label>
            <textarea name="terms" rows="4" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Benefits</label>
            <textarea name="benefits" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('recruitment.offers.index') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Offer</button>
        </div>
    </form>
</div>
@endsection
