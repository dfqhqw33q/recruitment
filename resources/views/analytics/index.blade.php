@extends('layouts.app')

@section('title', 'AI Recruitment Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">AI Recruitment Analytics Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Interactive recruitment analytics and KPIs.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><i class="fa-solid fa-file-export mr-1"></i>Generate Reports</a>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Total Applicants</p>
                <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-users text-sm"></i></span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $kpis['totalApplicants'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Active Vacancies</p>
                <span class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i class="fa-solid fa-briefcase text-sm"></i></span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $kpis['activeVacancies'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Candidates Interviewed</p>
                <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fa-solid fa-comments text-sm"></i></span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $kpis['candidatesInterviewed'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Applicants Hired</p>
                <span class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center"><i class="fa-solid fa-user-check text-sm"></i></span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $kpis['applicantsHired'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Offer Acceptance Rate</p>
                <span class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fa-solid fa-percent text-sm"></i></span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $kpis['offerAcceptanceRate'] }}%</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Time-to-Hire</p>
                <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center"><i class="fa-solid fa-clock text-sm"></i></span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $kpis['timeToHire'] }} <span class="text-sm font-medium text-gray-500">days</span></p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Cost-per-Hire</p>
                <span class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center"><i class="fa-solid fa-coins text-sm"></i></span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">₱{{ number_format($kpis['costPerHire']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Recruitment Success</p>
                <span class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center"><i class="fa-solid fa-trophy text-sm"></i></span>
            </div>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $kpis['recruitmentSuccessRate'] }}%</p>
        </div>
    </div>

    <!-- Average Scores -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Average Interview Score</h3>
            <div class="flex items-center">
                <div class="flex-1 bg-gray-100 rounded-full h-4 mr-3">
                    <div class="bg-indigo-600 h-4 rounded-full" style="width: {{ $avgInterviewScore }}%"></div>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $avgInterviewScore }}%</span>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Average AI Match Score</h3>
            <div class="flex items-center">
                <div class="flex-1 bg-gray-100 rounded-full h-4 mr-3">
                    <div class="bg-purple-600 h-4 rounded-full" style="width: {{ $avgMatchScore }}%"></div>
                </div>
                <span class="text-2xl font-bold text-gray-900">{{ $avgMatchScore }}%</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Recruitment Funnel -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recruitment Funnel</h3>
            <div class="space-y-3">
                @foreach($funnel as $stage => $count)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $stage) }}</span>
                        <span class="font-medium text-gray-900">{{ $count }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $count > 0 ? ($count / max(array_sum($funnel), 1)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

<!-- AI Recommendation Distribution -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">AI Recommendation Distribution</h3>
            <div class="space-y-3">
                @php $aiTotal = max($aiDistribution->sum(), 1); @endphp
                @foreach(['highly_recommended', 'recommended', 'not_recommended'] as $rec)
                @php $count = $aiDistribution[$rec] ?? 0; @endphp
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 capitalize">{{ str_replace('_', ' ', $rec) }}</span>
                        <span class="font-medium text-gray-900">{{ $count }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2">
                        <div class="{{ $rec == 'highly_recommended' ? 'bg-green-500' : ($rec == 'recommended' ? 'bg-blue-500' : 'bg-red-500') }} h-2 rounded-full" style="width: {{ $count > 0 ? ($count / $aiTotal) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Applicants per Position -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Applicants per Position</h3>
            <div class="space-y-3">
                @foreach($applicantsPerPosition as $item)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 truncate">{{ $item['label'] }}</span>
                        <span class="font-medium text-gray-900">{{ $item['count'] }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2">
                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $item['count'] > 0 ? ($item['count'] / max($applicantsPerPosition->max('count'), 1)) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Skills Distribution -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Skills</h3>
            <div class="flex flex-wrap gap-2">
                @forelse($skillsDistribution as $skill => $count)
                <span class="inline-flex items-center text-xs font-medium px-3 py-1.5 rounded-full bg-indigo-100 text-indigo-800">
                    {{ $skill }} <span class="ml-2 bg-indigo-200 text-indigo-800 px-1.5 rounded-full">{{ $count }}</span>
                </span>
                @empty
                <span class="text-sm text-gray-500">No skills data.</span>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Applications</h3>
            <div class="space-y-3">
                @forelse($recentApplications as $app)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $app->applicant->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $app->jobPosting->title }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($app->applied_at)->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No recent applications.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Interviews</h3>
            <div class="space-y-3">
                @forelse($upcomingInterviews as $interview)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $interview->application->applicant->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($interview->scheduled_at)->format('M d, h:i A') }}</p>
                    </div>
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($interview->scheduled_at)->diffForHumans() }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No upcoming interviews.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
