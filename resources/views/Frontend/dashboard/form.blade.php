@extends('Frontend.layouts.app')

@section('title', 'Student Dashboard')

@section('content')
    <section class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Heading -->
            <div class="mb-8">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-indigo-700 text-center">
                    Student Dashboard
                </h1>
                <p class="mt-2 text-center text-sm text-slate-600">
                    Your academic details and payment status at a glance
                </p>
            </div>

            <!-- Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse ($data as $user)
                    <div class="group bg-white rounded-2xl ring-1 ring-slate-200 shadow-sm hover:shadow-lg transition">
                        <div class="p-6">
                            <!-- Header row: avatar + name + status -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold">
                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-semibold text-slate-900 leading-tight">
                                            {{ $user->name }}
                                        </h2>
                                        <div class="mt-0.5 text-xs text-slate-500">
                                            Roll: <span class="font-mono">{{ $user->roll_num }}</span>
                                        </div>
                                    </div>
                                </div>

                                <span
                                    class="px-2 py-1 text-[11px] rounded-full
                           {{ $user->payment_id ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $user->payment_id ? 'Payment Verified' : 'Payment Pending' }}
                                </span>
                            </div>

                            <!-- Details -->
                            <dl class="mt-5 grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                <div>
                                    <dt class="text-slate-500">Batch</dt>
                                    <dd class="font-medium text-slate-900">
                                        {{ $user->batch }}
                                        @if (!empty($user->faculty))
                                            <span class="text-slate-500"> ({{ $user->faculty }})</span>
                                        @endif
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-slate-500">Year / Part</dt>
                                    <dd class="font-medium text-slate-900">
                                        {{ $user->year }} / {{ $user->part }}
                                    </dd>
                                </div>

                                <div >
                                    <dt class="text-green-500">Subject</dt>
                                    <dd class="font-medium text-slate-900">
                                        {{ $user->subject }}
                                    </dd>
                                </div>

                                 <div >
                                    <dt class="text-red-500">Exam Token</dt>
                                    <dd class="font-medium text-slate-900">
                                        {{ $user->token_num }}
                                    </dd>
                                </div>
                                
                            </dl>

                            <!-- Action -->
                            <div class="mt-6">
                                @if (!$user->payment_id)
                                    <a href="{{ route('form.verify', ['token' => $user->token_num]) }}"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg
                          bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium
                          shadow-md hover:from-indigo-700 hover:to-purple-700 focus:outline-none
                          focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path
                                                d="M12 3a1 1 0 0 1 1 1v1.055a7 7 0 1 1-2 0V4a1 1 0 0 1 1-1Zm0 6a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z" />
                                        </svg>
                                        Verify Payment Rs.{{ number_format($user->amount ?? 0) }}
                                    </a>
                                @else
                                    <!-- Payment Verified (always disabled) -->
                                    <button disabled
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 text-white font-medium shadow-md cursor-not-allowed opacity-80">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                            fill="currentColor">
                                            <path d="M9 12.75 7.5 11.25 6.439 12.31 9 14.871 14.561 9.31 13.5 8.25z" />
                                        </svg>
                                        Payment Verified
                                    </button>

                                    <!-- Submit Application (only if unsubmitted) -->
                                    @if ($user->status == 'unsubmitted')
                                        <a href="{{ route('form.verify', ['token' => $user->token_num]) }}">
                                            <button type="submit"
                                                class="w-full inline-flex mt-2 items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                    fill="currentColor">
                                                    <path d="M12 4v16m8-8H4" />
                                                </svg>
                                                Submit Application
                                            </button>
                                        </a>
                                    @else
                                        <button disabled
                                            class="w-full inline-flex mt-2 items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-slate-400 text-white font-medium shadow-md cursor-not-allowed opacity-80">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                                fill="currentColor">
                                                <path d="M9 12.75 7.5 11.25 6.439 12.31 9 14.871 14.561 9.31 13.5 8.25z" />
                                            </svg>
                                            Application Submitted
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center rounded-2xl border border-dashed border-slate-300 bg-white p-10">
                            <div class="mx-auto h-12 w-12 rounded-full bg-indigo-50 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path
                                        d="M12 2a7 7 0 0 0-7 7v2.126A2 2 0 0 1 4.553 12H5v6a3 3 0 0 0 3 3h8a3 3 0 0 0 3-3v-6h.447A2 2 0 0 1 21 11.126V9a7 7 0 0 0-7-7Z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-slate-900">No student data</h3>
                            <p class="mt-1 text-sm text-slate-600">Once your details are available, they will appear here.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
