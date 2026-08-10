@extends('layouts.public')

@section('title', 'Contact Us')

@section('content')
<section class="bg-gradient-to-br from-indigo-700 via-indigo-600 to-purple-700 text-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-200">Contact Us</p>
        <h1 class="mt-3 text-4xl font-bold">Get in Touch</h1>
        <p class="mt-4 max-w-2xl text-lg text-indigo-100">Have a question about our tours or services? We'd love to hear from you. Reach out and our team will respond promptly.</p>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 gap-12 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"><i class="fa-solid fa-location-dot"></i></span>
                <h3 class="mt-3 text-lg font-semibold text-gray-900">Our Office</h3>
                <p class="mt-2 text-sm text-gray-600">123 Rizal Avenue,<br>Makati City, Philippines</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"><i class="fa-solid fa-phone"></i></span>
                <h3 class="mt-3 text-lg font-semibold text-gray-900">Call Us</h3>
                <p class="mt-2 text-sm text-gray-600">+63 (2) 8123-4567<br>+63 917 123 4567</p>
            </div>
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200">
                <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600"><i class="fa-solid fa-envelope"></i></span>
                <h3 class="mt-3 text-lg font-semibold text-gray-900">Email Us</h3>
                <p class="mt-2 text-sm text-gray-600">hello@hirayatravel.com<br>support@hirayatravel.com</p>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-lg bg-white p-6 shadow-sm border border-gray-200 md:p-8">
                <h2 class="text-2xl font-bold text-gray-900">Send Us a Message</h2>
                <p class="mt-2 text-sm text-gray-600">Fill out the form below and we'll get back to you as soon as possible.</p>
<form method="POST" action="{{ route('public.contact.submit') }}" class="mt-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Subject</label>
                        <input type="text" name="subject" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <textarea name="message" rows="5" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                    <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
