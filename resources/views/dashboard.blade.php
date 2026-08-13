@extends('layouts.app')

@section('title', 'AI-Assisted Recruitment Dashboard')

@section('page-title', 'AI-Assisted Recruitment Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Page header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">AI-Assisted Recruitment Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">AI-assisted analysis of current recruitment and onboarding data.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('recruitment.ai.generate-all') }}" onsubmit="return confirm('Generate AI recruitment insights from the current pipeline data?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-4 py-2 text-sm font-medium text-white hover:from-indigo-700 hover:to-violet-700 shadow-sm">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate AI Insights
                </button>
            </form>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                <i class="fa-solid fa-file-export"></i> Reports
            </a>
        </div>
    </div>

    <!-- Vue Reactive Pipeline Kanban Widget -->
    <div id="vue-app">
        <pipeline-kanban-widget
            :applied-count="{{ $totalApplicants }}"
            :screening-count="{{ $applicationsByStatus['screening'] ?? 0 }}"
            :interview-count="{{ $candidatesInterviewed }}"
            :offer-count="{{ $applicationsByStatus['offered'] ?? 0 }}"
            :hired-count="{{ $applicantsHired }}"
        ></pipeline-kanban-widget>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Total Applicants</p>
                <span class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-users text-xs"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalApplicants }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Active Vacancies</p>
                <span class="w-7 h-7 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i class="fa-solid fa-briefcase text-xs"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeVacancies }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Candidates Interviewed</p>
                <span class="w-7 h-7 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-comments text-xs"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $candidatesInterviewed }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Applicants Hired</p>
                <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-user-check text-xs"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $applicantsHired }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Offer Acceptance Rate</p>
                <span class="w-7 h-7 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fa-solid fa-percent text-xs"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $offerAcceptanceRate }}%</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Time-to-Hire</p>
                <span class="w-7 h-7 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-clock text-xs"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $timeToHire > 0 ? $timeToHire . ' days' : 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Cost-per-Hire</p>
                <span class="w-7 h-7 rounded-full bg-red-100 text-red-600 flex items-center justify-center"><i class="fa-solid fa-coins text-xs"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $costPerHire > 0 ? '₱' . number_format($costPerHire) : 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Recruitment Success</p>
                <span class="w-7 h-7 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center"><i class="fa-solid fa-trophy text-xs"></i></span>
            </div>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $recruitmentSuccessRate }}%</p>
        </div>
    </div>

    <!-- ===== AI RECRUITMENT INSIGHTS (Decision Support Dashboard) ===== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/20 text-white">
                    <i class="fa-solid fa-robot text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">AI Recruitment Insights</h2>
                    <p class="text-xs text-indigo-100">AI-assisted analysis of current recruitment and onboarding data</p>
                </div>
            </div>
        </div>

        @if($pipelineInsights->isEmpty())
        <div class="p-10 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 mx-auto mb-3">
                <i class="fa-solid fa-wand-magic-sparkles text-2xl text-indigo-400"></i>
            </div>
            <p class="text-sm font-medium text-gray-700">No AI insights generated yet</p>
            <p class="text-sm text-gray-500 mt-1">Click <span class="font-semibold">"Generate AI Insights"</span> to analyze the recruitment pipeline and produce evidence-based, actionable recommendations for HR.</p>
        </div>
        @else
        <!-- Overall Recruitment Health -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fa-solid fa-heart-pulse text-rose-500"></i>
            <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Overall Recruitment Health</h3>
        </div>
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 px-6 py-4 border-b border-gray-100">
            <div class="bg-emerald-50 rounded-lg p-3">
                <p class="text-xs text-emerald-700 font-medium">Hiring Rate</p>
                <p class="text-xl font-bold text-emerald-800 mt-1">{{ $recruitmentSuccessRate }}%</p>
            </div>
            <div class="bg-blue-50 rounded-lg p-3">
                <p class="text-xs text-blue-700 font-medium">Offer Acceptance</p>
                <p class="text-xl font-bold text-blue-800 mt-1">{{ $offerAcceptanceRate }}%</p>
            </div>
            <div class="bg-amber-50 rounded-lg p-3">
                <p class="text-xs text-amber-700 font-medium">Time-to-Hire</p>
                <p class="text-xl font-bold text-amber-800 mt-1">{{ $timeToHire > 0 ? $timeToHire . ' days' : 'N/A' }}</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-3">
                <p class="text-xs text-purple-700 font-medium">Avg Match Score</p>
                <p class="text-xl font-bold text-purple-800 mt-1">{{ $avgMatchScore }}%</p>
            </div>
        </div>

        <!-- Insight Cards -->
        <div class="p-6 space-y-4">
            @foreach($pipelineInsights as $insight)
            @php
                $priorityColor = $insight->priority === 'HIGH' ? 'bg-red-100 text-red-700' : ($insight->priority === 'MEDIUM' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600');
                $priorityBorder = $insight->priority === 'HIGH' ? 'border-l-4 border-l-red-500' : ($insight->priority === 'MEDIUM' ? 'border-l-4 border-l-amber-400' : 'border-l-4 border-l-gray-300');
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden {{ $priorityBorder }}">
                <div class="p-5">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-2xl">
                            {{ $insight->icon ?? '📊' }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-bold text-gray-900">{{ $insight->title }}</h4>
                                @if($insight->priority)
                                <span class="inline-flex items-center text-xs font-bold px-2 py-1 rounded-full {{ $priorityColor }}">{{ $insight->priority }} PRIORITY</span>
                                @endif
                            </div>
                            @if($insight->category)
                            <span class="inline-flex mt-1 text-xs font-medium px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">{{ $insight->category }}</span>
                            @endif

                            <p class="text-sm text-gray-700 leading-relaxed mt-3">{{ $insight->summary ?? $insight->content }}</p>

                            @if(!empty($insight->evidence))
                            <div class="mt-3 bg-gray-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Supporting Evidence</p>
                                <ul class="space-y-1">
                                    @foreach($insight->evidence as $evidence)
                                    <li class="text-sm text-gray-600 flex items-start"><span class="mr-2 text-indigo-500">•</span>{{ $evidence }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if($insight->impact)
                            <div class="mt-3">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1"><i class="fa-solid fa-bullseye text-rose-500 mr-1"></i>Impact</p>
                                <p class="text-sm text-gray-700">{{ $insight->impact }}</p>
                            </div>
                            @endif

                            @if($insight->recommendation)
                            <div class="mt-3 bg-emerald-50 rounded-lg p-3">
                                <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide mb-1"><i class="fa-solid fa-lightbulb mr-1"></i>Recommended Action</p>
                                <p class="text-sm text-gray-700">{{ $insight->recommendation }}</p>
                            </div>
                            @endif

                            @if($insight->explanation)
                            <details class="mt-3 group">
                                <summary class="flex items-center gap-1 text-xs font-semibold text-indigo-600 cursor-pointer hover:text-indigo-500">
                                    <i class="fa-solid fa-circle-question"></i> Why this insight?
                                    <i class="fa-solid fa-chevron-down text-[10px] group-open:rotate-180 transition-transform"></i>
                                </summary>
                                <div class="mt-2 bg-indigo-50 border-l-4 border-indigo-400 rounded-r-lg p-3">
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ $insight->explanation }}</p>
                                </div>
                            </details>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Average Scores -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Average Interview Score</h3>
            <div class="flex items-center">
                <div class="flex-1 bg-gray-100 rounded-full h-3 mr-3">
                    <div class="bg-indigo-600 h-3 rounded-full" style="width: {{ $avgInterviewScore }}%"></div>
                </div>
                <span class="text-xl font-bold text-gray-900">{{ $avgInterviewScore }}%</span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Average AI Match Score</h3>
            <div class="flex items-center">
                <div class="flex-1 bg-gray-100 rounded-full h-3 mr-3">
                    <div class="bg-purple-600 h-3 rounded-full" style="width: {{ $avgMatchScore }}%"></div>
                </div>
                <span class="text-xl font-bold text-gray-900">{{ $avgMatchScore }}%</span>
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <!-- Recruitment Funnel -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Recruitment Funnel</h3>
            <div class="space-y-2">
                @foreach($funnel as $stage => $count)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $stage) }}</span>
                        <span class="font-medium text-gray-900">{{ $count }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-1.5">
                        <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $count > 0 ? ($count / max(array_sum($funnel), 1)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Applicants per Position -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Applicants per Position</h3>
            <div class="space-y-2">
                @forelse($applicantsPerPosition as $item)
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-600 truncate">{{ $item['label'] }}</span>
                        <span class="font-medium text-gray-900">{{ $item['count'] }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-1.5">
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $item['count'] > 0 ? ($item['count'] / max($applicantsPerPosition->max('count'), 1)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500">No postings yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Top Skills -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Top Skills</h3>
            <div class="flex flex-wrap gap-2">
                @forelse($skillsDistribution as $skill => $count)
                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-800">
                    {{ $skill }} <span class="ml-1.5 bg-indigo-200 text-indigo-800 px-1.5 rounded-full">{{ $count }}</span>
                </span>
                @empty
                <span class="text-sm text-gray-500">No skills data.</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Skills Gap & Missing Skills -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <!-- Skills Gap Analysis -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3"><i class="fa-solid fa-screwdriver-wrench text-amber-500 mr-1"></i>Skills Gap Analysis</h3>
            <p class="text-xs text-gray-500 mb-3">Required skills across postings with no applicant supply.</p>
            @forelse($skillsGap as $skill => $count)
            <div class="mb-2">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-700 capitalize">{{ ucfirst($skill) }}</span>
                    <span class="font-medium text-amber-600">{{ $count }} posting{{ $count > 1 ? 's' : '' }}</span>
                </div>
                <div class="bg-gray-100 rounded-full h-1.5">
                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $count > 0 ? min(100, $count * 20) : 0 }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500">No skills gaps detected. All required skills have applicant supply.</p>
            @endforelse
        </div>

        <!-- Missing Skills Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3"><i class="fa-solid fa-triangle-exclamation text-red-500 mr-1"></i>Missing Skills Summary</h3>
            <p class="text-xs text-gray-500 mb-3">Most common missing skills across AI-evaluated candidates.</p>
            <div class="flex flex-wrap gap-2">
                @forelse($missingSkillsSummary as $skill => $count)
                <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full bg-red-100 text-red-800">
                    {{ ucfirst($skill) }} <span class="ml-1.5 bg-red-200 text-red-800 px-1.5 rounded-full">{{ $count }}</span>
                </span>
                @empty
                <span class="text-sm text-gray-500">No missing skills data. Generate AI insights to populate.</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Interview Performance Analysis -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="text-sm font-semibold text-gray-900 mb-4"><i class="fa-solid fa-chart-column text-indigo-500 mr-1"></i>Interview Performance Analysis</h3>
        @if($interviewPerformance['count'] > 0)
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            @foreach([
                'communication' => ['Communication', 'bg-blue-500'],
                'technical' => ['Technical', 'bg-indigo-500'],
                'experience' => ['Experience', 'bg-purple-500'],
                'cultural_fit' => ['Cultural Fit', 'bg-emerald-500'],
                'overall' => ['Overall', 'bg-amber-500'],
            ] as $key => [$label, $color])
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600">{{ $label }}</span>
                    <span class="font-bold text-gray-900">{{ $interviewPerformance[$key] }}%</span>
                </div>
                <div class="bg-gray-100 rounded-full h-2">
                    <div class="{{ $color }} h-2 rounded-full" style="width: {{ $interviewPerformance[$key] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-500 mt-3">Based on {{ $interviewPerformance['count'] }} completed interview assessment{{ $interviewPerformance['count'] > 1 ? 's' : '' }}.</p>
        @else
        <p class="text-sm text-gray-500">No completed interview assessments yet.</p>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Recent Applications</h3>
            <div class="space-y-2">
                @forelse($recentApplications as $app)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $app->applicant->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $app->jobPosting->title }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ $app->applied_at?->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No recent applications.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Upcoming Interviews</h3>
            <div class="space-y-2">
                @forelse($upcomingInterviews as $interview)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $interview->application->applicant->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $interview->application->jobPosting->title }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ $interview->scheduled_at?->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No upcoming interviews.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Recent Offers</h3>
            <div class="space-y-2">
                @forelse($recentOffers as $offer)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $offer->application->applicant->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $offer->jobPosting->title ?? 'Position' }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ $offer->created_at?->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No offers yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
