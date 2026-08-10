@extends('layouts.public')

@section('title', 'Welcome to Hiraya Travel and Tours')

@section('content')
{{-- Hero Section --}}
<section class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 text-white">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px); background-size: 24px 24px;"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-200"><i class="fa-solid fa-plane-departure mr-1"></i> Discover Your Next Adventure</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight sm:text-5xl lg:text-6xl">Explore the Beauty of the Philippines with Hiraya</h1>
            <p class="mt-6 text-lg text-indigo-100">From pristine beaches to majestic mountains, we craft unforgettable travel experiences tailored to you. Join our growing family of travel enthusiasts.</p>
            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('public.tours') }}" class="rounded-md bg-white px-6 py-3 text-sm font-semibold text-indigo-700 shadow-lg hover:bg-indigo-50">Explore Tours</a>
                <a href="{{ route('public.careers') }}" class="rounded-md border border-white/40 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">Join Our Team</a>
            </div>
        </div>
    </div>
</section>

{{-- Intro --}}
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Welcome to Hiraya</p>
            <h2 class="mt-3 text-3xl font-bold text-gray-900">Your Trusted Travel Partner</h2>
            <p class="mt-4 text-base text-gray-600">Hiraya Travel and Tours is dedicated to providing exceptional travel experiences that create lasting memories. Whether you're seeking a relaxing beach getaway, an adventurous mountain trek, or a cultural immersion, our team of experts is here to guide you every step of the way.</p>
            <div class="mt-8 grid grid-cols-3 gap-4">
                <div class="rounded-lg bg-indigo-50 p-4 text-center">
                    <p class="text-3xl font-bold text-indigo-600">10+</p>
                    <p class="mt-1 text-sm text-gray-600">Years of Experience</p>
                </div>
                <div class="rounded-lg bg-indigo-50 p-4 text-center">
                    <p class="text-3xl font-bold text-indigo-600">50+</p>
                    <p class="mt-1 text-sm text-gray-600">Destinations</p>
                </div>
                <div class="rounded-lg bg-indigo-50 p-4 text-center">
                    <p class="text-3xl font-bold text-indigo-600">10k+</p>
                    <p class="mt-1 text-sm text-gray-600">Happy Travelers</p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=60" alt="Beach" class="h-48 w-full rounded-lg object-cover shadow-md">
            <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=400&q=60" alt="Mountains" class="mt-8 h-48 w-full rounded-lg object-cover shadow-md">
            <img src="https://images.unsplash.com/photo-1476900543704-4312b78632f8?auto=format&fit=crop&w=400&q=60" alt="Island" class="h-48 w-full rounded-lg object-cover shadow-md">
            <img src="https://images.unsplash.com/photo-1500835556837-99ac94a94552?auto=format&fit=crop&w=400&q=60" alt="Travel" class="mt-8 h-48 w-full rounded-lg object-cover shadow-md">
        </div>
    </div>
</section>

{{-- Featured Tours --}}
<section class="bg-gray-50 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Featured Tours</p>
                <h2 class="mt-3 text-3xl font-bold text-gray-900">Popular Tours & Services</h2>
            </div>
            <a href="{{ route('public.tours') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">View All</a>
        </div>
        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['icon' => 'fa-umbrella-beach', 'title' => 'Island Hopping', 'desc' => 'Explore the pristine islands of Palawan and Siargao.'],
                ['icon' => 'fa-mountain-sun', 'title' => 'Adventure Treks', 'desc' => 'Conquer the majestic mountains of Luzon and Visayas.'],
                ['icon' => 'fa-city', 'title' => 'City Escapes', 'desc' => 'Discover the vibrant culture of Manila, Cebu, and Davao.'],
                ['icon' => 'fa-water-ladder', 'title' => 'Diving Expeditions', 'desc' => 'Dive into the crystal-clear waters of the Coral Triangle.'],
            ] as $tour)
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"><i class="fa-solid {{ $tour['icon'] }} text-xl"></i></span>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $tour['title'] }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $tour['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Popular Destinations --}}
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="text-center">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Popular Destinations</p>
        <h2 class="mt-3 text-3xl font-bold text-gray-900">Places Our Travelers Love</h2>
    </div>
    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['name' => 'Palawan', 'img' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=60'],
            ['name' => 'Boracay', 'img' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=400&q=60'],
            ['name' => 'Siargao', 'img' => 'https://images.unsplash.com/photo-1500835556837-99ac94a94552?auto=format&fit=crop&w=400&q=60'],
            ['name' => 'Baguio', 'img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=400&q=60'],
        ] as $dest)
        <div class="group relative overflow-hidden rounded-lg shadow-md">
            <img src="{{ $dest['img'] }}" alt="{{ $dest['name'] }}" class="h-64 w-full object-cover transition-transform duration-300 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
            <div class="absolute bottom-0 p-4">
                <h3 class="text-lg font-bold text-white">{{ $dest['name'] }}</h3>
                <p class="text-sm text-white/80">Discover more →</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- Why Choose Us --}}
