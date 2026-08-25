@extends('layouts.app')

@section('title', 'Application Details')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">{{ $application->applicant->full_name }}</h1>
                <span class="inline-flex font-mono text-xs font-bold px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $application->reference_code }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Application for {{ $application->jobPosting->title ?? 'N/A' }} &bull; Submitted {{ \Carbon\Carbon::parse($application->applied_at)->format('M d, Y g:i A') }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('recruitment.applications.index') }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Back</a>
            @can('generate_ai_recommendations')
            <form method="POST" action="{{ route('recruitment.ai.generate', $application) }}">
                @csrf
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><i class="fa-solid fa-robot mr-1"></i>Generate AI Recommendation</button>
            </form>
            @endcan
        </div>
    </div>

    <!-- Status Update -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Update Application Status</h3>
        <form method="POST" action="{{ route('recruitment.applications.status', $application) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            @method('PATCH')
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @foreach(config('recruitment.application_statuses', []) as $key => $label)
                    <option value="{{ $key }}" @selected($application->status == $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700">Screening Notes</label>
                <input type="text" name="screening_notes" value="{{ $application->screening_notes }}" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Applicant Profile -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Knockout Warning Banner -->
            @if($application->is_knocked_out)
            <div class="bg-red-50 border border-red-300 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-bold text-red-900">Knockout Screening Criteria Disqualification</h4>
                        <p class="text-xs text-red-700 mt-1 font-medium">{{ $application->knockout_reason ?? 'Candidate did not meet one or more mandatory screening requirements.' }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- 1. APPLICANT SUBMITTED RESUME PREVIEW (INLINE VIEWER) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" id="resumePreviewCard">
                <!-- Header & Controls -->
                <div class="p-4 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-file-pdf"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-bold text-gray-900">Applicant Resume Preview</h3>
                                @if($application->has_resume)
                                <span class="inline-flex items-center text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $application->resume_is_custom ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                    {{ $application->resume_type_label }}
                                </span>
                                @endif
                            </div>
                            @if($application->has_resume)
                            <p class="text-xs text-gray-500 mt-0.5 font-mono">
                                {{ strtoupper($application->resume_extension) }}
                                @if($application->resume_file_size) &bull; {{ $application->resume_file_size }} @endif
                                &bull; <span class="text-gray-400">Instant in-browser preview</span>
                            </p>
                            @else
                            <p class="text-xs text-amber-600 mt-0.5 font-medium">No file attached &bull; Profile data available below</p>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($application->has_resume)
                            <!-- Download Button (Always accessible) -->
                            <a href="{{ route('recruitment.applications.resume.download', $application) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 hover:text-indigo-600 transition-colors"
                               title="Download file to your device">
                                <i class="fa-solid fa-download text-indigo-600"></i>
                                <span>Download</span>
                            </a>

                            <!-- Popout / Open in New Tab -->
                            <a href="{{ route('recruitment.applications.resume.preview', $application) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 hover:text-indigo-600 transition-colors"
                               title="Open preview in new tab">
                                <i class="fa-solid fa-arrow-up-right-from-square text-gray-500"></i>
                                <span>New Tab</span>
                            </a>

                            <!-- Fullscreen Preview Modal Trigger -->
                            <button type="button"
                                    onclick="openFullscreenResumeModal()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md shadow-sm hover:bg-indigo-100 transition-colors"
                                    title="View fullscreen">
                                <i class="fa-solid fa-expand"></i>
                                <span>Fullscreen</span>
                            </button>
                        @endif

                        <!-- Collapse/Expand Preview Button -->
                        <button type="button"
                                onclick="toggleResumeViewer()"
                                id="resumeToggleBtn"
                                class="inline-flex items-center justify-center w-8 h-8 text-gray-500 hover:text-gray-800 hover:bg-gray-200/60 rounded-md transition-colors"
                                title="Toggle Preview Container">
                            <i class="fa-solid fa-chevron-up text-xs transition-transform duration-200" id="resumeToggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Preview Frame Content Area -->
                <div id="resumeViewerContent" class="transition-all duration-300 ease-in-out">
                    @if($application->has_resume)
                        @if($application->resume_extension === 'pdf')
                        <div class="relative bg-gray-100 border-t border-gray-200" style="width:100%; height:850px;">
                            <iframe src="{{ route('recruitment.applications.resume.preview', $application) }}#toolbar=1&navpanes=0&view=FitH"
                                    style="width:100%; height:850px; border:none; display:block; background-color:#f8fafc;"
                                    title="Applicant Resume Preview">
                            </iframe>
                        </div>
                        @elseif(in_array($application->resume_extension, ['png', 'jpg', 'jpeg']))
                        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-center items-center overflow-auto" style="min-height:500px; max-height:850px;">
                            <img src="{{ route('recruitment.applications.resume.preview', $application) }}"
                                 alt="Applicant Resume"
                                 style="max-width:100%; height:auto; display:block;"
                                 class="rounded shadow-sm border border-gray-200">
                        </div>
                        @else
                        <!-- Word / Other document format fallback -->
                        <div class="p-6 bg-amber-50 border-t border-amber-200 text-center">
                            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-3 text-xl font-bold">
                                <i class="fa-solid fa-file-word"></i>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900">Word Document ({{ strtoupper($application->resume_extension) }})</h4>
                            <p class="text-xs text-gray-600 mt-1 max-w-md mx-auto">This document is formatted as a {{ strtoupper($application->resume_extension) }} file. You can download it directly or review the candidate's parsed qualifications below.</p>
                            <div class="mt-4 flex items-center justify-center gap-3">
                                <a href="{{ route('recruitment.applications.resume.download', $application) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-indigo-600 rounded-md hover:bg-indigo-500 shadow-sm">
                                    <i class="fa-solid fa-download"></i> Download {{ strtoupper($application->resume_extension) }} File
                                </a>
                            </div>
                        </div>
                        @endif
                    @else
                    <div class="p-8 text-center bg-gray-50 border-t border-gray-200">
                        <i class="fa-solid fa-file-circle-xmark text-gray-300 text-4xl mb-2"></i>
                        <p class="text-sm font-semibold text-gray-700">No Resume File Attached</p>
                        <p class="text-xs text-gray-500 mt-1">This candidate submitted an application without an attached resume file.</p>
                        <p class="text-xs text-indigo-600 font-medium mt-2">You can review their profile details, education, experience, and skills below.</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Screening Q&A Responses -->
            @if(!empty($application->screening_answers) && is_array($application->screening_answers))
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-2 mb-4 pb-2 border-b border-gray-100">
                    <i class="fa-solid fa-list-check text-indigo-600"></i>
                    <h3 class="text-lg font-semibold text-gray-900">Screening Questionnaire Responses</h3>
                </div>

                <div class="space-y-3">
                    @foreach($application->jobPosting->screening_questions ?? [] as $index => $q)
                    @php
                        $qId = $q['id'] ?? ('q_'.$index);
                        $ans = $application->screening_answers[$qId] ?? 'N/A';
                        $isKnockoutTarget = false;
                        if (!empty($q['knockout_value']) && strtolower((string)$ans) === strtolower((string)$q['knockout_value'])) {
                            $isKnockoutTarget = true;
                        }
                        if (isset($q['min_value']) && is_numeric($q['min_value']) && is_numeric($ans) && (float)$ans < (float)$q['min_value']) {
                            $isKnockoutTarget = true;
                        }
                    @endphp
                    <div class="p-3 rounded-md border {{ $isKnockoutTarget ? 'bg-red-50/70 border-red-200' : 'bg-gray-50 border-gray-200' }} flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold text-gray-700">{{ $index + 1 }}. {{ $q['question'] }}</p>
                            <p class="text-sm font-bold {{ $isKnockoutTarget ? 'text-red-700' : 'text-gray-900' }} mt-0.5">
                                Response: {{ is_array($ans) ? implode(', ', $ans) : $ans }}
                            </p>
                        </div>
                        <div>
                            @if($isKnockoutTarget)
                                <span class="inline-flex text-[11px] font-bold px-2 py-0.5 rounded bg-red-100 text-red-800 border border-red-200">
                                    <i class="fa-solid fa-xmark mr-1 mt-0.5"></i>Disqualified
                                </span>
                            @else
                                <span class="inline-flex text-[11px] font-semibold px-2 py-0.5 rounded bg-green-100 text-green-800 border border-green-200">
                                    <i class="fa-solid fa-check mr-1 mt-0.5"></i>Passed
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- AI Recommendation -->
            @if($application->aiRecommendation)
            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg border border-indigo-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-indigo-900"><i class="fa-solid fa-robot mr-2"></i>AI Recommendation</h3>
                    <span class="text-3xl font-bold text-indigo-600">{{ $application->aiRecommendation->match_score }}%</span>
                </div>
                <div class="mb-4">
                    <span class="inline-flex text-sm font-semibold px-3 py-1 rounded-full {{ $application->aiRecommendation->recommendation == 'highly_recommended' ? 'bg-green-100 text-green-800' : ($application->aiRecommendation->recommendation == 'recommended' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ str_replace('_', ' ', ucfirst($application->aiRecommendation->recommendation)) }}
                    </span>
                </div>
                @if($application->aiRecommendation->explanation)
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-indigo-900 mb-1">Explanation</h4>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $application->aiRecommendation->explanation }}</p>
                </div>
                @endif
                <div class="grid grid-cols-2 gap-4">
                    @if($application->aiRecommendation->skills_match_percentage)
                    <div class="bg-white rounded-lg p-3 border border-indigo-100">
                        <p class="text-xs text-gray-500">Skills Match</p>
                        <p class="text-lg font-bold text-indigo-700">{{ $application->aiRecommendation->skills_match_percentage }}%</p>
                    </div>
                    @endif
                    @if($application->aiRecommendation->confidence_score)
                    <div class="bg-white rounded-lg p-3 border border-indigo-100">
                        <p class="text-xs text-gray-500">Confidence</p>
                        <p class="text-lg font-bold text-indigo-700">{{ $application->aiRecommendation->confidence_score }}%</p>
                    </div>
                    @endif
                </div>
                @if($application->aiRecommendation->missing_skills)
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-amber-800 mb-1">Missing Skills</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($application->aiRecommendation->missing_skills as $skill)
                        <span class="inline-flex text-xs font-medium px-2 py-1 rounded-full bg-amber-100 text-amber-800">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Personal Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Personal Information</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $application->applicant->email }}</dd></div>
                    <div><dt class="text-gray-500">Phone</dt><dd class="font-medium text-gray-900">{{ $application->applicant->phone ?? 'N/A' }}</dd></div>
                    <div><dt class="text-gray-500">Gender</dt><dd class="font-medium text-gray-900 capitalize">{{ $application->applicant->gender ?? 'N/A' }}</dd></div>
                    <div><dt class="text-gray-500">Nationality</dt><dd class="font-medium text-gray-900">{{ $application->applicant->nationality ?? 'N/A' }}</dd></div>
                    <div><dt class="text-gray-500">Location</dt><dd class="font-medium text-gray-900">{{ $application->applicant->city ?? '' }} {{ $application->applicant->country ?? '' }}</dd></div>
                </dl>
                @if($application->applicant->summary)
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Summary</h4>
                    <p class="text-sm text-gray-600">{{ $application->applicant->summary }}</p>
                </div>
                @endif
            </div>

            <!-- Skills -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3"><i class="fa-solid fa-wand-magic-sparkles text-purple-600 mr-2"></i>Skills & Competencies</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($application->applicant->skills as $skill)
                    <span class="inline-flex text-xs font-semibold px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-800 border border-indigo-200">
                        {{ $skill->skill }}
                        @if($skill->proficiency)<span class="ml-1 text-[10px] opacity-75 font-bold">({{ $skill->proficiency }})</span>@endif
                        @if($skill->years_of_experience)<span class="ml-1 text-[10px] opacity-75">&bull; {{ $skill->years_of_experience }}y</span>@endif
                    </span>
                    @empty
                    <span class="text-sm text-gray-500 italic">No skills listed.</span>
                    @endforelse
                </div>
            </div>

            <!-- Experience -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3"><i class="fa-solid fa-briefcase text-blue-600 mr-2"></i>Work Experience</h3>
                <div class="space-y-4">
                    @forelse($application->applicant->experiences as $exp)
                    <div class="border-l-2 border-indigo-500 pl-3">
                        <p class="font-bold text-gray-900">{{ $exp->job_title }} <span class="text-indigo-600">@ {{ $exp->company }}</span></p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('M Y') : 'N/A' }} - 
                            {{ $exp->is_current ? 'Present' : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : 'N/A') }}
                            @if($exp->location) &bull; {{ $exp->location }} @endif
                        </p>
                        @if($exp->description)<p class="text-xs text-gray-700 mt-1 whitespace-pre-line">{{ $exp->description }}</p>@endif
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 italic">No work experience listed.</p>
                    @endforelse
                </div>
            </div>

            <!-- Education -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3"><i class="fa-solid fa-graduation-cap text-emerald-600 mr-2"></i>Education</h3>
                <div class="space-y-4">
                    @forelse($application->applicant->education as $edu)
                    <div class="border-l-2 border-emerald-500 pl-3">
                        <p class="font-bold text-gray-900">{{ $edu->degree }} {{ $edu->field_of_study ? 'in ' . $edu->field_of_study : '' }}</p>
                        <p class="text-xs text-gray-600 font-medium">{{ $edu->institution }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $edu->start_date ? \Carbon\Carbon::parse($edu->start_date)->format('Y') : '' }} - 
                            {{ $edu->end_date ? \Carbon\Carbon::parse($edu->end_date)->format('Y') : 'Present' }}
                            @if($edu->gpa) &bull; GPA: {{ $edu->gpa }} @endif
                            @if($edu->honors) &bull; {{ $edu->honors }} @endif
                        </p>
                        @if($edu->description)<p class="text-xs text-gray-700 mt-1 whitespace-pre-line">{{ $edu->description }}</p>@endif
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 italic">No education listed.</p>
                    @endforelse
                </div>
            </div>

            <!-- Certifications -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3"><i class="fa-solid fa-award text-amber-600 mr-2"></i>Certifications & Licenses</h3>
                <div class="space-y-3">
                    @forelse($application->applicant->certifications as $cert)
                    <div class="p-3 bg-gray-50 rounded-md border border-gray-200">
                        <p class="font-bold text-gray-900 text-sm">{{ $cert->name }}</p>
                        <p class="text-xs text-gray-600">Issued by {{ $cert->issuing_organization }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Issued: {{ $cert->issue_date ? \Carbon\Carbon::parse($cert->issue_date)->format('M Y') : 'N/A' }}
                            @if($cert->expiry_date) &bull; Expires: {{ \Carbon\Carbon::parse($cert->expiry_date)->format('M Y') }} @endif
                            @if($cert->credential_id) &bull; ID: {{ $cert->credential_id }} @endif
                        </p>
                        @if($cert->credential_url)
                        <a href="{{ $cert->credential_url }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 mt-1">
                            Verify Credential <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                        @endif
                    </div>
                    @empty
                    <p class="text-sm text-gray-500 italic">No certifications listed.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Application Info</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Ref Code</dt><dd class="font-mono font-bold text-indigo-700">{{ $application->reference_code }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="font-medium capitalize">{{ str_replace('_', ' ', $application->status) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Applied</dt><dd class="font-medium">{{ \Carbon\Carbon::parse($application->applied_at)->format('M d, Y') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Position</dt><dd class="font-medium">{{ $application->jobPosting->title }}</dd></div>
                </dl>

                <!-- Sidebar Resume Quick Box -->
                <div class="mt-5 pt-4 border-t border-gray-100">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Resume & CV</h4>
                    @if($application->has_resume)
                    <div class="p-3 bg-indigo-50/70 rounded-lg border border-indigo-100 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-indigo-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-file-pdf text-red-500"></i>
                                {{ $application->resume_type_label }}
                            </span>
                            <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded bg-indigo-200/70 text-indigo-800 uppercase">
                                {{ $application->resume_extension }}
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-600 truncate" title="{{ $application->resume_file_name }}">
                            {{ $application->resume_file_name }}
                        </p>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <button type="button"
                                    onclick="openFullscreenResumeModal()"
                                    class="w-full inline-flex items-center justify-center gap-1 py-1.5 text-xs font-semibold text-white bg-indigo-600 rounded hover:bg-indigo-500 shadow-sm transition-colors">
                                <i class="fa-solid fa-eye"></i> Preview
                            </button>
                            <a href="{{ route('recruitment.applications.resume.download', $application) }}"
                               class="w-full inline-flex items-center justify-center gap-1 py-1.5 text-xs font-semibold text-indigo-700 bg-white border border-indigo-200 rounded hover:bg-indigo-50 transition-colors">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200 text-center">
                        <p class="text-xs text-gray-500 italic">No resume file uploaded.</p>
                    </div>
                    @endif
                </div>

                @if($application->custom_notes)
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Additional Candidate Notes</h4>
                    <p class="text-xs text-gray-600 bg-gray-50 p-2.5 rounded border border-gray-200">{{ $application->custom_notes }}</p>
                </div>
                @endif
                @if($application->cover_letter)
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Cover Letter</h4>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $application->cover_letter }}</p>
                </div>
                @endif
            </div>

            <!-- Interviews -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Interviews</h3>
                @forelse($application->interviews as $interview)
                <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm font-medium text-gray-900 capitalize">{{ $interview->type }} Interview</p>
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($interview->scheduled_at)->format('M d, Y h:i A') }}</p>
                    <span class="inline-flex text-xs font-semibold px-2 py-0.5 rounded-full mt-1 {{ $interview->status == 'completed' ? 'bg-green-100 text-green-800' : ($interview->status == 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst($interview->status) }}</span>
                    @if($interview->assessment)
                    <div class="mt-2 text-xs text-gray-600">
                        <p>Score: {{ $interview->assessment->overall_score }}/100</p>
                        <p>Recommendation: {{ $interview->assessment->hiring_recommendation }}</p>
                    </div>
                    @endif
                </div>
                @empty
                <p class="text-sm text-gray-500">No interviews scheduled.</p>
                @endforelse
                @can('schedule_interviews')
                <a href="{{ route('recruitment.interviews.create') }}?application_id={{ $application->id }}" class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">Schedule Interview</a>
                @endcan
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-2">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Actions</h3>
                @can('shortlist_candidates')
                <form method="POST" action="{{ route('recruitment.applications.shortlist', $application) }}">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">Shortlist Candidate</button>
                </form>
                @endcan
                @can('reject_applications')
                <form method="POST" action="{{ route('recruitment.applications.reject', $application) }}" onsubmit="return confirm('Reject this candidate?')">
                    @csrf
                    <input type="text" name="rejection_reason" placeholder="Rejection reason (required)" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm mb-2">
                    <button type="submit" class="w-full rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Reject Candidate</button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>

@if($application->has_resume)
<!-- FULLSCREEN RESUME PREVIEW MODAL -->
<div id="fullscreenResumeModal" class="hidden fixed inset-0 z-50 overflow-hidden bg-gray-900/80 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full h-[95vh] max-w-6xl flex flex-col overflow-hidden border border-gray-200">
        <!-- Modal Top Bar -->
        <div class="px-6 py-3.5 bg-gray-900 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>{{ $application->applicant->full_name }} &mdash; Resume</span>
                        <span class="text-[10px] font-normal px-2 py-0.5 rounded bg-gray-800 text-gray-300 font-mono">{{ $application->reference_code }}</span>
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $application->resume_type_label }} &bull; {{ $application->resume_file_name }}</p>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex items-center gap-2">
                <a href="{{ route('recruitment.applications.resume.download', $application) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-500 rounded-md shadow-sm transition-colors">
                    <i class="fa-solid fa-download"></i>
                    <span>Download</span>
                </a>
                <a href="{{ route('recruitment.applications.resume.preview', $application) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-300 bg-gray-800 hover:bg-gray-700 rounded-md transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>New Tab</span>
                </a>
                <button type="button"
                        onclick="closeFullscreenResumeModal()"
                        class="w-8 h-8 rounded-md bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 flex items-center justify-center transition-colors"
                        title="Close Modal (Esc)">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Modal Iframe Body -->
        <div class="flex-1 bg-gray-100 relative overflow-hidden">
            @if($application->resume_extension === 'pdf')
            <iframe src="{{ route('recruitment.applications.resume.preview', $application) }}#toolbar=1&navpanes=0&view=FitH"
                    style="width:100%; height:100%; min-height:80vh; border:none; display:block;"
                    title="Fullscreen Resume Viewer">
            </iframe>
            @elseif(in_array($application->resume_extension, ['png', 'jpg', 'jpeg']))
            <div class="w-full h-full flex items-center justify-center p-4 overflow-auto">
                <img src="{{ route('recruitment.applications.resume.preview', $application) }}"
                     alt="Applicant Resume"
                     class="max-w-full max-h-full object-contain rounded shadow">
            </div>
            @else
            <div class="w-full h-full flex items-center justify-center p-6 text-center">
                <div class="bg-white p-8 rounded-xl shadow-lg max-w-md">
                    <i class="fa-solid fa-file-word text-blue-600 text-5xl mb-4"></i>
                    <h4 class="text-base font-bold text-gray-900">Word Document Preview</h4>
                    <p class="text-xs text-gray-600 mt-2">Word files (.docx) are downloaded to be opened in your word processor.</p>
                    <a href="{{ route('recruitment.applications.resume.download', $application) }}"
                       class="mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-indigo-600 rounded-md hover:bg-indigo-500">
                        <i class="fa-solid fa-download"></i> Download {{ $application->resume_file_name }}
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function toggleResumeViewer() {
    const content = document.getElementById('resumeViewerContent');
    const icon = document.getElementById('resumeToggleIcon');
    if (!content || !icon) return;

    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('rotate-180');
    } else {
        content.classList.add('hidden');
        icon.classList.add('rotate-180');
    }
}

function openFullscreenResumeModal() {
    const modal = document.getElementById('fullscreenResumeModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeFullscreenResumeModal() {
    const modal = document.getElementById('fullscreenResumeModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFullscreenResumeModal();
    }
});
</script>
@endpush
@endsection
