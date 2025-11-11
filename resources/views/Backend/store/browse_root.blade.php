@extends('Backend.layouts.app')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
  <h1 class="text-2xl font-bold mb-6">Browse Store</h1>

  <div class="grid sm:grid-cols-2 gap-4">
    <a href="{{ route('store.browse.ic') }}"
       class="block rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow">
      <div class="text-lg font-semibold mb-1">Browse by Item Category</div>
      <p class="text-sm text-gray-600">Pick an Item Category first, then choose a Product Category under it.</p>
    </a>

    <a href="{{ route('store.categories') }}"
       class="block rounded-2xl border border-gray-200 bg-white p-6 shadow-sm hover:shadow">
      <div class="text-lg font-semibold mb-1">Browse by Product Category (All)</div>
      <p class="text-sm text-gray-600">See all Product Categories that have Store Entries.</p>
    </a>
  </div>
</div>
@endsection
