@extends('layouts.app')

@section('title', 'Recruitment Calendar')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Recruitment Calendar</h1>
            <p class="mt-1 text-sm text-gray-500">View all scheduled interviews.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
            <div class="bg-gray-50 px-3 py-2 text-center text-xs font-semibold text-gray-500 uppercase">{{ $day }}</div>
            @endforeach

            @php
                $now = now();
                $start = $now->copy()->startOfMonth()->startOfWeek();
                $daysInCalendar = 42;
                $scheduled = $interviews->filter(fn($i) => $i->status === 'scheduled');
            @endphp

            @for($i = 0; $i < $daysInCalendar; $i++)
                @php
                    $date = $start->copy()->addDays($i);
                    $dayInterviews = $scheduled->filter(fn($iv) => \Carbon\Carbon::parse($iv->scheduled_at)->isSameDay($date));
                    $isCurrentMonth = $date->month === $now->month;
                    $isToday = $date->isToday();
                @endphp
                <div class="bg-white min-h-[100px] p-2 {{ !$isCurrentMonth ? 'bg-gray-50 text-gray-400' : '' }} {{ $isToday ? 'ring-2 ring-indigo-500 ring-inset' : '' }}">
                    <div class="text-xs font-semibold {{ $isToday ? 'text-indigo-600' : 'text-gray-500' }}">{{ $date->format('j') }}</div>
                    <div class="space-y-1 mt-1">
                        @foreach($dayInterviews as $interview)
                        <a href="{{ route('recruitment.interviews.show', $interview) }}" class="block text-[11px] leading-tight px-1.5 py-1 rounded bg-indigo-100 text-indigo-800 hover:bg-indigo-200">
                            <span class="font-medium">{{ \Carbon\Carbon::parse($interview->scheduled_at)->format('h:i A') }}</span>
                            <br>{{ $interview->application->applicant->full_name }}
                        </a>
                        @endforeach
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection
