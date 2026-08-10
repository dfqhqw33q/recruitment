@extends('layouts.public')

@section('title', 'Destinations')

@section('content')
<section class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 text-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-200">Destinations</p>
        <h1 class="mt-3 text-4xl font-bold">Popular Destinations</h1>
        <p class="mt-4 max-w-2xl text-lg text-indigo-100">Discover the breathtaking destinations that our travelers love the most.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['name' => 'Palawan', 'tag' => 'Beach Paradise', 'img' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=60', 'desc' => 'Home to the stunning Underground River and pristine islands of El Nido and Coron.'],
            ['name' => 'Boracay', 'tag' => 'White Beaches', 'img' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=600&q=60', 'desc' => 'World-famous for its powdery white sand and vibrant nightlife.'],
            ['name' => 'Siargao', 'tag' => 'Surf Capital', 'img' => 'https://images.unsplash.com/photo-1500835556837-99ac94a94552?auto=format&fit=crop&w=600&q=60', 'desc' => 'The surfing capital of the Philippines with laid-back island vibes.'],
            ['name' => 'Baguio', 'tag' => 'Summer Capital', 'img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=600&q=60', 'desc' => 'Cool mountain air, pine trees, and scenic views in the Summer Capital.'],
            ['name' => 'Cebu', 'tag' => 'Historic & Adventure', 'img' => 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?auto=format&fit=crop&w=600&q=60', 'desc' => 'A mix of history, culture, and thrilling water adventures.'],
            ['name' => 'Bohol', 'tag' => 'Nature Wonders', 'img' => 'https://images.unsplash.com/photo-1476900543704-4312b78632f8?auto=format&fit=crop&w=600&q=60', 'desc' => 'Famous for the Chocolate Hills and adorable tarsiers.'],
        ] as $dest)
        <div class="group relative overflow-hidden rounded-lg shadow-md">
            <img src="{{ $dest['img'] }}" alt="{{ $dest['name'] }}" class="h-72 w-full object-cover transition-transform duration-300 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
            <div class="absolute bottom-0 p-5">
                <span class="inline-flex text-xs font-semibold px-2 py-1 rounded-full bg-indigo-500/80 text-white">{{ $dest['tag'] }}</span>
                <h3 class="mt-2 text-xl font-bold text-white">{{ $dest['name'] }}</h3>
                <p class="mt-1 text-sm text-white/80">{{ $dest['desc'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</section>

<section class="bg-indigo-600 py-16 text-white">
    <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold">Can't Find What You're Looking For?</h2>
        <p class="mx-auto mt-4 max-w-2xl text-lg text-indigo-100">We offer custom itineraries to destinations all over the world. Let us create a personalized trip just for you.</p>
        <a href="{{ route('public.contact') }}" class="mt-6 inline-block rounded-md bg-white px-6 py-3 text-sm font-semibold text-indigo-700 shadow-lg hover:bg-indigo-50">Plan My Trip</a>
    </div>
</section>
@endsection
