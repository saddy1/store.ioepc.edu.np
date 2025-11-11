@extends('Backend.layouts.app')

@section('content')
<div class="py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-md rounded-2xl p-6">
      
      {{-- Header --}}
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Department List</h2>
      </div>

      {{-- Error Messages --}}
      @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
          <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Success Message --}}
      @if (Session::has('message'))
        <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-700">
          {{ Session::get('message') }}
        </div>
      @endif

      {{-- Table --}}
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left font-semibold text-gray-600">SN</th>
              <th class="px-4 py-2 text-left font-semibold text-gray-600">Name of Department</th>
              <th class="px-4 py-2 text-center font-semibold text-gray-600">Status</th>
              <th class="px-4 py-2 text-right font-semibold text-gray-600">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach($department as $key => $department_)
              <tr class="hover:bg-gray-50 transition">
                <form method="POST" action="{{ route('department.update', $department_->id) }}">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="status" value="{{ $department_['status'] ? 1 : 0 }}">
                  <td class="px-4 py-2">{{ $key + 1 }}</td>

                  <td class="px-4 py-2">
                    <span id="label_{{ $key }}" class="text-gray-800">{{ $department_['name'] }}</span>
                    <input id="input_{{ $key }}" value="{{ $department_['name'] }}" 
                      class="hidden w-full border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-gray-400 focus:ring-0" 
                      type="text" name="name" required placeholder="Enter Department Name">
                  </td>

                  <td class="px-4 py-2 text-center">
                    <button type="button" onclick="document.getElementById('submitStatus_{{ $key }}').submit()" 
                      class="p-2 rounded-full hover:bg-gray-100 transition">
                      @if($department_['status'] == 1)
                        <i class="fa fa-check-circle text-green-500 text-lg"></i>
                      @else
                        <i class="fa fa-times-circle text-red-500 text-lg"></i>
                      @endif
                    </button>
                  </td>

                  <td class="px-4 py-2 text-right">
                    <button type="button"
                      onclick="document.getElementById('label_{{ $key }}').classList.add('hidden'); 
                               document.getElementById('input_{{ $key }}').classList.remove('hidden'); 
                               document.getElementById('btn_{{ $key }}').classList.remove('hidden'); 
                               this.classList.add('hidden');"
                      class="inline-flex items-center px-3 py-1.5 bg-gray-200 hover:bg-gray-300 rounded-lg text-gray-700 text-sm font-medium">
                      <i class="fa fa-edit mr-2"></i> Edit
                    </button>

                    <button id="btn_{{ $key }}" type="submit"
                      class="hidden inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium">
                      <i class="fa fa-save mr-2"></i> Save
                    </button>
                  </td>
                </form>

                {{-- Toggle Form --}}
                <form id="submitStatus_{{ $key }}" method="POST" action="{{ route('department.update', $department_->id) }}">
                  @csrf
                  @method('PUT')
                  <input type="hidden" name="name" value="{{ $department_['name'] }}">
                  <input type="hidden" name="status" value="{{ $department_['status'] ? 0 : 1 }}">
                </form>
              </tr>
            @endforeach

          {{-- Add New Row --}}
<tr class="bg-gray-50">
    <form method="POST" action="{{ route('department.store') }}">
        @csrf
        <td class="px-4 py-2 font-medium text-gray-700">{{ $department->count() + 1 }}</td>
        <td class="px-4 py-2">
            <input type="text" name="name" placeholder="Enter Department Name" required
                   class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm focus:border-gray-400 focus:ring-0">
        </td>
        <td class="px-4 py-2 text-center">
            <i class="fa fa-check-circle text-green-500 text-lg"></i>
        </td>
        <td class="px-4 py-2 text-right">
            <button type="submit"
                    class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                <i class="fa fa-plus-circle mr-2"></i> Add
            </button>
        </td>
    </form>
</tr>

          </tbody>
    
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
