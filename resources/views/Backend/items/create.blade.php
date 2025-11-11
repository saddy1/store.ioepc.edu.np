@extends('Backend.layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
  <h1 class="text-2xl font-semibold mb-6">Add Item Category</h1>

  <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
    @csrf

    <div>
      <label class="block text-sm font-medium mb-1">Category Name (English)</label>
      <input type="text" name="name_en" class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0" placeholder="Consumable" required>
    </div>

    <div>
      <label class="block text-sm font-medium mb-1">Category Name (Nepali)</label>
      <input type="text" name="name_np" class="w-full rounded-lg border-gray-300 focus:border-gray-400 focus:ring-0" placeholder="संचलन सामग्री">
    </div>

    <div class="flex gap-3">
      <button class="bg-gray-900 text-white px-4 py-2 rounded-lg">Save</button>
      <a href="{{ route('categories.index') }}" class="text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</div>
@endsection
