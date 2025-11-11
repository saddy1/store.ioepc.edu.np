@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <!-- Header -->
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Add Supplier</h1>
      <p class="text-sm text-gray-500 mt-1">Create a new supplier with name, PAN, and address.</p>
    </div>
    <a href="{{ route('suppliers.index') }}"
       class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      ← Back to Suppliers
    </a>
  </div>

  <!-- Alerts -->
  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Card -->
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b bg-gray-50 px-5 py-4">
      <h2 class="text-sm font-semibold text-gray-700">Supplier Information</h2>
    </div>

    <div class="px-5 py-6">
      <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-6">
        @csrf
       {{-- Shared form fields for Create & Edit --}}

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
  {{-- Name --}}
  <div class="col-span-1">
    <label class="block text-sm font-medium text-gray-700 mb-1">
      Supplier Name <span class="text-red-500">*</span>
    </label>
    <input type="text" name="name"
           value="{{ old('name', $supplier->name ?? '') }}"
           class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
           placeholder="e.g., ABC Traders" required>
    @error('name')
      <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
  </div>

  {{-- PAN --}}
  <div class="col-span-1">
    <label class="block text-sm font-medium text-gray-700 mb-1">
      PAN Number <span class="text-red-500">*</span>
    </label>
    <input id="pan" type="text" name="pan"
           value="{{ old('pan', $supplier->pan ?? '') }}"
           class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
           inputmode="numeric" autocomplete="off" maxlength="9" placeholder="9-digit PAN (e.g., 123456789)" required>
    <p class="text-xs text-gray-500 mt-1">Use digits only; 9 characters.</p>
    @error('pan')
      <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
  </div>

  {{-- Address (full width on sm+) --}}
  <div class="sm:col-span-2">
    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
    <input type="text" name="address"
           value="{{ old('address', $supplier->address ?? '') }}"
           class="w-full rounded-lg border border-gray-300 focus:border-gray-400 focus:ring-0 px-3 py-2"
           placeholder="e.g., Patan, Lalitpur">
    @error('address')
      <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
  </div>
</div>

{{-- Actions --}}
<div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
  <button class="inline-flex justify-center rounded-xl bg-gray-900 text-white px-5 py-2.5 text-sm font-semibold hover:bg-gray-800">
    {{ ($mode ?? 'create') === 'edit' ? 'Update Supplier' : 'Save Supplier' }}
  </button>
  <a href="{{ route('suppliers.index') }}"
     class="inline-flex justify-center rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
    Cancel
  </a>
</div>

      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  // Keep PAN numeric and max 9
  document.addEventListener('DOMContentLoaded', function () {
    const pan = document.getElementById('pan');
    if (!pan) return;
    pan.addEventListener('input', () => {
      pan.value = pan.value.replace(/\D/g,'').slice(0, 9);
    });
  });
</script>
@endpush
@endsection
