<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin Dashboard ')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="{{asset('bs-datepicker.js')}}" ></script>
    @vite('resources/css/app.css')
    <style>
        .nav-item {
            transition: all 0.3s ease;
        }
        .nav-item:hover {
            transform: translateX(4px);
        }
        .nav-item.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>

<body class="antialiased bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900">

    <!-- HEADER (fixed to screen sides) -->
    <header class="fixed inset-x-0 top-0 h-16 bg-white shadow-xl z-50 border-b-2 border-indigo-100">
        <div class="h-full px-4 md:px-6 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('asset/assets/ioe_logo.png') }}" class="h-10 w-auto transition-transform group-hover:scale-110" alt="Logo">
                <span class="text-lg md:text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">STORE IOEPC</span>
            </a>

            <div class="flex items-center gap-3">
                @if (session('admin_id'))
                    <!-- Mobile sidebar toggle -->
                    <button id="sidebarToggle"
                        class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl hover:bg-indigo-50 transition-colors"
                        title="Menu">
                        <i class="fa-solid fa-bars text-lg text-indigo-600"></i>
                    </button>

                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all hidden sm:inline-flex">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-[10px] rounded-full h-5 w-5 flex items-center justify-center font-semibold animate-pulse">3</span>
                    </button>

                    <!-- Profile -->
                    <div class="relative">
                        <button id="profileDropdownBtn"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-indigo-50 transition-all">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-semibold shadow-lg">
                                {{ strtoupper(mb_substr($admin->name, 0, 1)) }}
                            </div>
                            <span class="hidden md:inline-block font-medium text-gray-700">{{ $admin->name }}</span>
                            <i class="fa-solid fa-chevron-down text-sm text-gray-400"></i>
                        </button>

                        <div id="profileDropdownMenu"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 z-50 overflow-hidden">
                            <div class="px-4 py-3 text-sm text-gray-600 border-b bg-gradient-to-r from-indigo-50 to-purple-50">
                                <p class="font-semibold text-gray-800">Admin</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('student.login.form') }}"
                        class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:shadow-lg transform hover:scale-105 transition-all font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                @endif
            </div>
        </div>
    </header>

 @if (session('admin_id'))

<div id="sidebarBackdrop" class="hidden fixed inset-0 bg-black/60 z-40 lg:hidden"></div>

<aside id="sidebar"
class="fixed left-0 top-16 w-60 h-[calc(100vh-4rem)] 
bg-slate-950 text-white z-50
transform -translate-x-full transition-transform duration-300
overflow-y-auto lg:translate-x-0 border-r border-slate-800">

    <nav class="py-2">
        <ul class="space-y-1 px-2 text-sm">

            <li>
                <a href="{{ route('admin.dashboard') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-chart-line w-5 text-slate-300"></i>
                    <span>ड्यासबोर्ड(Dashboard)</span>
                </a>
            </li>

            <li>
                <a href="{{ route('suppliers.index') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-truck w-5 text-slate-300"></i>
                    <span>आपूर्तिकर्ता (Suppliers)</span>
                </a>
            </li>

            <li>
                <a href="{{ route('employees.index') }}" 
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-users w-5 text-slate-300"></i>
                    <span>कर्मचारी (Staff)</span>
                </a>
            </li>
{{-- 
            <li>
                <a href="{{ route('categories.index') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-th-large w-5 text-slate-300"></i>
                    <span>सामान विधा (Item Categories)</span>
                </a>
            </li> --}}

            <li>
                <a href="{{ route('product_categories.index') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-layer-group w-5 text-slate-300"></i>
                    <span>सामग्री प्रकार (Type)</span>
                </a>
            </li>

            <li>
                <a href="{{ route('brands.index') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-tag w-5 text-slate-300"></i>
                    <span>ब्रान्ड (Brand)</span>
                </a>
            </li>

            <li>
                <a href="{{ route('department.index') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-building w-5 text-slate-300"></i>
                    <span>शाखा (Department)</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.ledger') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-box w-5 text-slate-300"></i>
                    <span>सामाग्री (Products)</span>
                </a>
            </li>

            <li>
                <a href="{{ route('slips.index') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-clipboard-list w-5 text-slate-300"></i>
                    <span>माग फारम</span>
                </a>
            </li>

            <li>
                <a href="{{ route('purchases.index') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-receipt w-5 text-slate-300"></i>
                    <span>स्टोर प्राप्ति</span>
                </a>
            </li>

            <li>
                <a href="{{ route('store.out.index') }}"
                class="nav-item flex items-center gap-3 px-3 py-2 rounded-md">
                    <i class="fas fa-sign-out-alt w-5 text-slate-300"></i>
                    <span>स्टोर खर्च</span>
                </a>
            </li>

        </ul>
    </nav>
</aside>

@endif


    <!-- MAIN -->
    <main class="pt-16 lg:ml-64 min-h-screen px-4 md:px-6 py-6">
        @yield('content')
    </main>

    <script>
        // Profile dropdown
        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdownMenu');
        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', () => profileMenu.classList.add('hidden'));
        }

        // Sidebar toggle (mobile)
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const backdrop = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            if (!sidebar) return;
            sidebar.classList.remove('-translate-x-full');
            if (backdrop) backdrop.classList.remove('hidden');
        }

        function closeSidebar() {
            if (!sidebar) return;
            sidebar.classList.add('-translate-x-full');
            if (backdrop) backdrop.classList.add('hidden');
        }

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', openSidebar);
        }
        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }
        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSidebar();
        });

    </script>
    <script>
  (function () {
    function toMasked(value) {
      let d = (value || '').replace(/\D/g, '').slice(0, 8); // YYYYMMDD
      const y = d.slice(0,4), m = d.slice(4,6), day = d.slice(6,8);

      if (d.length === 0) return '';
      if (d.length < 4)   return d;
      if (d.length === 4) return y + '-';
      if (d.length < 6)   return y + '-' + m;
      if (d.length === 6) return y + '-' + m + '-';
      return y + '-' + m + '-' + day;
    }

    function attachMask(el) {
      el.addEventListener('keypress', e => { if (!/[0-9]/.test(e.key)) e.preventDefault(); });
      el.addEventListener('input', () => {
        const masked = toMasked(el.value);
        if (el.value !== masked) el.value = masked;
        const len = el.value.length;
        el.setSelectionRange(len, len);
      });
      el.addEventListener('blur', () => { el.value = toMasked(el.value); });
    }

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.date-mask').forEach(attachMask);
    });
  })();
</script>
</body>

</html>