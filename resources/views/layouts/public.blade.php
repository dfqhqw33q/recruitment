<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hiraya Travel and Tours') — Hiraya Travel and Tours</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="h-full bg-white text-gray-900 antialiased">
    {{-- Top notification bar --}}
    <div class="bg-indigo-600 text-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-1.5 text-center text-xs sm:text-sm">
            <i class="fa-solid fa-plane-departure mr-1"></i>
            Explore the world with Hiraya Travel and Tours — <a href="{{ route('public.careers') }}" class="font-semibold underline underline-offset-2">We're hiring!</a>
        </div>
    </div>

    {{-- Navbar --}}
    <nav class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('public.home') }}" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-white">
                    <i class="fa-solid fa-earth-asia text-sm"></i>
                </span>
                <span class="text-lg font-bold tracking-tight text-gray-900">
                    Hiraya<span class="text-indigo-600">Travel</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 lg:flex">
                @foreach([
                    ['route' => 'public.home', 'label' => 'Home'],
                    ['route' => 'public.about', 'label' => 'About Us'],
                    ['route' => 'public.tours', 'label' => 'Tours & Services'],
                    ['route' => 'public.destinations', 'label' => 'Destinations'],
                    ['route' => 'public.careers', 'label' => 'Careers'],
                    ['route' => 'public.contact', 'label' => 'Contact'],
                ] as $item)
                <a href="{{ route($item['route']) }}" class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}">
                    {{ $item['label'] }}
                </a>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                @auth
                    @if(auth()->user()->hasRole('Applicant') || (auth()->user()->roles->pluck('name')->first() == 'Applicant'))
                        <a href="{{ route('applicant.dashboard') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">My Portal</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Register</a>
                @endauth
                <button type="button" onclick="toggleMobileMenu()" class="rounded-md p-2 text-gray-500 hover:bg-gray-100 lg:hidden">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobileMenu" class="hidden border-t border-gray-200 bg-white px-4 pb-4 pt-2 lg:hidden">
            @foreach([
                ['route' => 'public.home', 'label' => 'Home'],
                ['route' => 'public.about', 'label' => 'About Us'],
                ['route' => 'public.tours', 'label' => 'Tours & Services'],
                ['route' => 'public.destinations', 'label' => 'Destinations'],
                ['route' => 'public.careers', 'label' => 'Careers'],
                ['route' => 'public.contact', 'label' => 'Contact'],
            ] as $item)
            <a href="{{ route($item['route']) }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs($item['route']) ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-100' }}">{{ $item['label'] }}</a>
            @endforeach
        </div>
    </nav>

    {{-- Main content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <i class="fa-solid fa-earth-asia text-xs"></i>
                        </span>
                        <span class="text-base font-bold tracking-tight text-gray-900">Hiraya<span class="text-indigo-600">Travel</span></span>
                    </div>
                    <p class="mt-3 text-sm text-gray-600">Crafting unforgettable journeys across the Philippines and beyond. Your adventure, our passion.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">Company</h4>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        <li><a href="{{ route('public.about') }}" class="hover:text-indigo-600">About Us</a></li>
                        <li><a href="{{ route('public.tours') }}" class="hover:text-indigo-600">Tours & Services</a></li>
                        <li><a href="{{ route('public.destinations') }}" class="hover:text-indigo-600">Destinations</a></li>
                        <li><a href="{{ route('public.careers') }}" class="hover:text-indigo-600">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">Support</h4>
                    <ul class="mt-3 space-y-2 text-sm text-gray-600">
                        <li><a href="{{ route('public.contact') }}" class="hover:text-indigo-600">Contact Us</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-indigo-600">Applicant Login</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-indigo-600">Create Account</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">Follow Us</h4>
                    <div class="mt-3 flex gap-3">
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm hover:bg-indigo-600 hover:text-white"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm hover:bg-indigo-600 hover:text-white"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm hover:bg-indigo-600 hover:text-white"><i class="fa-brands fa-twitter"></i></a>
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-500 shadow-sm hover:bg-indigo-600 hover:text-white"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-8 border-t border-gray-200 pt-6 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} Hiraya Travel and Tours. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>
