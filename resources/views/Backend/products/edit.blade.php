@extends('Backend.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
  <div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">Edit Product</h1>
    <a href="{{ route('products.index') }}" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">← Back</a>
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc list-inside text-sm">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b bg-gray-50 px-5 py-4"><h2 class="text-sm font-semibold text-gray-700">Product Information</h2></div>
    <div class="px-5 py-6">
      <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')
        @include('backend.products._form', ['product' => $product])
      </form>
    </div>
  </div>
</div>
@endsection
