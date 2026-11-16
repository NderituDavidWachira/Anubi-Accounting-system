<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Anubi Accounting') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-700">
    <div class="flex min-h-screen">

        <!-- Left Sidebar (No Icons, Neutral Matte Dark Slate) -->
        <aside class="w-60 bg-slate-900 border-r border-slate-800 flex flex-col justify-between shrink-0 fixed inset-y-0 left-0 z-30">
            <div>
                <!-- Header -->
                <div class="flex items-center space-x-3 px-5 py-4 border-b border-slate-800">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto">
                    <div>
                        <span class="block font-bold text-sm tracking-wider text-slate-200">ANUBI LEDGER</span>
                        <span class="block text-[11px] text-slate-400">Internal FMS</span>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <nav class="px-3 py-4 space-y-1 text-sm">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Core Operations</div>
                            
                            <a href="{{ route('admin.dashboard') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Executive Dashboard
                            </a>

                            <a href="{{ route('vouchers.index') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('vouchers.index') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Voucher Register
                            </a>

                            <a href="{{ route('admin.approvals') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('admin.approvals') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Approvals Desk
                            </a>

                            <div class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Statements</div>

                            <a href="{{ route('reports.general-ledger') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('reports.general-ledger') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                General Ledger
                            </a>

                            <a href="{{ route('reports.trial-balance') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('reports.trial-balance') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Trial Balance
                            </a>

                            <a href="{{ route('reports.profit-loss') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('reports.profit-loss') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Profit &amp; Loss (P&amp;L)
                            </a>

                            <a href="{{ route('reports.balance-sheet') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('reports.balance-sheet') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Balance Sheet
                            </a>

                            <a href="{{ route('reports.hub') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('reports.hub') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Reports Center (PDF)
                            </a>

                            <a href="{{ route('admin.accounts') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('admin.accounts') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Chart of Accounts
                            </a>
                        @else
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">Workstation</div>

                            <a href="{{ route('vouchers.index') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('vouchers.index') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                My Vouchers
                            </a>

                            <a href="{{ route('vouchers.create') }}" 
                               class="block px-3 py-2 rounded text-xs font-medium transition {{ request()->routeIs('vouchers.create') ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                New Voucher Entry
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>

            <!-- User Meta & Logout -->
            @auth
                <div class="p-3 border-t border-slate-800 bg-slate-950/30">
                    <div class="mb-2">
                        <div class="text-xs font-medium text-slate-200 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-slate-400 uppercase tracking-wider">{{ auth()->user()->role }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs py-1 rounded transition text-center">
                            Sign Out
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <!-- Main Content Viewport -->
        <div class="flex-1 ml-60 flex flex-col min-h-screen">
            <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
                @if(session('success'))
                    <div class="mb-4 bg-slate-200 border-l-4 border-slate-700 text-slate-800 px-4 py-2.5 text-xs rounded-r">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 bg-red-100 border-l-4 border-red-700 text-red-800 px-4 py-2.5 text-xs rounded-r">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>
</body>
</html>