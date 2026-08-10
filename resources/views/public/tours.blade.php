@extends('layouts.public')

@section('title', 'Tours & Services')

@section('content')
<section class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 text-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-200">Tours & Services</p>
        <h1 class="mt-3 text-4xl font-bold">Explore Our Tours</h1>
        <p class="mt-4 max-w-2xl text-lg text-indigo-100">From relaxing getaways to thrilling adventures, discover the perfect tour crafted by our expert team.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['icon' => 'fa-umbrella-beach', 'title' => 'Beach & Island Tours', 'desc' => 'Escape to paradise with our beach and island hopping tours. Swim in crystal-clear waters, lounge on white sand, and soak up the tropical sun.', 'img' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=60'],
            ['icon' => 'fa-mountain-sun', 'title' => 'Mountain & Adventure Tours', 'desc' => 'Challenge yourself with our trekking and adventure tours. Conquer majestic peaks, explore hidden waterfalls, and embrace the great outdoors.', 'img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=600&q=60'],
            ['icon' => 'fa-city', 'title' => 'Cultural & City Tours', 'desc' => 'Immerse yourself in local culture and history. Visit heritage sites, museums, and vibrant markets with our knowledgeable guides.', 'img' => 'https://images.unsplash.com/photo-1500835556837-99ac94a94552?auto=format&fit=crop&w=600&q=60'],
            ['icon' => 'fa-water-ladder', 'title' => 'Diving & Water Sports', 'desc' => 'Discover the underwater world with our diving expeditions and water sports packages. Perfect for beginners and experienced divers alike.', 'img' => 'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=600&q=60'],
            ['icon' => 'fa-helicopter', 'title' => 'Luxury Getaways', 'desc' => 'Indulge in exclusive luxury travel experiences. Private villas, personalized service, and unforgettable moments await.', 'img' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=600&q=60'],
            ['icon' => 'fa-people-group', 'title' => 'Group & Corporate Tours', 'desc' => 'Plan the perfect group or corporate retreat. We handle everything from transportation to team-building activities.', 'img' => 'https://images.unsplash.com/photo-1522199755839-a2bacb67c546?auto=format&fit=crop&w=600&q=60'],
        ] as $tour)
        <div class="overflow-hidden rounded-lg bg-white shadow-sm border border-gray-200 hover:shadow-md transition">
            <img src="{{ $tour['img'] }}" alt="{{ $tour['title'] }}" class="h-48 w-full object-cover">
            <div class="p-6">
                <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"><i class="fa-solid {{ $tour['icon'] }} text-lg"></i></span>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ $tour['title'] }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $tour['desc'] }}</p>
                <a href="{{ route('public.contact') }}" class="mt-4 inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500">Inquire Now <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
        </div>
        @endforeach
    </div>
</section>

<section class="bg-indigo-600 py-16 text-white">
    <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold">Ready to Book Your Adventure?</h2>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-indigo-100">Contact our friendly team today and let us help you plan the journey of a lifetime.</p>
        <a href="{{ route('public.contact') }}" class="mt-6 inline-block rounded-md bg-white px-6 py-3 text-sm font-semibold text-indigo-700 shadow-lg hover:bg-indigo-50">Contact Us</a>
    </div>
</section>
@endsection
