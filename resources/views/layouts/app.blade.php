<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Recruitment System') — Smart Recruitment & Onboarding</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="h-full bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        {{-- Mobile sidebar backdrop --}}
        <div id="mobileBackdrop" class="fixed inset-0 z-30 hidden bg-gray-900/50 lg:hidden" onclick="toggleSidebar(false)"></div>

        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static">
            {{-- Brand --}}
            <div class="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 px-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <i class="fa-solid fa-users-viewfinder text-xs"></i>
                    </span>
                    <span class="text-base font-bold tracking-tight text-gray-900">Recruit<span class="text-indigo-600">Smart</span></span>
                </a>
                <button type="button" onclick="toggleSidebar(false)" class="rounded p-1 text-gray-400 hover:bg-gray-100 lg:hidden">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Sidebar nav --}}
            <nav class="flex-1 overflow-hidden px-3 py-3">
                @php
                    $user = auth()->user();
                    $role = $user->roles->pluck('name')->first();
                    $isAdmin = in_array($role, ['Super Admin', 'HR Administrator']);
                @endphp

                {{-- Overview --}}
                <div class="mb-2">
                    <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Overview</p>
                    <div class="space-y-0.5">
<a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-gauge-high w-4 text-center {{ request()->routeIs('dashboard') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            AI-Analytic Dashboard
                        </a>

                    </div>
                </div>

                {{-- Recruitment --}}
                @can('view_postings')
                <div class="mb-2">
                    <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Recruitment</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('recruitment.job-postings.index') }}" class="{{ request()->routeIs('recruitment.job-postings.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-briefcase w-4 text-center {{ request()->routeIs('recruitment.job-postings.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Job Postings
                        </a>
                        @can('view_applications')
                        <a href="{{ route('recruitment.applications.index') }}" class="{{ request()->routeIs('recruitment.applications.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-user-tie w-4 text-center {{ request()->routeIs('recruitment.applications.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Applicants
                        </a>
                        @endcan
@can('view_interviews')
                        <a href="{{ route('recruitment.interviews.index') }}" class="{{ request()->routeIs('recruitment.interviews.*', 'recruitment.calendar') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-handshake w-4 text-center {{ request()->routeIs('recruitment.interviews.*', 'recruitment.calendar') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Interviews
                        </a>
                        @endcan
                        @can('view_offers')
                        <a href="{{ route('recruitment.offers.index') }}" class="{{ request()->routeIs('recruitment.offers.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-file-signature w-4 text-center {{ request()->routeIs('recruitment.offers.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Job Offers
                        </a>
                        @endcan
                    </div>
                </div>
                @endcan

                {{-- Onboarding --}}
                @can('view_onboarding')
                <div class="mb-2">
                    <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Onboarding</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('recruitment.onboarding.index') }}" class="{{ request()->routeIs('recruitment.onboarding.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-rocket w-4 text-center {{ request()->routeIs('recruitment.onboarding.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Onboarding
                        </a>
                        <a href="{{ route('recruitment.documents.index') }}" class="{{ request()->routeIs('recruitment.documents.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-folder-open w-4 text-center {{ request()->routeIs('recruitment.documents.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Documents
                        </a>
                    </div>
                </div>
                @endcan

                {{-- Reports --}}
                @can('generate_reports')
                <div class="mb-2">
                    <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Reporting</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-file-export w-4 text-center {{ request()->routeIs('reports.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Reports
                        </a>
                    </div>
                </div>
                @endcan

                {{-- Administration --}}
                @if($isAdmin)
                <div class="mb-2">
                    <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">Administration</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-users-cog w-4 text-center {{ request()->routeIs('admin.users.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Users
                        </a>
                        <a href="{{ route('admin.departments.index') }}" class="{{ request()->routeIs('admin.departments.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-building w-4 text-center {{ request()->routeIs('admin.departments.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Departments
                        </a>
                        <a href="{{ route('admin.job-positions.index') }}" class="{{ request()->routeIs('admin.job-positions.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-clipboard-list w-4 text-center {{ request()->routeIs('admin.job-positions.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Job Positions
                        </a>
                        <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs.*') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-clock-rotate-left w-4 text-center {{ request()->routeIs('admin.activity-logs.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Activity Logs
                        </a>
                    </div>
                </div>
                @endif

                {{-- Notifications --}}
                <div class="mb-2">
                    <p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400">General</p>
                    <div class="space-y-0.5">
                        <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.index') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }} group flex items-center gap-3 rounded-md px-3 py-1.5 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-bell w-4 text-center {{ request()->routeIs('notifications.index') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            Notifications
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Sidebar footer / user --}}
            <div class="shrink-0 border-t border-gray-200 p-3">
                <div class="flex items-center gap-2 rounded-md bg-gray-50 px-2 py-1.5">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="truncate text-[11px] text-gray-500">{{ $role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600" title="Logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col">
            {{-- Top bar --}}
            <header class="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSidebar(true)" class="rounded p-2 text-gray-500 hover:bg-gray-100 lg:hidden">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
<h1 class="text-lg font-semibold text-gray-900">@yield('page-title', 'AI-Analytic Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('notifications.index') }}" class="relative rounded-full p-2 text-gray-500 hover:bg-gray-100">
                        <i class="fa-solid fa-bell text-lg"></i>
                        @php
                            $unreadCount = \App\Models\NotificationRecord::where('user_id', auth()->id())->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </a>
                    <div class="hidden items-center gap-3 sm:flex">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 leading-tight">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500 leading-tight">{{ $role }}</p>
                        </div>
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600 text-sm font-semibold text-white">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            <div class="shrink-0 px-4 pt-4 sm:px-6">
                @if(session('success'))
                <div class="mb-4 flex items-start gap-3 rounded-lg border-l-4 border-green-400 bg-green-50 p-4">
                    <i class="fa-solid fa-circle-check text-green-400 mt-0.5"></i>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 flex items-start gap-3 rounded-lg border-l-4 border-red-400 bg-red-50 p-4">
                    <i class="fa-solid fa-circle-xmark text-red-400 mt-0.5"></i>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                @endif

                @if(session('info'))
                <div class="mb-4 flex items-start gap-3 rounded-lg border-l-4 border-blue-400 bg-blue-50 p-4">
                    <i class="fa-solid fa-circle-info text-blue-400 mt-0.5"></i>
                    <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
                </div>
                @endif
            </div>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar(open) {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('mobileBackdrop');
            if (open) {
                sidebar.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
