@extends('Frontend.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">
  <h1 class="text-2xl font-bold">Welcome, {{ auth('employee')->user()->full_name }}</h1>
  <p class="text-sm text-gray-600 mt-2">This is the employee dashboard. (Add invigilator features later.)</p>

 
</div>
@endsection
