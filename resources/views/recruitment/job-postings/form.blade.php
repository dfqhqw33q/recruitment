@extends('layouts.app')

@section('title', $posting ? 'Edit Job Posting' : 'New Job Posting')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $posting ? 'Edit Job Posting' : 'New Job Posting' }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $posting ? 'Update the job posting details.' : 'Create a new job vacancy.' }}</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-400 p-4">
        <ul class="list-disc list-inside text-sm font-medium text-red-800">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ $posting ? route('recruitment.job-postings.update', $posting) : route('recruitment.job-postings.store') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        @if($posting)@method('PUT')@endif

        <div>
            <label class="block text-sm font-medium text-gray-700">Job Title</label>
            <input type="text" name="title" value="{{ old('title', $posting->title ?? '') }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-gray-700">Department</label>
                <select name="department_id" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Select department</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(old('department_id', $posting->department_id ?? '') == $dept->id)>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Job Position</label>
                <select name="job_position_id" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Select position</option>
                    @foreach($positions as $position)
                    <option value="{{ $position->id }}" @selected(old('job_position_id', $posting->job_position_id ?? '') == $position->id)>{{ $position->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Vacancies Count</label>
                <input type="number" name="vacancies_count" min="1" value="{{ old('vacancies_count', $posting->vacancies_count ?? 1) }}" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Employment Type</label>
                <select name="employment_type" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="full_time" @selected(old('employment_type', $posting->employment_type ?? '') == 'full_time')>Full Time</option>
                    <option value="part_time" @selected(old('employment_type', $posting->employment_type ?? '') == 'part_time')>Part Time</option>
                    <option value="contract" @selected(old('employment_type', $posting->employment_type ?? '') == 'contract')>Contract</option>
                    <option value="internship" @selected(old('employment_type', $posting->employment_type ?? '') == 'internship')>Internship</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="draft" @selected(old('status', $posting->status ?? '') == 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $posting->status ?? '') == 'published')>Published</option>
                    <option value="closed" @selected(old('status', $posting->status ?? '') == 'closed')>Closed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" name="location" value="{{ old('location', $posting->location ?? '') }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Salary Range</label>
                <input type="text" name="salary_range" value="{{ old('salary_range', $posting->salary_range ?? '') }}" placeholder="e.g. ₱50,000 - ₱70,000" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Closing Date</label>
                <input type="date" name="closing_date" value="{{ old('closing_date', $posting->closing_date ?? '') }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Summary</label>
            <textarea name="summary" rows="2" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('summary', $posting->summary ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="5" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $posting->description ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Required Skills (comma separated)</label>
            <input type="text" name="required_skills[]" value="{{ old('required_skills.0', isset($posting) && $posting->required_skills ? implode(', ', $posting->required_skills) : '') }}" placeholder="e.g. PHP, Laravel, MySQL" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        <!-- Dynamic Screening Questions Builder -->
        <div class="border-t border-gray-200 pt-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-base font-bold text-gray-900"><i class="fa-solid fa-list-check text-indigo-600 mr-2"></i>Candidate Screening Questionnaire & Knockout Rules</h3>
                    <p class="text-xs text-gray-500">Define custom screening questions and knockout rules to automatically screen applicants upon submission.</p>
                </div>
                <button type="button" onclick="addScreeningQuestion()" class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors">
                    <i class="fa-solid fa-plus mr-1"></i>Add Question
                </button>
            </div>

            <div id="questions-container" class="space-y-4 mt-4">
                @php
                    $existingQuestions = old('screening_questions', $posting->screening_questions ?? []);
                @endphp

                @forelse($existingQuestions as $i => $q)
                <div class="question-row bg-gray-50 p-4 rounded-lg border border-gray-200 relative space-y-3">
                    <button type="button" onclick="this.closest('.question-row').remove()" class="absolute top-3 right-3 text-gray-400 hover:text-red-600 text-sm">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                    
                    <input type="hidden" name="screening_questions[{{ $i }}][id]" value="{{ $q['id'] ?? ('q_'.($i+1)) }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700">Question Text</label>
                            <input type="text" name="screening_questions[{{ $i }}][question]" value="{{ $q['question'] ?? '' }}" required placeholder="e.g. Are you authorized to work in this location?" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700">Question Type</label>
                            <select name="screening_questions[{{ $i }}][type]" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="yes_no" @selected(($q['type'] ?? '') == 'yes_no')>Yes / No Radio</option>
                                <option value="number" @selected(($q['type'] ?? '') == 'number')>Numeric Input</option>
                                <option value="text" @selected(($q['type'] ?? '') == 'text')>Short Text</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2 border-t border-gray-200 text-xs">
                        <div class="flex items-center">
                            <label class="inline-flex items-center font-medium text-gray-700">
                                <input type="checkbox" name="screening_questions[{{ $i }}][is_required]" value="1" @checked(!empty($q['is_required'])) class="rounded text-indigo-600 focus:ring-indigo-500 mr-2"> Mandatory Question
                            </label>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700">Knockout Answer (Disqualifies if matched)</label>
                            <input type="text" name="screening_questions[{{ $i }}][knockout_value]" value="{{ $q['knockout_value'] ?? '' }}" placeholder="e.g. No" class="mt-1 block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700">Minimum Numeric Value (Knockout if below)</label>
                            <input type="number" step="any" name="screening_questions[{{ $i }}][min_value]" value="{{ $q['min_value'] ?? '' }}" placeholder="e.g. 2" class="mt-1 block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
                @empty
                <div id="no-questions-placeholder" class="text-center py-6 bg-gray-50 rounded-lg border border-dashed border-gray-300 text-gray-500 text-xs">
                    No screening questions added yet. Click "Add Question" above to create knockout rules.
                </div>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <a href="{{ route('recruitment.job-postings.index') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ $posting ? 'Update' : 'Create' }} Job Posting</button>
        </div>
    </form>
</div>

<script>
    let qCounter = {{ count($existingQuestions) }};

    function addScreeningQuestion() {
        const placeholder = document.getElementById('no-questions-placeholder');
        if (placeholder) placeholder.remove();

        qCounter++;
        const container = document.getElementById('questions-container');
        const i = Date.now();
        const qId = 'q_' + qCounter;

        const html = `
        <div class="question-row bg-gray-50 p-4 rounded-lg border border-gray-200 relative space-y-3">
            <button type="button" onclick="this.closest('.question-row').remove()" class="absolute top-3 right-3 text-gray-400 hover:text-red-600 text-sm">
                <i class="fa-solid fa-trash"></i>
            </button>
            
            <input type="hidden" name="screening_questions[\${i}][id]" value="\${qId}">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-700">Question Text</label>
                    <input type="text" name="screening_questions[\${i}][question]" required placeholder="e.g. Are you authorized to work in this location?" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700">Question Type</label>
                    <select name="screening_questions[\${i}][type]" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="yes_no">Yes / No Radio</option>
                        <option value="number">Numeric Input</option>
                        <option value="text">Short Text</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2 border-t border-gray-200 text-xs">
                <div class="flex items-center">
                    <label class="inline-flex items-center font-medium text-gray-700">
                        <input type="checkbox" name="screening_questions[\${i}][is_required]" value="1" checked class="rounded text-indigo-600 focus:ring-indigo-500 mr-2"> Mandatory Question
                    </label>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Knockout Answer (Disqualifies if matched)</label>
                    <input type="text" name="screening_questions[\${i}][knockout_value]" placeholder="e.g. No" class="mt-1 block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block font-semibold text-gray-700">Minimum Numeric Value (Knockout if below)</label>
                    <input type="number" step="any" name="screening_questions[\${i}][min_value]" placeholder="e.g. 2" class="mt-1 block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>
        `;

        container.insertAdjacentHTML('beforeend', html);
    }
</script>
@endsection
