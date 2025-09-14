@extends('Backend.layouts.app')

@section('content')
<div class="app-content content px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between items-center mb-6">
        <h3 class="text-2xl font-bold text-gray-800">Upload Bank Details</h3>
        <button id="addStudentBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow transition">
            Add Bank Data
        </button>
    </div>

    <!-- Alerts -->
    <div class="flex justify-center mb-6">
        <div class="w-full md:w-2/3">
            @if(session('error'))
                <div class="bg-red-100 text-red-700 p-4 rounded-md mb-4 shadow">{{ session('error') }}</div>
            @endif

            @if(session('success'))
                <div class="bg-green-100 text-green-700 p-4 rounded-md mb-4 shadow">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 text-red-700 p-4 rounded-md mb-4 shadow">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Upload Form -->
    <div id="uploadFormContainer" class="hidden mb-6">
        <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md">
            @csrf
            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <div class="flex-1">
                    <label for="file" class="block text-gray-700 font-semibold mb-2">Choose XLS/XLSX File</label>
                    <input type="file" name="file" id="file" accept=".xls,.xlsx" required
                        class="w-full border border-gray-300 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md shadow transition">
                        Import
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Transaction Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="py-3 px-4 text-left">Date</th>
                    <th class="py-3 px-4 text-left">Transaction ID</th>
                    <th class="py-3 px-4 text-left">Name</th>
                    <th class="py-3 px-4 text-left">Amount</th>
                    <th class="py-3 px-4 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($transactionData as $transaction)
                    <tr class="{{ $transaction->status == 2 ? 'bg-green-50' : 'bg-white' }}">
                        <td class="py-2 px-4">{{ $transaction->date }}</td>
                        <td class="py-2 px-4 font-medium">{{ $transaction->txn_id }}</td>
                        <td class="py-2 px-4">{{ $transaction->name }}</td>
                        <td class="py-2 px-4">{{ $transaction->amount }}</td>
                        <td class="py-2 px-4">
                            <span class="px-2 py-1 rounded-full text-white font-semibold {{ $transaction->status == 1 ? 'bg-yellow-500' : 'bg-green-500' }}">
                                {{ $transaction->status == 1 ? 'Unused' : 'Used' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">No transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('addStudentBtn').addEventListener('click', function() {
        const uploadFormContainer = document.getElementById('uploadFormContainer');
        uploadFormContainer.classList.toggle('hidden');
    });
</script>
@endsection
