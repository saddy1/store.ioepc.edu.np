<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Portal')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

</head>

<body class="antialiased bg-gray-50 text-gray-900">

    <!-- HEADER (fixed to screen sides) -->
    <header class="fixed inset-x-0 top-0 h-16 bg-white shadow-lg z-50">
        <div class="h-full px-4 md:px-6 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('asset/assets/ioe_logo.png') }}" class="h-10 w-auto" alt="Logo">
                <span class="text-lg md:text-xl font-bold text-blue-900">IOE Purwanchal Campus</span>
            </a>

            <div class="flex items-center gap-2">

                <a href="{{ route('student.login.form') }}"
                    class="px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition">
                    Login
                </a>

            </div>
        </div>
    </header>



    <div class="min-h-screen flex flex-col">
        <!-- Header -->


        <!-- Main Content -->
        <main class="flex-grow flex items-center justify-center bg-gradient-to-r from-blue-50 to-slate-100">
            <div class="text-center px-6">
                <h1 class="text-3xl sm:text-5xl font-bold text-blue-900 mb-4">
                    Welcome to Student Portal
                </h1>
                <p class="text-gray-600 text-lg sm:text-xl mb-6">
                    Submit online for Exam Registration at IOE Purwanchal Campus
                </p>
                <a href="{{ route('student.login.form') }}"
                    class="px-6 py-3 bg-blue-700 text-white rounded-xl shadow hover:bg-blue-800 transition">
                    Get Started
                </a>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white text-center py-4">
            <p class="text-sm">© {{ date('Y') }} IOE Purwanchal Campus. All Rights Reserved.</p>
        </footer>
    </div>


</body>

</html>

