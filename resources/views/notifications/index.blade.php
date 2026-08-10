@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notification Center</h1>
            <p class="mt-1 text-sm text-gray-500">Stay updated on recruitment activities.</p>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Mark All Read</button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @forelse($notifications as $notification)
        <div class="p-4 border-b border-gray-100 flex items-start justify-between {{ $notification->is_read ? 'bg-white' : 'bg-indigo-50' }}">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                    <p class="text-sm text-gray-600 mt-0.5">{{ $notification->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</p>
                </div>
            </div>
            @unless($notification->is_read)
            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">Mark Read</button>
            </form>
            @endunless
        </div>
        @empty
        <div class="p-8 text-center text-sm text-gray-500">No notifications yet.</div>
        @endforelse
    </div>

    <div>{{ $notifications->links() }}</div>
</div>
@endsection
</content>
