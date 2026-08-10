@extends('layouts.app')

@section('title', 'Edit Interview')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Interview</h1>
        <p class="mt-1 text-sm text-gray-500">Update the interview details.</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <ul class="list-disc list-inside text-sm font-medium text-red-800">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('recruitment.interviews.update', $interview) }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700">Candidate</label>
            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $interview->application->applicant->full_name }} — {{ $interview->application->jobPosting->title }}</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Interviewer</label>
            <select name="interviewer_id" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @foreach($interviewers as $interviewer)
                <option value="{{ $interviewer->id }}" @selected($interview->interviewer_id == $interviewer->id)>{{ $interviewer->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Scheduled At</label>
            <input type="datetime-local" name="scheduled_at" value="{{ \Carbon\Carbon::parse($interview->scheduled_at)->format('Y-m-d\TH:i') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="scheduled" @selected($interview->status == 'scheduled')>Scheduled</option>
                    <option value="completed" @selected($interview->status == 'completed')>Completed</option>
                    <option value="cancelled" @selected($interview->status == 'cancelled')>Cancelled</option>
                    <option value="no_show" @selected($interview->status == 'no_show')>No Show</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="technical" @selected($interview->type == 'technical')>Technical</option>
                    <option value="behavioral" @selected($interview->type == 'behavioral')>Behavioral</option>
                    <option value="panel" @selected($interview->type == 'panel')>Panel</option>
                    <option value="hr" @selected($interview->type == 'hr')>HR</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                <input type="number" name="duration_minutes" min="15" value="{{ $interview->duration_minutes }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" name="location" value="{{ $interview->location }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Meeting Link</label>
            <input type="url" name="meeting_link" value="{{ $interview->meeting_link }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ $interview->notes }}</textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('recruitment.interviews.show', $interview) }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Update Interview</button>
        </div>
    </form>
</div>
@endsection
