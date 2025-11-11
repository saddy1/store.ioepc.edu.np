@extends('Backend.layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
  <div class="mb-6">
    <h1 class="text-2xl font-bold">Edit Category</h1>
    <p class="text-sm text-gray-500">Update English and Nepali names.</p>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('categories.update', $category) }}"
        class="space-y-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm font-medium mb-1">Category Name (English) <span class="text-red-500">*</span></label>
      <input type="text" name="name_en" value="{{ old('name_en', $category->name_en) }}"
             class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0" required>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Category Name (Nepali)</label>
      <input type="text" name="name_np" value="{{ old('name_np', $category->name_np) }}"
             class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0" placeholder="संचलन सामग्री">
    </div>

    <div class="flex items-center gap-3">
      <button class="rounded-xl bg-gray-900 text-white px-4 py-2">Update</button>
      <a href="{{ route('categories.index') }}" class="text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</div>
@endsection
