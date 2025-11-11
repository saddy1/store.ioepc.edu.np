@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  {{-- Success message --}}
  @if(session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
      {{ session('success') }}
    </div>
  @endif

  {{-- Header --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Suppliers</h1>
    <a href="{{ route('suppliers.create') }}"
       class="inline-flex items-center rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm font-semibold shadow hover:bg-gray-800">
      + Add Supplier
    </a>
  </div>

  {{-- Search Bar --}}
  <form method="GET" action="{{ route('suppliers.index') }}" class="mb-6">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
      <div class="flex-1 relative">
        <input type="text" name="search"
               value="{{ request('search') }}"
               class="w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-10 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400"
               placeholder="Search by supplier name or PAN...">
        @if(request('search'))
          <a href="{{ route('suppliers.index') }}"
             class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </a>
        @endif
      </div>
      <button type="submit"
              class="inline-flex items-center justify-center rounded-xl bg-blue-600 text-white px-5 py-2.5 text-sm font-medium hover:bg-blue-700 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
        Search
      </button>
    </div>
  </form>

  {{-- Table --}}
  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 text-xs uppercase text-gray-600">
        <tr>
          <th class="px-4 py-3 w-16">S.N</th>
          <th class="px-4 py-3">Name</th>
          <th class="px-4 py-3">PAN</th>
          <th class="px-4 py-3">Address</th>
          <th class="px-4 py-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white">
        @php
          // get the starting index based on current page
          $sn = ($suppliers->currentPage() - 1) * $suppliers->perPage() + 1;
        @endphp

        @forelse($suppliers as $s)
          <tr class="hover:bg-gray-50 transition">
            <td class="px-4 py-3 text-gray-800">{{ $sn++ }}</td>
            <td class="px-4 py-3 font-medium text-gray-900">{{ $s->name }}</td>
            <td class="px-4 py-3 text-gray-700">{{ $s->pan }}</td>
            <td class="px-4 py-3 text-gray-700">{{ $s->address }}</td>
            <td class="px-4 py-3 text-right space-x-2">
              <a href="{{ route('suppliers.edit', $s) }}"
                 class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.232 5.232l3.536 3.536m-2.036-1.5a2.121 2.121 0 113.001 3.001L7.5 21H4v-3.5L16.732 7.268z" />
                </svg>
                Edit
              </a>
              <form action="{{ route('suppliers.destroy', $s) }}" method="POST" class="inline">
                @csrf @method('DELETE')
                <button class="inline-flex items-center text-red-600 hover:text-red-800 text-sm font-medium"
                        onclick="return confirm('Delete this supplier?')">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                       viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Delete
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No suppliers found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-4">
    {{ $suppliers->links() }}
  </div>
</div>
@endsection
