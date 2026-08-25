@extends('layouts.applicant')

@section('title', 'My Applications')

@section('content')
<div class="space-y-6 text-left" style="text-align:left;">
    <div style="text-align:left;">
        <h1 class="text-2xl font-bold text-gray-900" style="text-align:left;">My Applications</h1>
        <p class="mt-1 text-sm text-gray-500" style="text-align:left;">Track and manage your submitted job applications.</p>
    </div>

    <div class="space-y-6 text-left" style="text-align:left;">
        @forelse($applications as $application)
        @php
            $stages = [
                ['key' => 'submitted',     'label' => 'Submitted',   'icon' => 'fa-paper-plane'],
                ['key' => 'under_review',  'label' => 'Under Review', 'icon' => 'fa-magnifying-glass'],
                ['key' => 'screening',     'label' => 'Screening',   'icon' => 'fa-clipboard-list'],
                ['key' => 'shortlisted',   'label' => 'Shortlisted', 'icon' => 'fa-star'],
                ['key' => 'for_interview', 'label' => 'Interview',   'icon' => 'fa-video'],
                ['key' => 'assessed',      'label' => 'Assessed',    'icon' => 'fa-chart-bar'],
                ['key' => 'recommended',   'label' => 'Recommended', 'icon' => 'fa-thumbs-up'],
                ['key' => 'hired',         'label' => 'Hired',       'icon' => 'fa-champagne-glasses'],
            ];

            $statusOrder = collect($stages)->pluck('key')->flip();
            $currentStatus = $application->status;
            $isTerminal = in_array($currentStatus, ['rejected', 'withdrawn']);

            // Find the current stage index
            if ($isTerminal) {
                $currentIdx = -1;
            } else {
                $currentIdx = $statusOrder->get($currentStatus, 0);
            }
        @endphp

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden text-left" style="text-align:left;">
            <!-- Card Header -->
            <div style="display:flex; flex-direction:row; align-items:center; justify-content:space-between; gap:1rem; padding:1.25rem; border-bottom:1px solid #f3f4f6; text-align:left;">
                <div style="text-align:left; display:flex; flex-direction:column; align-items:flex-start; justify-content:flex-start;">
                    <div style="display:flex; align-items:center; gap:0.5rem; justify-content:flex-start; text-align:left;">
                        <h3 class="text-lg font-semibold text-gray-900" style="text-align:left; margin:0; font-size:1.125rem;">{{ $application->jobPosting->title ?? 'N/A' }}</h3>
                        <span class="inline-flex font-mono text-xs font-bold px-2.5 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $application->reference_code }}</span>
                    </div>
                    <p class="text-sm text-gray-500" style="text-align:left; margin-top:0.25rem;">
                        <i class="fa-solid fa-building mr-1 text-gray-400"></i>{{ $application->jobPosting->department->name ?? 'N/A' }} &bull;
                        <i class="fa-solid fa-calendar mr-1 text-gray-400"></i>Applied {{ $application->applied_at ? $application->applied_at->format('M d, Y') : 'N/A' }}
                    </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if($application->is_knocked_out)
                        <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-full bg-orange-100 text-orange-800 border border-orange-200">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>Screened Out
                        </span>
                    @endif
                    <span class="inline-flex text-xs font-semibold px-3 py-1 rounded-full {{ match($application->status) {
                        'submitted'     => 'bg-blue-50 text-blue-700 border border-blue-200',
                        'under_review'  => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                        'screening'     => 'bg-purple-50 text-purple-700 border border-purple-200',
                        'shortlisted'   => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                        'for_interview' => 'bg-amber-50 text-amber-700 border border-amber-200',
                        'interviewed'   => 'bg-cyan-50 text-cyan-700 border border-cyan-200',
                        'assessed'      => 'bg-teal-50 text-teal-700 border border-teal-200',
                        'recommended'   => 'bg-green-50 text-green-700 border border-green-200',
                        'hired'         => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
                        'rejected'      => 'bg-red-50 text-red-700 border border-red-200',
                        'withdrawn'     => 'bg-gray-100 text-gray-500 border border-gray-200',
                        default         => 'bg-gray-100 text-gray-800',
                    } }}">
                        <i class="fa-solid fa-circle text-[6px] mr-1.5 mt-0.5"></i>{{ str_replace('_', ' ', ucfirst($application->status)) }}
                    </span>
                </div>
            </div>

            <!-- Visual Progress Tracker -->
            @if(!$isTerminal)
            <div class="px-5 pt-5 pb-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Application Progress</p>
                <div class="relative">
                    <!-- Track Line -->
                    <div class="absolute top-5 left-5 right-5 h-0.5 bg-gray-200 z-0"></div>
                    <div class="absolute top-5 left-5 h-0.5 bg-indigo-500 z-0 transition-all"
                         style="width: calc({{ $currentIdx > 0 ? ($currentIdx / max(count($stages)-1,1)) * 100 : 0 }}% - {{ $currentIdx > 0 ? '20px' : '0px' }})">
                    </div>

                    <!-- Stage Nodes -->
                    <div class="relative z-10 flex justify-between">
                        @foreach($stages as $i => $stage)
                        @php
                            $isDone    = $i < $currentIdx;
                            $isCurrent = $i === $currentIdx;
                            $isPending = $i > $currentIdx;
                        @endphp
                        <div class="flex flex-col items-center gap-1" style="width: {{ 100/count($stages) }}%">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold transition-all
                                {{ $isDone    ? 'bg-indigo-600 text-white shadow-md' : '' }}
                                {{ $isCurrent ? 'bg-indigo-600 text-white ring-4 ring-indigo-100 shadow-lg scale-110' : '' }}
                                {{ $isPending ? 'bg-white text-gray-400 border-2 border-gray-200' : '' }}">
                                @if($isDone)
                                    <i class="fa-solid fa-check text-xs"></i>
                                @else
                                    <i class="fa-solid {{ $stage['icon'] }} text-xs"></i>
                                @endif
                            </div>
                            <span class="text-[10px] font-semibold text-center leading-tight
                                {{ $isCurrent ? 'text-indigo-700' : ($isDone ? 'text-indigo-500' : 'text-gray-400') }}"
                                  style="max-width:52px">{{ $stage['label'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @elseif($currentStatus === 'rejected')
            <div class="mx-5 mt-4 mb-1 bg-red-50 border border-red-200 rounded-lg p-3 flex items-center gap-3">
                <i class="fa-solid fa-circle-xmark text-red-500 text-xl"></i>
                <div>
                    <p class="text-sm font-semibold text-red-900">Application Not Progressed</p>
                    @if($application->rejection_reason)
                    <p class="text-xs text-red-700 mt-0.5">{{ $application->rejection_reason }}</p>
                    @endif
                </div>
            </div>
            @else
            <div class="mx-5 mt-4 mb-1 bg-gray-50 border border-gray-200 rounded-lg p-3 flex items-center gap-3">
                <i class="fa-solid fa-circle-xmark text-gray-400 text-xl"></i>
                <p class="text-sm font-medium text-gray-600">You withdrew this application.</p>
            </div>
            @endif

            <!-- AI Match & Custom CV Row -->
            <div class="px-5 pb-4 grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                @if($application->aiRecommendation)
                <div class="bg-indigo-50/70 border border-indigo-100 rounded-md p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-robot text-indigo-600"></i>
                        <span class="text-xs font-medium text-indigo-900">AI Match Score:</span>
                    </div>
                    <span class="font-bold text-sm text-indigo-700">{{ $application->aiRecommendation->match_score }}%</span>
                </div>
                @endif

                @if($application->has_resume)
                <div class="bg-gray-50 border border-gray-200 rounded-md p-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-file-pdf text-red-500"></i>
                        <span class="text-xs font-medium text-gray-700">{{ $application->resume_type_label }}</span>
                    </div>
                    <a href="{{ route('applicant.applications.resume.preview', $application) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                        <i class="fa-solid fa-eye text-[11px]"></i> Preview <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                    </a>
                </div>
                @endif
            </div>

            <!-- Cover Letter Preview -->
            @if($application->cover_letter)
            <div class="px-5 pb-4">
                <div class="bg-gray-50 p-3 rounded text-xs text-gray-700 border border-gray-100">
                    <span class="font-semibold text-gray-900">Cover Letter: </span>{{ Str::limit($application->cover_letter, 180) }}
                </div>
            </div>
            @endif

            <!-- Interviews -->
            @if($application->interviews->isNotEmpty())
            <div class="px-5 pb-4 border-t border-gray-100 pt-3">
                <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Scheduled Interviews</h4>
                <ul class="space-y-2">
                    @foreach($application->interviews as $interview)
                    <li class="flex items-center justify-between text-xs bg-purple-50 p-2.5 rounded border border-purple-100">
                        <span class="font-medium text-purple-900">
                            <i class="fa-solid fa-video text-purple-600 mr-1.5"></i>{{ ucfirst($interview->type) }} Interview &mdash;
                            {{ \Carbon\Carbon::parse($interview->scheduled_at)->format('M d, Y g:i A') }}
                        </span>
                        <span class="font-bold text-purple-700 uppercase tracking-wide text-[10px]">{{ ucfirst($interview->status) }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Offer Letter -->
            @if($application->offerLetter)
            <div class="px-5 pb-4 border-t border-gray-100 pt-3">
                <div style="background-color:#ecfdf5; border:1px solid #a7f3d0; border-radius:8px; padding:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div>
                        <p style="font-size:14px; font-weight:700; color:#065f46; margin:0;">
                            <i class="fa-solid fa-file-contract mr-1.5" style="color:#059669;"></i>Official Offer Letter &mdash; {{ $application->offerLetter->offer_number }}
                        </p>
                        <p style="font-size:12px; color:#047857; margin-top:4px; margin-bottom:0;">
                            Status: <span style="font-weight:800; text-transform:uppercase; color:#065f46;">{{ $application->offerLetter->status }}</span>
                            @if($application->offerLetter->salary)
                            &bull; Salary: <strong>₱{{ number_format($application->offerLetter->salary, 2) }}</strong>
                            @endif
                            @if($application->offerLetter->start_date)
                            &bull; Start Date: <strong>{{ \Carbon\Carbon::parse($application->offerLetter->start_date)->format('M d, Y') }}</strong>
                            @endif
                        </p>
                    </div>

                    <div style="display:flex; items-center; gap:8px; flex-wrap:wrap;">
                        <!-- Open Offer Letter Modal Button -->
                        <button type="button" onclick="document.getElementById('offerModal-{{ $application->offerLetter->id }}').classList.remove('hidden')"
                                style="background-color:#2563eb; color:#ffffff; padding:7px 16px; font-size:12px; font-weight:700; border-radius:6px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 2px rgba(0,0,0,0.1);"
                                onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#2563eb'">
                            <i class="fa-solid fa-envelope-open-text"></i> Open Offer Letter
                        </button>

                        @if($application->offerLetter->status === 'sent')
                        <!-- Direct Accept Button -->
                        <form method="POST" action="{{ route('applicant.offers.accept', $application->offerLetter) }}" onsubmit="return confirm('Are you sure you want to ACCEPT this official job offer?')">
                            @csrf
                            <button type="submit" style="background-color:#059669; color:#ffffff; padding:7px 16px; font-size:12px; font-weight:700; border-radius:6px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 2px rgba(0,0,0,0.1);"
                                    onmouseover="this.style.backgroundColor='#047857'" onmouseout="this.style.backgroundColor='#059669'">
                                <i class="fa-solid fa-circle-check"></i> Accept
                            </button>
                        </form>

                        <!-- Direct Decline Button -->
                        <button type="button" onclick="document.getElementById('declineNotes-{{ $application->offerLetter->id }}').classList.toggle('hidden')"
                                style="background-color:#dc2626; color:#ffffff; padding:7px 16px; font-size:12px; font-weight:700; border-radius:6px; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px; box-shadow:0 1px 2px rgba(0,0,0,0.1);"
                                onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                            <i class="fa-solid fa-circle-xmark"></i> Decline
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Decline Reason Inline Drawer -->
                @if($application->offerLetter->status === 'sent')
                <div id="declineNotes-{{ $application->offerLetter->id }}" class="hidden mt-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <form method="POST" action="{{ route('applicant.offers.reject', $application->offerLetter) }}" onsubmit="return confirm('Are you sure you want to DECLINE this offer?')">
                        @csrf
                        <label class="block text-xs font-bold text-red-900 mb-1">Reason for declining (Optional):</label>
                        <textarea name="response_notes" rows="2" placeholder="Provide a brief explanation..." class="w-full rounded-md border-red-300 text-xs p-2 focus:ring-red-500 focus:border-red-500 mb-2"></textarea>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="document.getElementById('declineNotes-{{ $application->offerLetter->id }}').classList.add('hidden')" class="px-3 py-1 text-xs font-semibold text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">Cancel</button>
                            <button type="submit" style="background-color:#dc2626; color:#ffffff; padding:5px 14px; font-size:12px; font-weight:700; border-radius:4px; border:none; cursor:pointer;">Confirm Decline</button>
                        </div>
                    </form>
                </div>
                @endif
            </div>

            <!-- OFFER LETTER FULL MODAL -->
            <div id="offerModal-{{ $application->offerLetter->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full overflow-hidden border border-gray-200">
                    <!-- Modal Header -->
                    <div style="background-color:#059669; color:#ffffff; padding:16px 24px; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <i class="fa-solid fa-file-signature text-2xl"></i>
                            <div>
                                <h3 style="font-size:18px; font-weight:700; color:#ffffff; margin:0;">Official Job Offer Letter</h3>
                                <p style="font-size:12px; color:#d1fae5; margin:0;">Reference: {{ $application->offerLetter->offer_number }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('offerModal-{{ $application->offerLetter->id }}').classList.add('hidden')" style="background:none; border:none; color:#ffffff; font-size:20px; cursor:pointer;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-5 text-left max-h-[75vh] overflow-y-auto" style="text-align:left;">
                        <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-lg">
                            <p class="text-xs font-semibold text-emerald-800 uppercase tracking-wider">Congratulations!</p>
                            <p class="text-sm text-emerald-900 font-medium mt-0.5">We are pleased to offer you the position of <strong>{{ $application->jobPosting->title ?? 'N/A' }}</strong> at RecruitSmart.</p>
                        </div>

                        <!-- Key Terms Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                <span class="text-[11px] font-semibold text-gray-500 uppercase block">Offered Salary</span>
                                <span class="text-base font-bold text-gray-900">₱{{ number_format($application->offerLetter->salary, 2) }}</span>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg">
                                <span class="text-[11px] font-semibold text-gray-500 uppercase block">Employment Type</span>
                                <span class="text-sm font-bold text-gray-900">{{ ucfirst($application->offerLetter->employment_type ?? 'Full-Time') }}</span>
                            </div>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg col-span-2 sm:col-span-1">
                                <span class="text-[11px] font-semibold text-gray-500 uppercase block">Proposed Start Date</span>
                                <span class="text-sm font-bold text-gray-900">{{ $application->offerLetter->start_date ? \Carbon\Carbon::parse($application->offerLetter->start_date)->format('M d, Y') : 'To be agreed' }}</span>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        @if($application->offerLetter->terms)
                        <div>
                            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Terms & Conditions</h4>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg text-xs text-gray-700 whitespace-pre-line leading-relaxed">
                                {{ $application->offerLetter->terms }}
                            </div>
                        </div>
                        @endif

                        <!-- Benefits -->
                        @if($application->offerLetter->benefits)
                        <div>
                            <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Benefits Package</h4>
                            <div class="bg-gray-50 border border-gray-200 p-3 rounded-lg text-xs text-gray-700 whitespace-pre-line leading-relaxed">
                                {{ $application->offerLetter->benefits }}
                            </div>
                        </div>
                        @endif

                        @if($application->offerLetter->response_notes)
                        <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg text-xs">
                            <span class="font-bold text-amber-900">Applicant Notes: </span>
                            <span class="text-amber-800">{{ $application->offerLetter->response_notes }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Modal Footer / Action Bar -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                        <button type="button" onclick="document.getElementById('offerModal-{{ $application->offerLetter->id }}').classList.add('hidden')" class="px-4 py-2 text-xs font-semibold text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-100">
                            Close Window
                        </button>

                        @if($application->offerLetter->status === 'sent')
                        <div class="flex gap-2">
                            <button type="button" onclick="document.getElementById('offerModal-{{ $application->offerLetter->id }}').classList.add('hidden'); document.getElementById('declineNotes-{{ $application->offerLetter->id }}').classList.remove('hidden');" style="background-color:#dc2626; color:#ffffff; padding:8px 18px; font-size:12px; font-weight:700; border-radius:6px; border:none; cursor:pointer;">
                                <i class="fa-solid fa-xmark mr-1"></i> Decline Offer
                            </button>
                            <form method="POST" action="{{ route('applicant.offers.accept', $application->offerLetter) }}" onsubmit="return confirm('Are you sure you want to ACCEPT this official job offer?')">
                                @csrf
                                <button type="submit" style="background-color:#059669; color:#ffffff; padding:8px 18px; font-size:12px; font-weight:700; border-radius:6px; border:none; cursor:pointer;">
                                    <i class="fa-solid fa-check mr-1"></i> Accept Job Offer
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-xs font-bold uppercase px-3 py-1.5 rounded-full {{ match($application->offerLetter->status) {
                            'accepted' => 'bg-emerald-100 text-emerald-800 border border-emerald-300',
                            'rejected' => 'bg-red-100 text-red-800 border border-red-300',
                            default => 'bg-gray-100 text-gray-800'
                        } }}">
                            Offer Status: {{ $application->offerLetter->status }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Withdraw Action -->
            @if(in_array($application->status, ['submitted', 'under_review', 'screening']))
            <div class="px-5 pb-4 pt-2 border-t border-gray-100 flex justify-end">
                <form method="POST" action="{{ route('applicant.applications.withdraw', $application) }}" onsubmit="return confirm('Are you sure you want to withdraw this application?')">
                    @csrf
                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">
                        <i class="fa-solid fa-ban mr-1"></i>Withdraw Application
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <i class="fa-solid fa-folder-open text-gray-300 text-4xl mb-3"></i>
            <p class="text-sm font-medium text-gray-700">You haven't applied to any job postings yet.</p>
            <p class="text-xs text-gray-500 mt-1">Explore open opportunities and submit your first application.</p>
            <a href="{{ route('applicant.jobs') }}" class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500">
                <i class="fa-solid fa-briefcase mr-1.5"></i>Browse Jobs
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection
