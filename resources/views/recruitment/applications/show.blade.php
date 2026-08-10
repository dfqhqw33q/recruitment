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
                @if($application->custom_resume_path)
                <div class="mt-4 p-3 bg-indigo-50 rounded-md border border-indigo-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-red-500 text-lg"></i>
                        <span class="text-xs font-semibold text-indigo-900">Custom Position CV</span>
                    </div>
                    <a href="{{ Storage::url($application->custom_resume_path) }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Open File <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                    </a>
                </div>
                @endif
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
@endsection
