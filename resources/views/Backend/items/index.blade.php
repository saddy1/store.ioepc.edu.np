@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
      <h1 class="text-2xl font-bold tracking-tight">Item Categories</h1>
      <p class="text-sm text-gray-500 mt-1">Manage consumable / non-consumable / other (with Nepali labels).</p>
    </div>
    <a href="{{ route('categories.create') }}"
       class="inline-flex items-center gap-2 rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm font-semibold shadow hover:bg-gray-800">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H6a1 1 0 110-2h5V6a1 1 0 011-1z"/></svg>
      Add Category
    </a>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
      {{ session('success') }}
    </div>
  @endif

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between border-b bg-gray-50 px-4 py-3">
      <div class="text-sm text-gray-600">
        Total:
        <span class="font-semibold">
          {{-- safe with paginator; if you later return a Collection, use count() --}}
          {{ method_exists($categories, 'total') ? $categories->total() : $categories->count() }}
        </span>
      </div>
      <div class="text-xs text-gray-500">Tip: Click a row to edit.</div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="bg-gray-50 text-xs uppercase text-gray-600">
          <tr>
            <th class="px-4 py-3">English Name</th>
            <th class="px-4 py-3">Nepali Name</th>
            <th class="px-4 py-3">Badge</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100">
          @forelse($categories as $cat)
            @php
              $badge = strtolower($cat->name_en ?? '');
              $badgeClasses = str_contains($badge, 'consum')
                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                : (str_contains($badge, 'non')
                    ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'
                    : 'bg-gray-50 text-gray-700 ring-1 ring-gray-200');
            @endphp

            {{-- whole row clickable → edit --}}
            <tr onclick="window.location='{{ route('categories.edit', $cat) }}'"
                class="group cursor-pointer bg-white hover:bg-gray-50 transition">
              <td class="px-4 py-3 font-medium text-gray-900">
                <span class="group-hover:underline">{{ $cat->name_en }}</span>
              </td>
              <td class="px-4 py-3 text-gray-800">{{ $cat->name_np }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClasses }}">
                  {{ $cat->name_en }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                  <a href="{{ route('categories.edit', $cat) }}"
                     class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                     title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm14.71-9.04a1.003 1.003 0 000-1.42l-2.5-2.5a1.003 1.003 0 00-1.42 0l-1.83 1.83 3.75 3.75 1.99-1.66z"/></svg>
                    Edit
                  </a>
                  <form action="{{ route('categories.destroy', $cat) }}" method="POST"
                        onsubmit="return confirm('Delete this category?')" class="inline">
                    @csrf @method('DELETE')
                    <button
                      class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100"
                      title="Delete">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M6 7h12l-1 13H7L6 7zm3-3h6l1 2H8l1-2z"/></svg>
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td class="px-4 py-6 text-gray-500" colspan="4">No categories yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if(method_exists($categories, 'links'))
      <div class="px-4 py-3 border-t bg-gray-50">
        {{ $categories->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
