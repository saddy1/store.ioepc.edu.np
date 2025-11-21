@extends('Frontend.layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
  <h1 class="text-2xl font-bold mb-6 text-center">Change Password</h1>

  @if (session('warning'))
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
      {{ session('warning') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('employee.password.update') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm mb-1">Current Password</label>
      <input type="password" name="current_password" class="w-full rounded-lg border px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm mb-1">New Password</label>
      <input type="password" name="password" class="w-full rounded-lg border px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Confirm Password</label>
      <input type="password" name="password_confirmation" class="w-full rounded-lg border px-3 py-2" required>
    </div>
    <button class="w-full rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm">Update Password</button>
  </form>
</div>
@endsection
