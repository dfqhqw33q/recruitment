@extends('layouts.public')

@section('title', 'About Us')

@section('content')
{{-- Page header --}}
<section class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 text-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-200">About Us</p>
        <h1 class="mt-3 text-4xl font-bold">Our Story</h1>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">About Hiraya Travel and Tours</h2>
            <p class="mt-4 text-base text-gray-600">Hiraya Travel and Tours was founded on a simple belief: travel has the power to transform lives. For over a decade, we have been helping travelers discover the breathtaking beauty of the Philippines and the world beyond.</p>
            <p class="mt-4 text-base text-gray-600">Our team of passionate travel experts works tirelessly to craft personalized itineraries, ensure seamless logistics, and deliver experiences that exceed expectations. Whether you're a solo adventurer, a romantic couple, or a family seeking fun, we have the perfect journey for you.</p>
            <div class="mt-8 grid grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-2xl font-bold text-indigo-600">10+</p>
                    <p class="mt-1 text-sm text-gray-600">Years of Excellence</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-2xl font-bold text-indigo-600">50+</p>
                    <p class="mt-1 text-sm text-gray-600">Expert Guides</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-2xl font-bold text-indigo-600">10k+</p>
                    <p class="mt-1 text-sm text-gray-600">Happy Travelers</p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4">
                    <p class="text-2xl font-bold text-indigo-600">4.9/5</p>
                    <p class="mt-1 text-sm text-gray-600">Average Rating</p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <img src="https://images.unsplash.com/photo-1476900543704-4312b78632f8?auto=format&fit=crop&w=400&q=60" alt="Travel" class="h-64 w-full rounded-lg object-cover shadow-md">
            <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=60" alt="Beach" class="mt-8 h-64 w-full rounded-lg object-cover shadow-md">
        </div>
    </div>
</section>

<section class="bg-gray-50 py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-gray-900">Our Mission & Vision</h2>
        </div>
        <div class="mt-10 grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-lg bg-white p-8 shadow-sm border border-gray-200">
                <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"><i class="fa-solid fa-bullseye text-xl"></i></span>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Our Mission</h3>
                <p class="mt-2 text-sm text-gray-600">To provide exceptional travel experiences that inspire, connect, and create lasting memories for every traveler we serve.</p>
            </div>
            <div class="rounded-lg bg-white p-8 shadow-sm border border-gray-200">
                <span class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"><i class="fa-solid fa-eye text-xl"></i></span>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">Our Vision</h3>
                <p class="mt-2 text-sm text-gray-600">To be the leading travel and tourism company in the Philippines, known for innovation, integrity, and unforgettable adventures.</p>
            </div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900">Our Core Values</h2>
    </div>
    <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['icon' => 'fa-heart', 'title' => 'Passion', 'desc' => 'We love what we do and it shows in every journey.'],
            ['icon' => 'fa-handshake', 'title' => 'Integrity', 'desc' => 'We operate with honesty and transparency always.'],
            ['icon' => 'fa-lightbulb', 'title' => 'Innovation', 'desc' => 'We embrace new ideas to enhance your experience.'],
            ['icon' => 'fa-users', 'title' => 'Community', 'desc' => 'We build connections among travelers and hosts.'],
        ] as $value)
        <div class="rounded-lg bg-white p-6 text-center shadow-sm border border-gray-200">
            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mx-auto"><i class="fa-solid {{ $value['icon'] }} text-xl"></i></span>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $value['title'] }}</h3>
            <p class="mt-2 text-sm text-gray-600">{{ $value['desc'] }}</p>
        </div>
        @endforeach
    </div>
</section>
@endsection
