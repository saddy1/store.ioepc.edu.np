@extends('Frontend.layouts.app')

@section('title', 'Student Dashboard')

@section('content')
    <section class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Heading -->
            <div class="mb-8">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-indigo-700 text-center">
                    Payment Dashboard
                </h1>
            </div>


            <form action="{{ route('verify.payment') }}" method="POST" id="txnForm">
                @csrf
                <input type="hidden" name="token_num" value="{{ $data->token_num }}">

                <!-- Note -->
                <div class="max-w-3xl mx-auto mt-6">
                    <div class="text-center rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3">
                        <h5 class="font-semibold">Verify Your Payment For Rs. {{ $data->amount }}</h5>
                        <div class="mt-1 text-sm">
                            Note: Please ensure you provide correct payment details.
                        </div>
                    </div>

                    <!-- Card -->
                    <div class="max-w-3xl mx-auto mt-6">
                        <div class="bg-white rounded-2xl shadow-lg ring-1 ring-gray-200 p-6">

                            <!-- Header (Logo + Title) -->
                            <div class="flex items-center justify-center gap-4">
                                <img src="https://www.nicasiabank.com/_next/image/?url=https%3A%2F%2Fcms.nicasiabank.com%2Fframework%2Fuploads%2FLogo%2FNIC-logo%20-%20footer.png&w=256&q=75"
                                    alt="NIC Asia Logo" class="h-12 w-auto" />
                                <h3 class="text-xl font-bold text-red-600">Payment Detail</h3>
                            </div>

                            <!-- Success Message -->
                            @if (session('success'))
                                <div class="max-w-3xl mx-auto mt-4">
                                    <div
                                        class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 shadow-sm">
                                        ✅ {{ session('success') }}
                                    </div>
                                </div>
                            @endif

                            <!-- Error Messages -->
                            @if ($errors->any())
                                <div class="max-w-3xl mx-auto mt-4">
                                    <div
                                        class="rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 shadow-sm">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>⚠️ {{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif


                            <!-- Fields -->
                            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- TXN Number -->
                                <div>
                                    <label for="txn_number" class="block text-sm font-medium text-gray-700 mb-1">TXN
                                        Number</label>
                                    <input type="text" id="txn_number" name="txn_number" placeholder="Enter TXN Number"
                                        required
                                        class="block w-full p-2 border border-red-300 focus:border-indigo-600 focus:ring-indigo-600" />
                                    @error('txn_number')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- TXN Date -->
                                <div>
                                    <label for="txn_date" class="block text-sm font-medium text-gray-700 mb-1">TXN
                                        Date</label>
                                    <input type="date" id="txn_date" name="txn_date" required
                                        class="block w-full p-2 border border-gray-300 focus:border-indigo-600 focus:ring-indigo-600" />
                                    @error('txn_date')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="mt-8 text-center">
                                <button type="submit"
                                    class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-green-600 text-white font-semibold shadow-lg hover:bg-green-700 hover:scale-105 transform transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2">
                                    ✅ Verify Payment
                                </button>
                            </div>
                        </div>
                    </div>
            </form>
        </div>
    </section>
@endsection
