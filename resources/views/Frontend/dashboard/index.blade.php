@extends('Frontend.layouts.app')

@section('content')
<section>
    <section class="bg-gradient-to-r from-gray-600 to-blue-500 text-white py-16">
    <div class="container mx-auto px-6 text-center">
        <h1 class="text-5xl font-extrabold mb-4 drop-shadow-lg">Dashboard</h1>
        <p class="text-lg max-w-2xl mx-auto opacity-90">
            Welcome to your dashboard! Here you can manage {{$student->name}} your activities and view important information.
        </p>
    </div>
</section>
</section>


@endsection