{{-- resources/views/Backend/student/import.blade.php --}}
@extends('Backend.layouts.app')

@section('title', 'Import Students')

@section('content')
    <div class="mx-auto bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
        <h1 class="text-xl font-semibold text-gray-900 mb-2">Import Students</h1>
        <p class="text-sm text-gray-600 mb-6">
            Upload a CSV or Excel file with columns:
            <strong>token, roll, name, (ignore), (ignore), year, part, subject</strong>.
        </p>

        {{-- Alerts --}}
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        {{-- Import form --}}
     <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
  @csrf

  <div class="grid gap-6 md:grid-cols-2">
    <div>
      <label class="block text-sm font-medium mb-1">File</label>
      <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls"
             class="w-full p-2 border border-blue-300 focus:ring-blue-600 focus:border-blue-600" required>
      <p class="text-xs text-gray-500 mt-1">Tip: Your sample looks tab-separated. CSV or XLSX both work.</p>
    </div>

    {{-- Keep current per_page/q so we return with same view state --}}
    <input type="hidden" name="per_page" value="{{ request('per_page', $perPage ?? 10) }}">
    <input type="hidden" name="q" value="{{ request('q') }}">

    <div>
      <label class="block text-sm font-medium mb-2 text-gray-700">Payment Type</label>

      {{-- Hidden field ensures "0" is sent when unchecked --}}
      <input type="hidden" name="fine" value="0">

      {{-- Toggle --}}
      <label for="fine" class="inline-flex items-center cursor-pointer select-none">
        <input
          id="fine"
          type="checkbox"
          name="fine"
          value="1"
          class="sr-only peer"
          @checked(old('fine', request('fine', 0)) == 1)
        />

        <!-- Track (with knob via ::after) -->
        <span class="relative w-14 h-8 rounded-full bg-gray-300 transition-colors duration-300
                     peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500
                     peer-checked:bg-green-500
                     after:content-[''] after:absolute after:top-1 after:left-1 after:w-6 after:h-6
                     after:bg-white after:rounded-full after:shadow-md after:transition-transform after:duration-300
                     peer-checked:after:translate-x-6">
        </span>

        <!-- Label that reflects state -->
        <span class="ml-3 text-sm font-medium text-gray-700">
          <span class="peer-checked:hidden">Fine: OFF</span>
          <span class="hidden peer-checked:inline">Fine: ON</span>
        </span>
      </label>

      <p class="text-xs text-gray-500 mt-2">
        Default = 0 (Normal). Toggle ON = 1 (Fine).
      </p>
    </div>
  </div>

  <button
    class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-lg
           bg-blue-700 text-white font-medium hover:bg-blue-800 focus:outline-none
           focus:ring-2 focus:ring-blue-600 focus:ring-offset-2">
    Import Students
  </button>
</form>


    </div>

    {{-- Data table --}}
    <div class="mt-8 bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
            <div class="text-sm text-gray-700">
                <span class="font-medium">Students</span>
                @isset($students)
                    <span class="ml-2 text-gray-500">Total: {{ $students->total() }}</span>
                @endisset
            </div>

            {{-- Toolbar: search + per-page --}}
            <form method="GET" class="flex flex-col md:flex-row gap-3 md:items-center">
                <input type="text" name="q" value="{{ $q ?? '' }}"
                    placeholder="Search token/roll/name/batch/subject"
                    class="rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600 w-full md:w-72">
                <div class="flex items-center gap-2">
                    <label for="per_page" class="text-sm text-gray-600 whitespace-nowrap">Rows per page</label>
                    <select id="per_page" name="per_page"
                        class="rounded-lg border-gray-300 focus:border-blue-600 focus:ring-blue-600"
                        onchange="this.form.submit()">
                        @foreach ($allowedPerPage ?? [10, 20, 50, 100, 200] as $opt)
                            <option value="{{ $opt }}" @selected(($perPage ?? 10) == $opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="px-3 py-2 bg-gray-900 text-white rounded-lg hover:bg-black md:ml-2">Apply</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-700">
                    <tr>
                        <th class="text-left px-4 py-3">#</th>
                        <th class="text-left px-4 py-3">Token</th>
                        <th class="text-left px-4 py-3">Roll</th>
                        <th class="text-left px-4 py-3">Name</th>
                        <th class="text-left px-4 py-3">Batch</th>
                        <th class="text-left px-4 py-3">Subject</th>
                        <th class="text-left px-4 py-3">Year</th>
                        <th class="text-left px-4 py-3">Part</th>
                        <th class="text-left px-4 py-3">Amount</th>
                        <th class="text-left px-4 py-3">Payment ID</th>
                        <th class="text-left px-4 py-3">Fine</th>
                        <th class="text-left px-4 py-3">Status</th>

                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse(($students ?? collect()) as $idx => $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ ($students->firstItem() ?? 1) + $idx }}</td>
                            <td class="px-4 py-3 font-mono">{{ $s->token_num }}</td>
                            <td class="px-4 py-3 font-mono">{{ $s->roll_num }}</td>
                            <td class="px-4 py-3 font-medium">{{ $s->name }}</td>
                            <td class="px-4 py-3">{{ $s->batch }}</td>
                            <td class="px-4 py-3">{{ $s->subject }}</td>
                            <td class="px-4 py-3">{{ $s->year }}</td>
                            <td class="px-4 py-3">{{ $s->part }}</td>
                            <td class="px-4 py-3 font-mono text-gray-700">{{ $s->amount ?? '—' }}</td>

                            <td class="px-4 py-3 font-mono text-gray-700">{{ $s->payment_id ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($s->fine)
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Yes</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">No</span>
                                @endif
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-1 text-xs rounded-full
                {{ $s->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-6 text-center text-gray-500" colspan="11">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        @isset($students)
            <div class="px-4 py-3 border-t border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div class="text-sm text-gray-600">
                    Showing
                    <span class="font-semibold">{{ $students->firstItem() }}</span>
                    to
                    <span class="font-semibold">{{ $students->lastItem() }}</span>
                    of
                    <span class="font-semibold">{{ $students->total() }}</span>
                    results
                </div>
                <div>
                    {{ $students->onEachSide(1)->withQueryString()->links() }}
                </div>
            </div>
        @endisset
    </div>
@endsection
