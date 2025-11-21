@extends('Backend.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
  <h1 class="text-2xl font-bold mb-6">Edit Employee</h1>

  @if ($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc list-inside text-sm">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('employees.update',$employee) }}" class="space-y-4">
    @csrf @method('PUT')

    <div>
      <label class="block text-sm mb-1">Department *</label>
      <select name="department_id" required class="w-full rounded-lg border px-3 py-2">
        @foreach ($departments as $d)
          <option value="{{ $d->id }}" @selected(old('department_id',$employee->department_id)==$d->id)>{{ $d->name }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm mb-1">Full Name *</label>
      <input name="full_name" value="{{ old('full_name',$employee->full_name) }}" required class="w-full rounded-lg border px-3 py-2">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <div>
        <label class="block text-sm mb-1">Contact</label>
        <input name="contact" value="{{ old('contact',$employee->contact) }}" class="w-full rounded-lg border px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Atten No</label>
        <input name="atten_no" value="{{ old('atten_no',$employee->atten_no) }}" class="w-full rounded-lg border px-3 py-2">
      </div>
      <div>
        <label class="block text-sm mb-1">Email (optional)</label>
        <input type="email" name="email" value="{{ old('email',$employee->email) }}" class="w-full rounded-lg border px-3 py-2">
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Reset Password (optional)</label>
      <input type="password" name="password" class="w-full rounded-lg border px-3 py-2" placeholder="Leave blank to keep same">
      <p class="text-xs text-gray-500 mt-1">If you set a new password, the user will be forced to change it on next login.</p>
    </div>

    <div class="flex items-center gap-4">
      <label class="text-sm">Active</label>
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active',$employee->is_active))>
      <label class="text-sm">Require password change</label>
      <input type="checkbox" name="must_change_password" value="1" @checked(old('must_change_password',$employee->must_change_password))>
    </div>

    <div class="pt-2">
      <button class="rounded-xl bg-gray-900 text-white px-4 py-2.5 text-sm">Update</button>
      <a href="{{ route('employees.index') }}" class="ml-3 text-sm text-gray-600 hover:underline">Cancel</a>
    </div>
  </form>
</div>
@endsection
