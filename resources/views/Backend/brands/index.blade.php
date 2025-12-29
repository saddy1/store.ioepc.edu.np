@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  {{-- Header --}}
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Brands</h1>
      <p class="text-sm text-gray-500 mt-1">Manage brand names and their status.</p>
    </div>
    <a href="{{ route('brands.create') }}"
       class="inline-flex items-center rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm font-semibold shadow hover:bg-gray-800 transition">
      + Add Brand
    </a>
  </div>

  {{-- Alerts --}}
  @if(session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- Search --}}
  <form method="GET" action="{{ route('brands.index') }}" class="mb-6">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
      <div class="flex-1 relative">
        <input type="text" name="search" value="{{ request('search') }}"
               class="w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-10 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
               placeholder="Search brand name...">
        @if(request('search'))
          <a href="{{ route('brands.index') }}"
             class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </a>
        @endif
      </div>
      <button type="submit"
              class="inline-flex items-center justify-center rounded-xl bg-blue-600 text-white px-5 py-2.5 text-sm font-medium hover:bg-blue-700 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
        </svg>
        Search
      </button>
    </div>
  </form>

  {{-- Table Card --}}
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b bg-gray-50 px-4 py-3 text-sm text-gray-600">
      Total: <span class="font-semibold">{{ $brands->total() }}</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
          <tr>
            <th class="px-4 py-3 w-16">S.N</th>
            <th class="px-4 py-3">Name</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          @forelse($brands as $row)
            <tr class="hover:bg-gray-50 transition">
              <td class="px-4 py-3">
                {{ $brands->firstItem() + $loop->index }}
              </td>
              <td class="px-4 py-3 font-medium text-gray-900">
                {{ $row->name }}
              </td>
              <td class="px-4 py-3">
                @php
                  $active = (bool) $row->is_active;
                  $badge = $active
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                    : 'bg-red-50 text-red-700 ring-1 ring-red-200';
                @endphp
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">
                  {{ $active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-end gap-2">
                  <a href="{{ route('brands.edit', $row) }}"
                     class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm14.71-9.04a1.003 1.003 0 000-1.42l-2.5-2.5a1.003 1.003 0 00-1.42 0l-1.83 1.83 3.75 3.75 1.99-1.66z"/>
                    </svg>
                    Edit
                  </a>
                  {{-- <form action="{{ route('brands.destroy', $row) }}" method="POST"
                        onsubmit="return confirm('Delete this brand?')" class="inline">
                    @csrf @method('DELETE')
                    <button
                      class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M6 7h12l-1 13H7L6 7zm3-3h6l1 2H8l1-2z"/>
                      </svg>
                      Delete
                    </button>
                  </form> --}}
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">No brands found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="px-4 py-3 border-t bg-gray-50">
      {{ $brands->links('pagination::tailwind') }}
    </div>
  </div>
</div>
@endsection
