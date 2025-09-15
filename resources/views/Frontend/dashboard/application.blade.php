@extends('Frontend.layouts.app')

@section('title', 'Student Dashboard')

@section('content')
    <section class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (!$data->payment_id)
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
                                        <input type="text" id="txn_number" name="txn_number"
                                            placeholder="Enter TXN Number" required
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
            @elseif ($data->status == 'unsubmitted')
                {{-- Payment verified alert (unchanged) --}}
                <div class="max-w-3xl mx-auto mt-6">
                    <div class="text-center rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3">
                        <h5 class="font-semibold">Your Payment is Verified Successfully!</h5>
                        <div class="mt-1 text-sm"> Thank you for your payment of Rs. {{ $data->amount }}. Your transaction
                            ID is <strong>{{ $data->payment_id }}</strong>. </div>
                    </div>
                </div>

                {{-- Voucher upload --}}
                <div class="max-w-3xl mx-auto mt-8">
                    <div class="flex items-center gap-3">
                        <h3 class="text-lg font-bold text-slate-900">Voucher Details</h3>
                        <div class="flex-1 h-px bg-gradient-to-r from-red-500/60 to-transparent"></div>
                    </div>

                    {{-- Success flash --}}
                    @if (session('success'))
                        <div class="mt-4 rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Global error summary --}}
                    @if ($errors->any())
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 text-red-800">
                            <div class="px-4 py-3 font-semibold">Please fix the following:</div>
                            <ul class="px-6 pb-4 list-disc text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('vouchers.store') }}" method="POST" enctype="multipart/form-data"
                        class="mt-4">
                        @csrf
                        <input type="hidden" name="token_num" value="{{ $data->token_num }}">


                        <div class="rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                {{-- Payment Voucher (PDF only, < 2MB) --}}
                                <div>
                                    <label for="payment_voucher" class="block text-sm font-medium text-slate-700 mb-1">
                                        Upload Payment Voucher (PDF) <span class="text-red-600">*</span>
                                    </label>

                                    <input type="file" id="payment_voucher" name="payment_voucher" required
                                        accept=".pdf,application/pdf" onchange="validatePDF(this)"
                                        aria-describedby="payment_voucher_error"
                                        aria-invalid="@error('payment_voucher') true @else false @enderror"
                                        class="block w-full rounded-lg border text-sm text-slate-900
                   file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2
                   file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100
                   focus:outline-none
                   @error('payment_voucher')
                     border-red-500 focus:ring-red-500
                   @else
                     border-slate-300 focus:border-indigo-600 focus:ring-indigo-600
                   @enderror" />
                                    @error('payment_voucher')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p id="payment_voucher_error" class="mt-1 text-xs text-red-600"></p>
                                    <p class="mt-1 text-xs text-slate-500">PDF only, up to 2 MB.</p>
                                </div>

                                {{-- Confirmation Slip (PDF only, < 2MB) --}}
                                <div>
                                    <label for="token_slip" class="block text-sm font-medium text-slate-700 mb-1">
                                        Confirmation Slip (PDF) <span class="text-red-600">*</span>
                                    </label>

                                    <input type="file" id="token_slip" name="token_slip" required
                                        accept=".pdf,application/pdf" onchange="validatePDF(this)"
                                        aria-describedby="token_slip_error"
                                        aria-invalid="@error('token_slip') true @else false @enderror"
                                        class="block w-full rounded-lg border text-sm text-slate-900
                   file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2
                   file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100
                   focus:outline-none
                   @error('token_slip')
                     border-red-500 focus:ring-red-500
                   @else
                     border-slate-300 focus:border-indigo-600 focus:ring-indigo-600
                   @enderror" />
                                    @error('token_slip')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p id="token_slip_error" class="mt-1 text-xs text-red-600"></p>
                                    <p class="mt-1 text-xs text-slate-500">PDF only, up to 2 MB.</p>
                                </div>
                            </div>

                            <div class="mt-6 text-center">
                                <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5
                 rounded-lg bg-indigo-600 text-white font-medium shadow
                 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <script>
                    // Client-side guard: PDF only, <= 2MB
                    function validatePDF(input) {
                        const err = document.getElementById(input.id + '_error');
                        if (err) err.textContent = '';

                        const file = input.files && input.files[0];
                        if (!file) return;

                        if (file.type !== 'application/pdf') {
                            if (err) err.textContent = 'Only PDF files are allowed.';
                            input.value = '';
                            return;
                        }
                        const maxBytes = 2 * 1024 * 1024; // 2 MB
                        if (file.size > maxBytes) {
                            if (err) err.textContent = 'File too large. Max size is 2 MB.';
                            input.value = '';
                        }
                    }
                </script>
            @elseif ($data->status == 'submitted')
                {{-- Payment verified and application submitted alert (unchanged) --}}
                <div class="max-w-3xl mx-auto mt-6">
                    <div class="text-center rounded-lg border border-blue-200 bg-blue-50 text-blue-700 px-4 py-3">
                        <h5 class="font-semibold">Your Application is Submitted Successfully!</h5>
                        <div class="mt-1 text-sm"> Your application has been received and is under review. We will
                            notify you of any updates via email. </div>
                    </div>
                </div>

            @endif
        </div>
    </section>
@endsection