<section class="bg-indigo-600 py-16 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-200">Why Choose Us</p>
            <h2 class="mt-3 text-3xl font-bold">The Hiraya Difference</h2>
        </div>
        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['icon' => 'fa-star', 'title' => 'Expert Guides', 'desc' => 'Local experts who know every hidden gem.'],
                ['icon' => 'fa-shield-halved', 'title' => 'Safe & Secure', 'desc' => 'Your safety is our top priority on every trip.'],
                ['icon' => 'fa-hand-holding-heart', 'title' => 'Personalized Service', 'desc' => 'Tailored itineraries built around you.'],
                ['icon' => 'fa-tags', 'title' => 'Best Value', 'desc' => 'Competitive pricing without compromising quality.'],
            ] as $item)
            <div class="rounded-lg bg-white/10 p-6 backdrop-blur">
                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20"><i class="fa-solid {{ $item['icon'] }} text-xl"></i></span>
                <h3 class="mt-4 text-lg font-semibold">{{ $item['title'] }}</h3>
                <p class="mt-2 text-sm text-indigo-100">{{ $item['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Careers CTA --}}
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-700 to-purple-700 p-8 text-white md:p-12">
        <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
            <div>
                <h2 class="text-2xl font-bold md:text-3xl">Join Our Growing Team</h2>
                <p class="mt-2 text-indigo-100">Explore exciting career opportunities in the travel and tourism industry with Hiraya Travel and Tours.</p>
            </div>
            <a href="{{ route('public.careers') }}" class="shrink-0 rounded-md bg-white px-6 py-3 text-sm font-semibold text-indigo-700 shadow-lg hover:bg-indigo-50">View Open Positions</a>
        </div>
    </div>
</section>

{{-- Featured Open Positions --}}
@if($featuredJobs->isNotEmpty())
<section class="bg-gray-50 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Careers</p>
                <h2 class="mt-3 text-3xl font-bold text-gray-900">Featured Open Positions</h2>
            </div>
            <a href="{{ route('public.careers') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">All Jobs</a>
        </div>
        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2">
            @foreach($featuredJobs as $job)
            <a href="{{ route('public.jobs.show', $job) }}" class="flex items-center justify-between rounded-lg bg-white p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $job->title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $job->department->name ?? 'N/A' }} • {{ $job->location ?? 'On-site' }}</p>
                </div>
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700">View Job <i class="fa-solid fa-arrow-right ml-1"></i></span>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- Contact CTA --}}
<section id="contact" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Contact Us</p>
            <h2 class="mt-3 text-3xl font-bold text-gray-900">Plan Your Dream Trip</h2>
            <p class="mt-4 text-base text-gray-600">Have questions about our tours or services? Our team is ready to help you plan the perfect getaway. Reach out today and let's start your adventure.</p>
            <div class="mt-6 space-y-3 text-sm text-gray-600">
                <p><i class="fa-solid fa-location-dot text-indigo-600 mr-2"></i>123 Rizal Avenue, Makati City, Philippines</p>
                <p><i class="fa-solid fa-phone text-indigo-600 mr-2"></i>+63 (2) 8123-4567</p>
                <p><i class="fa-solid fa-envelope text-indigo-600 mr-2"></i>hello@hirayatravel.com</p>
            </div>
        </div>
<form class="space-y-4" method="POST" action="{{ route('public.contact.submit') }}">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" name="name" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Message</label>
                <textarea name="message" rows="4" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>
            <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Send Message</button>
        </form>
    </div>
</section>
@endsection
