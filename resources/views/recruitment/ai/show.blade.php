@extends('layouts.app')

@section('title', 'AI Recommendation Detail')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">AI Recommendation Detail</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $recommendation->application->applicant->full_name }} — {{ $recommendation->jobPosting->title ?? 'N/A' }}</p>
        </div>
        <a href="{{ url()->previous() }}" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Back</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Main AI Panel -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Match Score -->
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-lg shadow-lg p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-indigo-200">Candidate Match Score</p>
                        <p class="text-5xl font-bold mt-2">{{ $recommendation->match_score }}%</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-indigo-200">Confidence</p>
                        <p class="text-2xl font-bold">{{ $recommendation->confidence_score }}%</p>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="inline-flex text-sm font-semibold px-3 py-1 rounded-full {{ $recommendation->recommendation == 'highly_recommended' ? 'bg-white text-indigo-700' : 'bg-indigo-400 text-white' }}">
                        <i class="fa-solid fa-robot mr-2 mt-0.5"></i>{{ str_replace('_', ' ', ucfirst($recommendation->recommendation)) }}
                    </span>
                </div>
            </div>

            <!-- Explanation Panel -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3"><i class="fa-solid fa-circle-info text-indigo-600 mr-2"></i>AI Explanation</h3>
                @if($recommendation->explanation)
                <div class="bg-indigo-50 border-l-4 border-indigo-500 rounded-r-lg p-4">
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $recommendation->explanation }}</p>
                </div>
                @else
                <p class="text-sm text-gray-500">No explanation available.</p>
                @endif
            </div>

            <!-- Strengths & Weaknesses -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                @if($recommendation->strengths)
                <div class="bg-green-50 rounded-lg border border-green-200 p-6">
                    <h4 class="text-sm font-semibold text-green-800 mb-2"><i class="fa-solid fa-circle-check mr-1"></i>Strengths</h4>
                    <ul class="space-y-1">
                        @foreach($recommendation->strengths as $strength)
                        <li class="text-sm text-green-700 flex items-start"><span class="mr-2">✔</span>{{ $strength }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if($recommendation->weaknesses)
                <div class="bg-amber-50 rounded-lg border border-amber-200 p-6">
                    <h4 class="text-sm font-semibold text-amber-800 mb-2"><i class="fa-solid fa-circle-exclamation mr-1"></i>Weaknesses</h4>
                    <ul class="space-y-1">
                        @foreach($recommendation->weaknesses as $weakness)
                        <li class="text-sm text-amber-700 flex items-start"><span class="mr-2">✗</span>{{ $weakness }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>

            <!-- Score Breakdown -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i class="fa-solid fa-chart-column text-indigo-600 mr-2"></i>Score Breakdown</h3>
                <div class="space-y-4">
                    @php
                        $breakdown = $recommendation->score_breakdown ?? [];
                        $categories = [
                            'skills' => 'Skills Match',
                            'experience' => 'Experience',
                            'education' => 'Education',
                            'interview' => 'Interview',
                        ];
                    @endphp
                    @foreach($categories as $key => $label)
                    @php
                        $score = $breakdown[$key] ?? 50;
                        $barColor = $score >= 80 ? 'bg-green-500' : ($score >= 60 ? 'bg-amber-500' : 'bg-red-500');
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-bold {{ $score >= 80 ? 'text-green-600' : ($score >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ $score }}%</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-3 overflow-hidden">
                            <div class="{{ $barColor }} h-3 rounded-full transition-all duration-500" style="width: {{ $score }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Skills Distribution -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Skills Match Analysis</h3>
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600">Skills Match</span>
                        <span class="font-bold text-indigo-600">{{ $recommendation->skills_match_percentage }}%</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-3">
                        <div class="bg-indigo-600 h-3 rounded-full" style="width: {{ $recommendation->skills_match_percentage }}%"></div>
                    </div>
                </div>
                @if($recommendation->missing_skills)
                <div class="mt-4">
                    <h4 class="text-sm font-semibold text-amber-800 mb-2">Missing Skills</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($recommendation->missing_skills as $skill)
                        <span class="inline-flex text-xs font-medium px-2 py-1 rounded-full bg-amber-100 text-amber-800">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Candidate Summary</h3>
                <p class="font-medium text-gray-900 text-lg">{{ $recommendation->application->applicant->full_name }}</p>
                <p class="text-sm text-gray-500">{{ $recommendation->application->applicant->email }}</p>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Application Status</span><span class="font-medium capitalize">{{ str_replace('_', ' ', $recommendation->application->status) }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Generated</span><span class="font-medium">{{ \Carbon\Carbon::parse($recommendation->created_at)->format('M d, Y') }}</span></div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Candidate Skills</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($recommendation->application->applicant->skills as $skill)
                    <span class="inline-flex text-xs font-medium px-2 py-1 rounded-full bg-indigo-100 text-indigo-800">{{ $skill->name }}</span>
                    @empty
                    <span class="text-sm text-gray-500">No skills listed.</span>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Actions</h3>
                <a href="{{ route('recruitment.applications.show', $recommendation->application) }}" class="block w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white text-center hover:bg-indigo-500 mb-2">View Application</a>
                @can('approve_offers')
                <a href="{{ route('recruitment.offers.create') }}?application_id={{ $recommendation->application_id }}" class="block w-full rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white text-center hover:bg-green-500">Create Offer</a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
