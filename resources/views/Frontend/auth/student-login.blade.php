@extends('Frontend.layouts.app')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-100 via-blue-200 to-blue-300 p-4">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg border border-slate-200">

            <!-- Heading -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-blue-900">Student Login</h1>
                <p class="text-sm text-slate-600 mt-1">Enter your Token and Roll Number to continue</p>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('student.login') }}" class="space-y-4">
                @csrf

                <!-- Token Number -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700">Token Number</label>
                    <input type="text" name="token_num" value="{{ old('token_num') }}" required
                        class="w-full rounded-lg border border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm"
                        placeholder="e.g., T123456" />
                    @error('token_num')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Roll Number -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-700">
                        Roll Number <span class="text-slate-400 text-xs">(format: PUR123ABC456)</span>
                    </label>
                    <input type="text" name="roll_num" value="{{ old('roll_num') }}" required
                        class="w-full rounded-lg border border-slate-300 focus:border-blue-500 focus:ring focus:ring-blue-200 p-2 text-sm"
                        placeholder="PUR123ABC456" />
                    @error('roll_num')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-blue-700 text-white py-2 rounded-lg hover:bg-blue-800 transition shadow-md">
                    Sign In
                </button>
                @if (session('success'))
                    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                        {{ session('error') }}
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
