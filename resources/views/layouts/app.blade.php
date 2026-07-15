<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP Inventory Management System')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navyBlue: '#1E3A8A',
                        emeraldGreen: '#10B981',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-slate-800 font-sans min-h-screen flex flex-col md:flex-row relative">

    <!-- Left Sidebar Layout -->
    <aside class="w-full md:w-64 bg-navyBlue text-white flex flex-col justify-between md:sticky md:top-0 md:h-screen shadow-xl z-20 shrink-0">
        <div class="flex flex-col">
            <!-- App Branding -->
            <div class="p-6 border-b border-white/10">
                <h1 class="text-lg font-bold tracking-wide text-white">ERP Inventory</h1>
                <p class="text-[10px] text-blue-200 uppercase tracking-widest mt-0.5">Management System</p>
            </div>
            
            <!-- Sidebar Navigation Links -->
            <nav class="p-4 flex flex-col gap-1.5 overflow-y-auto">
                <a href="#" onclick="clearAllFilters()" class="px-4 py-2.5 text-xs font-semibold text-blue-100 rounded-lg hover:text-white hover:bg-white/10 transition-all">Dashboard</a>
                <a href="#" class="px-4 py-2.5 text-xs font-semibold text-blue-100 rounded-lg hover:text-white hover:bg-white/10 transition-all">Inventory Items</a>
                <a href="#" class="px-4 py-2.5 text-xs font-semibold text-blue-100 rounded-lg hover:text-white hover:bg-white/10 transition-all">Stock Movements</a>
                <a href="#" class="px-4 py-2.5 text-xs font-semibold text-blue-100 rounded-lg hover:text-white hover:bg-white/10 transition-all">Warehouse Layout</a>
                <a href="{{ route('alerts.index') }}" class="px-4 py-2.5 text-xs font-bold text-white bg-white/10 border-l-4 border-emeraldGreen rounded-r-lg transition-all">Alerts & Reorders</a>
                <a href="#" class="px-4 py-2.5 text-xs font-semibold text-blue-100 rounded-lg hover:text-white hover:bg-white/10 transition-all">Returns & QC</a>
                <a href="#" class="px-4 py-2.5 text-xs font-semibold text-blue-100 rounded-lg hover:text-white hover:bg-white/10 transition-all">Product Bundling</a>
            </nav>
        </div>

        <!-- Bottom Half: User Profile & Actions -->
        <div class="relative p-4 border-t border-white/10 bg-black/10">
            <button onclick="toggleAccountMenu()" class="w-full flex items-center justify-between text-sm font-semibold hover:bg-white/5 rounded-lg py-1.5 px-2 transition-colors">
                <span class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0"
                          style="background:{{ $actingAdmin->avatar_color ?? '#1a2f7a' }}">
                        {{ $actingAdmin ? substr($actingAdmin->name, -1) : '?' }}
                    </span>
                    <span class="text-xs text-left">
                        <span class="block text-blue-200/70 font-normal text-[10px] uppercase tracking-wide">Acting as</span>
                        <span class="block text-white">{{ $actingAdmin->name ?? 'Guest' }}</span>
                    </span>
                </span>
                <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <!-- Dropdown: switch acting admin, no password required -->
            <div id="account-menu" class="hidden absolute bottom-full left-4 right-4 mb-2 bg-white rounded-lg shadow-xl border border-slate-200 py-2 z-30">
                <p class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wide">Switch Account</p>
                @foreach ($allAdmins ?? [] as $admin)
                    <form action="{{ route('account.switch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $admin->id }}">
                        <button type="submit"
                            class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-slate-50 text-left {{ isset($actingAdmin) && $actingAdmin->id === $admin->id ? 'bg-slate-50' : '' }}">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white shrink-0"
                                  style="background:{{ $admin->avatar_color }}">{{ substr($admin->name, -1) }}</span>
                            <span class="flex-1">
                                <span class="block font-semibold text-slate-700">{{ $admin->name }}</span>
                                <span class="block text-slate-400 text-[10px]">{{ $admin->email }}</span>
                            </span>
                            @if (isset($actingAdmin) && $actingAdmin->id === $admin->id)
                                <svg class="w-3.5 h-3.5 text-emeraldGreen" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </aside>

    <!-- Main Workspace Content Container -->
    <main class="flex-1 p-6 md:p-8 space-y-6 max-w-7xl overflow-x-hidden">
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-lg px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        function toggleAccountMenu() {
            document.getElementById('account-menu').classList.toggle('hidden');
        }
        // Close the dropdown if you click anywhere else on the page
        document.addEventListener('click', function (e) {
            const menu = document.getElementById('account-menu');
            const trigger = e.target.closest('button[onclick="toggleAccountMenu()"]');
            if (!trigger && menu && !menu.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>

</body>
</html>