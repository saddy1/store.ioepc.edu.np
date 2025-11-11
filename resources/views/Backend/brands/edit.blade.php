@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  {{-- Header --}}
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Edit Brand</h1>
      <p class="text-sm text-gray-500 mt-1">Update the brand name and status.</p>
    </div>
    <a href="{{ route('brands.index') }}"
       class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      ← Back to Brands
    </a>
  </div>

  {{-- Errors --}}
  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Form Card --}}
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b bg-gray-50 px-5 py-4">
      <h2 class="text-sm font-semibold text-gray-700">Brand Information</h2>
    </div>

    <div class="px-5 py-6">
      <form method="POST" action="{{ route('brands.update', $brand->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Name --}}
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
            Brand Name <span class="text-red-500">*</span>
          </label>
          <input type="text" id="name" name="name"
                 value="{{ old('name', $brand->name) }}"
                 class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
                 placeholder="e.g., Samsung" required>
          @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Active --}}
        <div class="flex items-center gap-2">
          <input type="checkbox" id="is_active" name="is_active" value="1"
                 {{ old('is_active', $brand->is_active) ? 'checked' : '' }}
                 class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
          <label for="is_active" class="text-sm text-gray-700">Active</label>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row justify-end items-stretch sm:items-center gap-3 pt-2">
          <a href="{{ route('brands.index') }}"
             class="inline-flex justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Cancel
          </a>
          <button type="submit"
                  class="inline-flex justify-center rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800">
            Update Brand
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
