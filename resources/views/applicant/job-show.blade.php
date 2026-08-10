@extends('layouts.applicant')

@section('title', $posting->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('applicant.jobs') }}" class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-500">
        <i class="fa-solid fa-arrow-left mr-2"></i>Back to jobs
    </a>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 md:p-8">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $posting->title }}</h1>
                <div class="mt-2 flex flex-wrap gap-3 text-sm text-gray-500">
                    <span><i class="fa-solid fa-building text-indigo-500 mr-1"></i>{{ $posting->department->name ?? 'N/A' }}</span>
                    <span><i class="fa-solid fa-briefcase text-indigo-500 mr-1"></i>{{ $posting->jobPosition->name ?? 'N/A' }}</span>
                    <span><i class="fa-solid fa-location-dot text-indigo-500 mr-1"></i>{{ $posting->location ?? 'On-site' }}</span>
                    <span><i class="fa-solid fa-clock text-indigo-500 mr-1"></i>{{ ucfirst(str_replace('_', ' ', $posting->employment_type)) }}</span>
                </div>
            </div>
            <span class="inline-flex text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-800">
                <i class="fa-solid fa-circle text-[6px] mr-1 mt-1"></i>Open
            </span>
        </div>

        <div class="mt-6 space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Job Description</h3>
                <p class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ $posting->description }}</p>
            </div>

            @if(!empty($posting->requirements))
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Requirements</h3>
                <ul class="mt-2 space-y-1">
                    @foreach($posting->requirements as $requirement)
                    <li class="text-sm text-gray-700 flex items-start">
                        <i class="fa-solid fa-check text-green-500 mt-1 mr-2"></i>{{ $requirement }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div>
                <h3 class="text-lg font-semibold text-gray-900">Salary & Details</h3>
                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <p class="text-xs text-gray-500">Salary Range</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $posting->salary_range ?? 'Competitive' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Closing Date</p>
                        <p class="text-sm font-semibold text-gray-900">{{ $posting->closing_date ? \Carbon\Carbon::parse($posting->closing_date)->format('M d, Y') : 'Open until filled' }}</p>
                    </div>
                </div>
            </div>
        </div>

        @php
            $applicant = auth()->user()->applicant;
            $existingApp = $applicant ? \App\Models\Application::where('applicant_id', $applicant->id)->where('job_posting_id', $posting->id)->first() : null;
        @endphp

        <div class="mt-8 border-t border-gray-200 pt-6">
            @if($existingApp)
                <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-4">
                    <div class="flex items-center">
                        <i class="fa-solid fa-circle-check text-indigo-600 text-xl mr-3"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-indigo-900">Already Applied</h4>
                            <p class="text-xs text-indigo-700 mt-1">
                                You submitted an application for this position on {{ $existingApp->applied_at ? $existingApp->applied_at->format('M d, Y') : 'N/A' }}.
                            </p>
                            <div class="mt-2 flex items-center gap-3">
                                <span class="font-mono text-xs font-bold bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded">
                                    Ref: {{ $existingApp->reference_code }}
                                </span>
                                <span class="text-xs font-medium text-gray-600 capitalize">
                                    Status: <span class="font-bold text-gray-900">{{ str_replace('_', ' ', $existingApp->status) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('applicant.track') }}" class="inline-flex items-center text-xs font-semibold text-indigo-700 hover:text-indigo-900">
                            <i class="fa-solid fa-arrow-right mr-1"></i>Go to My Applications Track
                        </a>
                    </div>
                </div>
            @else
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i class="fa-solid fa-paper-plane text-indigo-600 mr-2"></i>Apply for this Position</h3>
                
                <form method="POST" action="{{ route('applicant.apply', $posting) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Dynamic Screening Questions -->
                    @if(!empty($posting->screening_questions) && is_array($posting->screening_questions))
                    <div class="bg-indigo-50/70 border border-indigo-200 rounded-lg p-5 space-y-4">
                        <div class="flex items-center gap-2 pb-2 border-b border-indigo-100">
                            <i class="fa-solid fa-list-check text-indigo-600"></i>
                            <h4 class="text-sm font-bold text-indigo-900 uppercase tracking-wider">Candidate Screening Questionnaire</h4>
                        </div>

                        @foreach($posting->screening_questions as $index => $q)
                        @php $qId = $q['id'] ?? ('q_'.$index); @endphp
                        <div class="bg-white p-4 rounded-md border border-gray-200 space-y-2">
                            <label class="block text-sm font-semibold text-gray-900">
                                {{ $index + 1 }}. {{ $q['question'] }}
                                @if(!empty($q['is_required']))
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            @if(($q['type'] ?? 'text') === 'yes_no')
                                <div class="flex items-center space-x-6 mt-1">
                                    <label class="inline-flex items-center text-sm text-gray-700 font-medium">
                                        <input type="radio" name="screening_answers[{{ $qId }}]" value="Yes" @if(!empty($q['is_required'])) required @endif class="text-indigo-600 focus:ring-indigo-500 mr-2"> Yes
                                    </label>
                                    <label class="inline-flex items-center text-sm text-gray-700 font-medium">
                                        <input type="radio" name="screening_answers[{{ $qId }}]" value="No" @if(!empty($q['is_required'])) required @endif class="text-indigo-600 focus:ring-indigo-500 mr-2"> No
                                    </label>
                                </div>
                            @elseif(($q['type'] ?? 'text') === 'number')
                                <input type="number" step="any" name="screening_answers[{{ $qId }}]" placeholder="Enter number..." @if(!empty($q['is_required'])) required @endif class="block w-full sm:w-48 rounded-md border border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @elseif(($q['type'] ?? 'text') === 'select' && !empty($q['options']))
                                <select name="screening_answers[{{ $qId }}]" @if(!empty($q['is_required'])) required @endif class="block w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Select an Option --</option>
                                    @foreach($q['options'] as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="screening_answers[{{ $qId }}]" placeholder="Your response..." @if(!empty($q['is_required'])) required @endif class="block w-full rounded-md border border-gray-300 px-3 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cover Letter (Optional)</label>
                        <textarea name="cover_letter" rows="4" placeholder="Introduce yourself and explain why you're a great fit for this position..." class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Custom Resume (Optional)</label>
                            <input type="file" name="custom_resume" accept=".pdf,.doc,.docx" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <p class="text-xs text-gray-500 mt-1">
                                @if($applicant && $applicant->resume_path)
                                    <i class="fa-solid fa-circle-info text-indigo-500 mr-1"></i>Your profile default resume will be used if left blank.
                                @else
                                    Upload a PDF or Word document (Max 5MB).
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Additional Notes / Portfolio Link (Optional)</label>
                            <input type="text" name="custom_notes" placeholder="e.g. GitHub link, portfolio URL, or availability notice" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                            <i class="fa-solid fa-paper-plane mr-2"></i>Submit Application
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
