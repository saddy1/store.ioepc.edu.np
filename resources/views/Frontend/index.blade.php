@extends('frontend.layouts.app')

@push('head')
    <title>Home | Admission Portal</title>
@endpush

@section('content')
<div class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('asset/assets/ioe_logo.png') }}" alt="Logo" class="h-10 w-auto">
                    <span class="text-xl font-bold text-blue-900">IOE Purwanchal Campus</span>
                </a>

                <!-- Login Button -->
                <div>
                    <a href=""
                       class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center bg-gradient-to-r from-blue-50 to-slate-100">
        <div class="text-center px-6">
            <h1 class="text-3xl sm:text-5xl font-bold text-blue-900 mb-4">
                Welcome to Student Portal
            </h1>
            <p class="text-gray-600 text-lg sm:text-xl mb-6">
                Submit online for Exam Registration at IOE Purwanchal Campus
            </p>
            <a href=""
               class="px-6 py-3 bg-blue-700 text-white rounded-xl shadow hover:bg-blue-800 transition">
                Get Started
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white text-center py-4">
        <p class="text-sm">© {{ date('Y') }} IOE Purwanchal Campus. All Rights Reserved.</p>
    </footer>
</div>
@endsection
