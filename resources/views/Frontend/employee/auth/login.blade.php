@extends('Frontend.layouts.app') {{-- reuse your layout if you want --}}

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
  <h1 class="text-2xl font-bold mb-6 text-center">Employee Login</h1>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('employee.login.post') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input type="email" name="email" class="w-full rounded-lg border px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Password</label>
      <input type="password" name="password" class="w-full rounded-lg border px-3 py-2" required>
    </div>
    <div class="flex items-center gap-2">
      <input type="checkbox" name="remember" id="remember">
      <label for="remember" class="text-sm">Remember me</label>
    </div>
    <button class="w-full rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm">Login</button>
  </form>
</div>
@endsection
