@extends('layouts.admin')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-7xl mx-auto" id="network-command-center">

        {{-- ── Header ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold text-white tracking-tight">Network Management</h1>
                    <span
                        class="bg-red-500/10 border border-red-500/20 text-red-500 text-xs px-2.5 py-1 rounded-md font-bold tracking-wide uppercase shadow-sm flex items-center shadow-[0_0_10px_rgba(239,68,68,0.2)]">
                        <i class="fas fa-lock mr-2"></i> Super Admin Only
                    </span>
                </div>
                <p class="text-slate-400 text-sm mt-1">Live command center — pulse auto-checks every 30s.</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Live Pulse Indicator -->
                <div id="pulse-status"
                    class="flex items-center gap-2 text-xs text-slate-400 font-mono bg-slate-900/60 px-3 py-2 rounded-lg border border-slate-700/50">
                    <span
                        class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.8)] animate-pulse"></span>
                    <span id="pulse-label">Live</span>
                </div>
                <!-- Sync All Button -->
                <button id="sync-all-btn"
                    class="bg-blue-900/40 hover:bg-blue-600 text-blue-400 hover:text-white border border-blue-700 hover:border-blue-400 text-sm font-semibold py-2.5 px-5 rounded-xl transition-all shadow-[0_0_12px_rgba(59,130,246,0.2)] hover:shadow-[0_0_20px_rgba(59,130,246,0.5)] flex items-center gap-2">
                    <i class="fas fa-rotate" id="sync-icon"></i> Sync All Platforms
                </button>
                <button
                    class="bg-brand-red hover:bg-brand-red-hover text-white text-sm font-semibold py-2.5 px-6 rounded-xl transition-all shadow-[0_0_15px_rgba(227,30,36,0.3)] hover:shadow-[0_0_20px_rgba(227,30,36,0.5)]">
                    <i class="fas fa-plus mr-2"></i> Deploy New Site
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
            <div class="xl:col-span-3 space-y-8">

                {{-- ── Top Stats ──────────────────────────────────────────────── --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Network Revenue (Clickable for Deep Dive) -->
                    <div onclick="openNetworkDeepDive()" id="total-revenue-card"
                        class="exec-card p-6 flex flex-col justify-between group cursor-pointer transition-all duration-300 hover:border-emerald-500/50 hover:shadow-[0_4px_30px_rgba(0,0,0,0.5)] bg-surface-card backdrop-blur-xl relative overflow-hidden">
                        
                        <!-- Hover hint -->
                        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/0 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                             <span class="text-[9px] uppercase tracking-widest text-emerald-400 font-bold bg-emerald-500/10 px-2 py-0.5 rounded-full ring-1 ring-emerald-500/30">Deep Dive <i class="fas fa-arrow-right ml-1"></i></span>
                        </div>

                        <div class="flex items-start justify-between mb-4 relative z-10">
                            <div>
                                <p class="text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Total Network
                                    Revenue</p>
                                <h3
                                    class="text-3xl font-bold text-white group-hover:text-emerald-400 transition-colors drop-shadow-md">
                                    RM {{ number_format($stats['total_revenue']) }}
                                </h3>
                                <p class="text-[10px] text-slate-500 mt-1 font-mono">Across 3 live databases</p>
                            </div>
                            <div
                                class="w-10 h-10 rounded-full bg-slate-800/80 border border-white/5 flex items-center justify-center text-emerald-500 shadow-inner">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="relative w-full mt-2 border-t border-slate-700/50 pt-3 space-y-1.5">
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="text-slate-500 font-mono">BrandThirty HQ</span>
                                <span class="text-white font-semibold font-mono">RM
                                    {{ number_format($stats['revenue_brandthirty']) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="text-orange-400 font-mono">Prism Media Hub</span>
                                <span class="text-white font-semibold font-mono">RM
                                    {{ number_format($stats['revenue_prism']) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="text-sky-400 font-mono">Alphafin</span>
                                <span class="text-white font-semibold font-mono">RM
                                    {{ number_format($stats['revenue_alphafin']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Active Platforms -->
                    <div
                        class="exec-card p-6 flex items-start justify-between group cursor-default transition-all duration-300 hover:border-slate-500/50 hover:shadow-[0_4px_30px_rgba(0,0,0,0.5)] bg-surface-card backdrop-blur-xl">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Active Platforms
                            </p>
                            <div class="flex items-end gap-2">
                                <h3 id="stat-active-sites" class="text-3xl font-bold text-white drop-shadow-md">
                                    {{ $stats['active_sites'] }}</h3>
                                <span class="text-sm font-medium text-slate-500 relative -top-1">/ {{ count($sites) + 1 }}
                                    total</span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-2 font-mono">DB connections:
                                {{ implode(', ', array_map(fn($d) => $d === 'pgsql' ? 'HQ' : ucfirst($d), array_keys(array_flip(['pgsql', 'prism', 'alphafin'])))) }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-full bg-slate-800/80 border border-white/5 flex items-center justify-center text-blue-400 shadow-inner">
                            <i class="fas fa-server text-xl"></i>
                        </div>
                    </div>

                    <!-- Network Receivables -->
                    <div
                        class="exec-card p-6 flex items-start justify-between group cursor-default transition-all duration-300 hover:border-slate-500/50 hover:shadow-[0_4px_30px_rgba(0,0,0,0.5)] bg-surface-card backdrop-blur-xl">
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-500 tracking-wider mb-2">Network
                                Receivables</p>
                            <h3
                                class="text-3xl font-bold text-white group-hover:text-amber-400 transition-colors drop-shadow-md">
                                RM {{ number_format($stats['pending_payments']) }}
                            </h3>
                            <p class="text-[10px] text-slate-500 mt-2 font-mono">Pending across all nodes</p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-full bg-slate-800/80 border border-white/5 flex items-center justify-center text-amber-500 shadow-inner">
                            <i class="fas fa-file-invoice-dollar text-xl"></i>
                        </div>
                    </div>
                </div>

                {{-- ── Registry Table ─────────────────────────────────────────── --}}
                <div class="exec-card overflow-hidden bg-surface-card backdrop-blur-xl shadow-xl">
                    <div class="px-6 py-5 border-b border-white/5 flex items-center justify-between bg-slate-900/40">
                        <h2 class="text-lg font-bold text-white">The Registry
                            <span class="text-sm font-normal text-slate-400 ml-2 border-l border-slate-700 pl-3">Sub-site
                                Index</span>
                        </h2>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm"></i>
                            <input type="text" id="registry-search" placeholder="Search domain or IP..."
                                class="pl-9 pr-4 py-2 bg-slate-950/50 border border-slate-700/50 rounded-lg text-sm text-white focus:outline-none focus:border-brand-red transition-colors w-64 placeholder-slate-600 focus:shadow-[0_0_10px_rgba(227,30,36,0.2)]">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left" id="registry-table">
                            <thead>
                                <tr
                                    class="bg-slate-950/30 border-b border-white/5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                    <th class="px-6 py-4">Site Name & URL</th>
                                    <th class="px-6 py-4">Hosting & Pulse</th>
                                    <th class="px-6 py-4">Status</th>
                                    <th class="px-6 py-4">Design Preset</th>
                                    <th class="px-6 py-4">Active Modules</th>
                                    <th class="px-6 py-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($sites as $site)
                                    @php
                                        $glowColor = $site->theme === 'Modern AI-Authority' ? 'rgba(249,115,22,0.4)' : 'rgba(56,189,248,0.4)';
                                        $borderColor = $site->theme === 'Modern AI-Authority' ? '#f97316' : '#38bdf8';
                                    @endphp
                                    <tr class="hover:bg-slate-800/40 transition-all duration-300 group relative registry-row"
                                        data-domain="{{ $site->domain }}" data-ip="{{ $site->ip }}"
                                        style="--hover-glow:{{ $glowColor }};--border-color:{{ $borderColor }}">
                                        <div
                                            class="absolute left-0 top-0 bottom-0 w-1 bg-[var(--border-color)] opacity-0 group-hover:opacity-100 transition-opacity shadow-[0_0_15px_var(--hover-glow)] z-10">
                                        </div>

                                        {{-- Site Info --}}
                                        <td class="px-6 py-5 align-top relative z-0">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 rounded-lg bg-slate-900 border border-slate-700/50 flex items-center justify-center text-slate-300 font-bold shadow-md transition-colors group-hover:border-[var(--border-color)] group-hover:text-white group-hover:shadow-[0_0_10px_var(--hover-glow)]">
                                                    {{ substr($site->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-white text-sm tracking-wide">{{ $site->name }}
                                                    </div>
                                                    <a href="https://{{ $site->domain }}" target="_blank"
                                                        class="text-xs text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1 mt-0.5">
                                                        {{ $site->domain }} <i
                                                            class="fas fa-external-link-alt text-[9px] opacity-70"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- IP / Version --}}
                                        <td class="px-6 py-5 align-top">
                                            <div class="text-sm text-slate-300 font-mono text-xs site-ip">{{ $site->ip ?? '—' }}
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                                <i class="fas fa-code-branch opacity-50"></i> {{ $site->version }}
                                            </div>
                                        </td>

                                        {{-- Status + Ping --}}
                                        <td class="px-6 py-5 align-top" data-site-key="{{ $site->db_key }}">
                                            @if($site->status === 'Online')
                                                <div class="flex items-center gap-3">
                                                    <div class="site-status-wrap">
                                                        <span
                                                            class="site-status-badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/50 shadow-[0_0_10px_rgba(16,185,129,0.3)] text-xs px-2.5 py-1 rounded-full font-bold inline-flex items-center gap-1.5 tracking-wide">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 relative">
                                                                <span
                                                                    class="absolute inset-0 rounded-full bg-emerald-400 animate-[ping_1.5s_ease-in-out_infinite] opacity-75"></span>
                                                            </span>
                                                            ONLINE
                                                        </span>
                                                        <span
                                                            class="site-ping block text-[10px] {{ $site->ping ? 'text-emerald-500/70' : 'text-slate-600' }} font-mono mt-1 ml-1">{{ $site->ping ? $site->ping . 'ms' : '— ms' }}</span>
                                                    </div>
                                                    <div class="flex items-end h-5 gap-0.5 opacity-80 mt-1">
                                                        <div class="w-1 bg-emerald-500/80 rounded-t animate-[pulse-bar_1s_ease-in-out_infinite_alternate]"
                                                            style="height:40%"></div>
                                                        <div class="w-1 bg-emerald-500/80 rounded-t animate-[pulse-bar_1.2s_ease-in-out_infinite_alternate]"
                                                            style="height:70%"></div>
                                                        <div class="w-1 bg-emerald-500/80 rounded-t animate-[pulse-bar_0.8s_ease-in-out_infinite_alternate]"
                                                            style="height:30%"></div>
                                                        <div class="w-1 bg-emerald-500/80 rounded-t animate-[pulse-bar_1.5s_ease-in-out_infinite_alternate]"
                                                            style="height:90%"></div>
                                                        <div class="w-1 bg-emerald-500/80 rounded-t animate-[pulse-bar_0.9s_ease-in-out_infinite_alternate]"
                                                            style="height:50%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-3">
                                                    <div class="site-status-wrap">
                                                        <span
                                                            class="site-status-badge bg-slate-900/80 text-slate-400 border border-slate-700/80 text-xs px-2.5 py-1 rounded-full font-bold shadow-sm inline-flex items-center gap-1.5 tracking-wide">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> OFFLINE
                                                        </span>
                                                        <span
                                                            class="site-ping block text-[10px] text-slate-600 font-mono mt-1 ml-1">—
                                                            ms</span>
                                                    </div>
                                                    <div class="flex items-end h-5 gap-0.5 opacity-30 mt-1">
                                                        <div class="w-1 bg-slate-600 rounded-t" style="height:10%"></div>
                                                        <div class="w-1 bg-slate-600 rounded-t" style="height:10%"></div>
                                                        <div class="w-1 bg-slate-600 rounded-t" style="height:10%"></div>
                                                        <div class="w-1 bg-slate-600 rounded-t" style="height:10%"></div>
                                                        <div class="w-1 bg-slate-600 rounded-t" style="height:10%"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>

                                        {{-- Theme Badge --}}
                                        <td class="px-6 py-5 align-top text-sm font-medium">
                                            @if($site->theme === 'Modern AI-Authority')
                                                <span
                                                    class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider bg-orange-950/40 border border-orange-500/30 text-orange-400 rounded shadow-[0_0_8px_rgba(249,115,22,0.15)] inline-block">{{ $site->theme }}</span>
                                            @else
                                                <span
                                                    class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider bg-sky-950/40 border border-sky-500/30 text-sky-400 rounded shadow-[0_0_8px_rgba(56,189,248,0.15)] inline-block">{{ $site->theme }}</span>
                                            @endif
                                        </td>

                                        {{-- Module Toggles --}}
                                        <td class="px-6 py-5 align-top">
                                            <div class="space-y-2.5">
                                                @foreach(['e_invoice' => 'e-Invoice', 'offline_sales' => 'Offline Sales', 'reports' => 'Reports'] as $moduleKey => $moduleLabel)
                                                    <label
                                                        class="flex items-center justify-between cursor-pointer group/toggle w-32">
                                                        <span
                                                            class="text-[11px] font-semibold uppercase text-slate-400 group-hover/toggle:text-white transition-colors">{{ $moduleLabel }}</span>
                                                        <div class="relative">
                                                            <input type="checkbox" class="sr-only peer module-toggle"
                                                                data-site-key="{{ $site->db_key }}" data-module="{{ $moduleKey }}"
                                                                {{ $site->features[$moduleKey] ? 'checked' : '' }}>
                                                            <div
                                                                class="w-7 h-3.5 bg-slate-800 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[1px] after:left-[1px] after:bg-slate-300 peer-checked:after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-brand-red border border-slate-700 peer-checked:border-brand-red shadow-inner">
                                                            </div>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-5 align-middle text-right space-x-1 whitespace-nowrap">
                                            <button
                                                class="bg-slate-900/80 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-500 rounded p-1.5 px-2 text-[11px] transition-colors"
                                                title="Settings">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <button
                                                class="bg-slate-900/80 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-500 rounded p-1.5 px-2 text-[11px] transition-colors"
                                                title="Terminal">
                                                <i class="fas fa-terminal"></i>
                                            </button>
                                            {{-- God-Mode Login Button --}}
                                            <button
                                                class="god-mode-btn bg-indigo-900/40 hover:bg-indigo-600 text-indigo-400 hover:text-white border border-indigo-700 hover:border-indigo-400 rounded p-1.5 px-3 text-[11px] font-semibold transition-all shadow-[0_0_10px_rgba(79,70,229,0.2)] hover:shadow-[0_0_15px_rgba(79,70,229,0.5)] ml-2"
                                                data-site-key="{{ $site->db_key }}" data-site-name="{{ $site->name }}"
                                                title="God-Mode Login (60-second signed link)">
                                                <i class="fas fa-bolt text-amber-300 mr-1"></i> Admin
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-12 text-center text-slate-500 font-medium bg-slate-900/40">
                                            <i class="fas fa-satellite text-4xl mb-3 opacity-30 block"></i>
                                            No network sites found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="bg-slate-900/60 px-6 py-4 border-t border-white/5 flex items-center justify-between text-[11px] uppercase tracking-wider text-slate-500 font-semibold">
                        <div>Showing {{ count($sites) }} registry entries</div>
                        <div class="flex items-center gap-4">
                            <span class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.8)]">
                                </div>
                                Multi-DB Synchronized
                            </span>
                            <span class="text-slate-600 font-mono" id="last-pulse-time"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Right Sidebar: Live Activity Feed ──────────────────────────── --}}
            <div class="xl:col-span-1">
                <div
                    class="exec-card h-full bg-surface-card backdrop-blur-xl shadow-xl flex flex-col relative overflow-hidden">
                    <div
                        class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-brand-red via-purple-500 to-blue-500 opacity-50">
                    </div>

                    <div class="px-5 py-4 border-b border-white/5 bg-slate-900/40 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <i class="fas fa-rss text-brand-red text-[10px] animate-pulse"></i> Live Activity Feed
                        </h2>
                        <span class="text-[10px] text-slate-500 font-mono">Last 5 events</span>
                    </div>

                    <div class="p-5 flex-1 overflow-y-auto space-y-4 max-h-[600px] fancy-scrollbar" id="activity-feed">
                        @forelse($activities as $activity)
                            @php $icon = $activity->icon_data; @endphp
                            <div class="flex gap-3 relative group activity-item">
                                <div class="w-px absolute top-6 bottom-[-20px] left-3 bg-slate-700/50 group-last:hidden"></div>
                                <div
                                    class="relative z-10 w-6 h-6 rounded-full {{ $icon['bg'] }} border flex items-center justify-center shrink-0 mt-1">
                                    <i class="fas {{ $icon['icon'] }} {{ $icon['color'] }} text-[9px]"></i>
                                </div>
                                <div>
                                    <p class="text-[13px] text-slate-300 leading-tight">
                                        <span class="font-bold {{ $activity->site_color }}">{{ $activity->site_name }}:</span>
                                        {{ $activity->description }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span
                                            class="text-[10px] text-slate-500 font-mono">{{ $activity->created_at->diffForHumans() }}</span>
                                        @if($activity->duration_ms)
                                            <span class="text-[9px] text-slate-600 font-mono">·
                                                {{ $activity->duration_ms }}ms</span>
                                        @endif
                                        @if($activity->status === 'failed')
                                            <span class="text-[9px] text-red-400 font-bold uppercase">FAILED</span>
                                        @elseif($activity->status === 'queued')
                                            <span class="text-[9px] text-amber-400 font-bold uppercase">QUEUED</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-slate-600 text-sm py-8">
                                <i class="fas fa-circle-notch fa-spin text-xl mb-3 block opacity-30"></i>
                                No activity yet. Try syncing or toggling a module.
                            </div>
                        @endforelse
                    </div>

                    {{-- Toast Notification (inline in sidebar) --}}
                    <div id="toggle-toast"
                        class="mx-3 mb-2 hidden px-4 py-3 rounded-lg text-xs font-semibold border transition-all duration-300">
                    </div>

                    {{-- God-Mode Countdown + Link Modal --}}
                    <div id="godmode-modal"
                        class="hidden mx-3 mb-3 p-3 rounded-lg bg-indigo-950/60 border border-indigo-600/40 shadow-[0_0_15px_rgba(79,70,229,0.3)]">
                        <p
                            class="text-[11px] text-indigo-300 font-semibold mb-2 uppercase tracking-wide flex items-center gap-1.5">
                            <i class="fas fa-bolt text-amber-400"></i> God-Mode Link Generated
                        </p>
                        <p class="text-[10px] text-slate-400 mb-2">Valid for <span id="godmode-countdown"
                                class="text-amber-400 font-bold font-mono">60</span>s. Click to open.</p>
                        <a id="godmode-link" href="#" target="_blank"
                            class="block w-full text-center bg-indigo-700 hover:bg-indigo-500 text-white text-xs font-bold py-2 px-3 rounded transition-colors truncate">
                            Enter Admin Panel →
                        </a>
                    </div>

                    <div class="p-3 bg-slate-900/50 border-t border-white/5 text-center mt-auto">
                        <button
                            class="text-xs font-semibold text-brand-red hover:text-white transition-colors uppercase tracking-wider">
                            View Full Logs <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Financial Intelligence Deep Dive Modal ──────────────────────────── --}}
        <div id="fi-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/90 backdrop-blur-sm p-4 sm:p-6 transition-all duration-300">
            <div class="bg-surface-card border border-slate-700/50 rounded-2xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden relative">
                     
                    <!-- Header -->
                    <div class="px-6 py-4 flex items-center justify-between border-b border-slate-700/50 bg-slate-800/30">
                        <div class="flex items-center gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                                    <i class="fas fa-chart-pie text-emerald-400"></i>
                                    Network Financial Intelligence
                                </h2>
                                <p class="text-xs text-slate-400 mt-1 font-mono uppercase tracking-tighter">Rolling 12-Month Performance Audit</p>
                            </div>
                            
                            <!-- Year Filter -->
                            <div class="ml-6 flex items-center gap-2 bg-slate-900/50 border border-slate-700/50 rounded-lg px-2 py-1">
                                <label class="text-[10px] font-bold text-slate-500 uppercase">Analysis Year:</label>
                                <select id="fi-year-filter" onchange="openNetworkDeepDive(this.value)" class="bg-transparent text-xs font-bold text-emerald-400 outline-none cursor-pointer">
                                    <option value="{{ date('Y') }}" class="bg-slate-900">{{ date('Y') }} (Current)</option>
                                    <option value="{{ date('Y') - 1 }}" class="bg-slate-900">{{ date('Y') - 1 }}</option>
                                </select>
                            </div>
                        </div>
                        <button onclick="closeNetworkDeepDive()" class="text-slate-400 hover:text-white transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Loader -->
                    <div id="fi-loader" class="flex-1 flex flex-col items-center justify-center p-12 min-h-[400px]">
                        <i class="fas fa-circle-notch fa-spin text-4xl text-emerald-500 mb-4 shadow-[0_0_15px_rgba(16,185,129,0.5)] rounded-full"></i>
                        <p class="text-slate-400 font-mono text-sm tracking-wider animate-pulse">ESTABLISHING UPLINK...</p>
                    </div>

                    <!-- Content (Full Dashboard) -->
                    <div id="fi-content" class="hidden flex-1 flex flex-col overflow-y-auto p-6 space-y-8 custom-scrollbar">
                        
                        <!-- Top Rows: Metric Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-slate-900/40 border border-slate-700/30 rounded-2xl p-5 flex flex-col justify-between shadow-lg">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Total Network Revenue</span>
                                <div class="flex items-center justify-between">
                                    <h4 id="fi-stat-total" class="text-2xl font-bold text-white">RM 0.00</h4>
                                    <i class="fas fa-wallet text-emerald-500/50 text-xl"></i>
                                </div>
                                <div class="mt-2 text-[10px] text-slate-400 font-mono">Consolidated across all live nodes</div>
                            </div>
                            <div class="bg-slate-900/40 border border-slate-700/30 rounded-2xl p-5 flex flex-col justify-between shadow-lg">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Avg Monthly Performance</span>
                                <div class="flex items-center justify-between">
                                    <h4 id="fi-stat-avg" class="text-2xl font-bold text-white">RM 0.00</h4>
                                    <i class="fas fa-calendar-check text-sky-500/50 text-xl"></i>
                                </div>
                                <div class="mt-2 text-[10px] text-slate-400 font-mono">Mean revenue per active month</div>
                            </div>
                            <div class="bg-slate-900/40 border border-slate-700/30 rounded-2xl p-5 flex flex-col justify-between shadow-lg">
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Active Sites Monitored</span>
                                <div class="flex items-center justify-between">
                                    <h4 id="fi-stat-sites" class="text-2xl font-bold text-white">3 / 3</h4>
                                    <i class="fas fa-server text-orange-500/50 text-xl"></i>
                                </div>
                                <div class="mt-2 text-[10px] text-slate-400 font-mono">Network synchronization pulse active</div>
                            </div>
                        </div>

                        <!-- Middle Row: Performance Chart -->
                        <div class="bg-slate-900/40 border border-slate-700/30 rounded-2xl p-6 shadow-xl relative">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Revenue Comparison Trend</h3>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span><span class="text-[10px] text-slate-400 font-bold uppercase">HQ</span></div>
                                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-orange-500"></span><span class="text-[10px] text-slate-400 font-bold uppercase">Prism</span></div>
                                    <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-sky-500"></span><span class="text-[10px] text-slate-400 font-bold uppercase">Alphafin</span></div>
                                </div>
                            </div>
                            <div class="h-[280px]">
                                <canvas id="fiTrendChart"></canvas>
                            </div>
                        </div>

                        <!-- Bottom Row: Financial Comparison Table -->
                        <div class="bg-slate-850/50 border border-slate-700/30 rounded-2xl overflow-hidden shadow-2xl">
                            <div class="px-6 py-4 border-b border-slate-700/30 bg-slate-800/20">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Monthly Financial Breakdown</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead>
                                        <tr class="bg-slate-950/40 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b border-slate-700/30">
                                            <th class="px-6 py-4">Month</th>
                                            <th class="px-6 py-4">BrandThirty HQ</th>
                                            <th class="px-6 py-4">Prism Media Hub</th>
                                            <th class="px-6 py-4">Alphafin</th>
                                            <th class="px-6 py-4 text-emerald-400 text-right">Combined Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="fi-comparison-body" class="divide-y divide-slate-700/20 text-xs font-mono">
                                        <!-- Populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        {{-- ── End Financial Intelligence Modal ────────────────────────────────────── --}}
    </div>

    <style>
        @keyframes pulse-bar {
            0% {
                height: 10%;
            }

            100% {
                height: 95%;
            }
        }

        .fancy-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .fancy-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .fancy-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(30, 41, 59, 0.8);
            border-radius: 10px;
        }
    </style>

    <script>
        // ── Financial Intelligence Modal Vanilla Logic ────────────────────────────
        let fiData = null;
        let fiChart = null;

        async function openNetworkDeepDive(year) {
            const currentYear = year || document.getElementById('fi-year-filter').value;
            console.log(`Deep Dive Triggered for year ${currentYear}`);
            
            const modal = document.getElementById('fi-modal');
            const loader = document.getElementById('fi-loader');
            const content = document.getElementById('fi-content');

            modal.style.display = 'flex';
            setTimeout(() => modal.classList.remove('opacity-0'), 10);
            
            loader.classList.remove('hidden');
            content.classList.add('hidden');

            try {
                const res = await fetch(`{{ route('admin.network.financial') }}?year=${currentYear}`);
                const json = await res.json();
                
                if (json.success) {
                    fiData = json.data;
                    renderFIModalDashboard(currentYear);
                    loader.classList.add('hidden');
                    content.classList.remove('hidden');
                } else {
                    alert('Backend error: ' + (json.message || 'Unknown error'));
                }
            } catch (err) {
                console.error(err);
                alert('Could not establish uplink with HQ.');
            }
        }

        function closeNetworkDeepDive() {
            const modal = document.getElementById('fi-modal');
            modal.style.display = 'none';
        }

        function renderFIModalDashboard(year) {
            const tableBody = document.getElementById('fi-comparison-body');
            const chartCanvas = document.getElementById('fiTrendChart');
            
            // 1. Calculate Aggregates
            let totalRevenue = 0;
            let activeMonths = 0;
            const siteKeys = Object.keys(fiData);
            const months = Object.keys(fiData[siteKeys[0]].monthly_sales).sort();

            const seriesData = {
                pgsql: [],
                prism: [],
                alphafin: []
            };

            tableBody.innerHTML = '';
            
            months.forEach(mKey => {
                const hqVal = fiData.pgsql?.monthly_sales[mKey] || 0;
                const prismVal = fiData.prism?.monthly_sales[mKey] || 0;
                const alphaVal = fiData.alphafin?.monthly_sales[mKey] || 0;
                const grandTotal = hqVal + prismVal + alphaVal;

                if (grandTotal > 0) activeMonths++;
                totalRevenue += grandTotal;

                seriesData.pgsql.push(hqVal);
                seriesData.prism.push(prismVal);
                seriesData.alphafin.push(alphaVal);

                const row = `
                    <tr class="hover:bg-slate-800/20 transition-colors">
                        <td class="px-6 py-4 text-slate-400 font-bold">${mKey}</td>
                        <td class="px-6 py-4 text-slate-300">RM ${hqVal.toLocaleString()}</td>
                        <td class="px-6 py-4 text-slate-300">RM ${prismVal.toLocaleString()}</td>
                        <td class="px-6 py-4 text-slate-300">RM ${alphaVal.toLocaleString()}</td>
                        <td class="px-6 py-4 text-emerald-400 font-bold text-right">RM ${grandTotal.toLocaleString()}</td>
                    </tr>
                `;
                tableBody.insertAdjacentHTML('beforeend', row);
            });

            // 2. Update Metric Cards
            document.getElementById('fi-stat-total').textContent = `RM ${totalRevenue.toLocaleString(undefined, {minimumFractionDigits:2})}`;
            const avg = activeMonths > 0 ? totalRevenue / activeMonths : 0;
            document.getElementById('fi-stat-avg').textContent = `RM ${avg.toLocaleString(undefined, {minimumFractionDigits:2})}`;

            // 3. Render Trend Chart
            if (fiChart) {
                fiChart.destroy();
            }

            const ctx = chartCanvas.getContext('2d');
            fiChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: months.map(m => m.split('-')[1]), // Just month numbers
                    datasets: [
                        { label: 'HQ', data: seriesData.pgsql, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.1)', fill: true, tension: 0.4 },
                        { label: 'Prism', data: seriesData.prism, borderColor: '#f97316', backgroundColor: 'rgba(249, 115, 22, 0.1)', fill: true, tension: 0.4 },
                        { label: 'Alphafin', data: seriesData.alphafin, borderColor: '#0ea5e9', backgroundColor: 'rgba(14, 165, 233, 0.1)', fill: true, tension: 0.4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: '#64748b', font: { size: 10 }, callback: v => 'RM ' + v.toLocaleString() }
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { size: 10 } }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

            // ── Revenue Donut Chart ───────────────────────────────────────────────────
            if (typeof Chart !== 'undefined') {
                const ctx = document.getElementById('revenueDonutChart').getContext('2d');
                Chart.defaults.color = '#64748b';
                Chart.defaults.font.family = 'Inter';
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Prism Media Hub', 'Alphafin', 'BrandThirty HQ'],
                        datasets: [{
                            data: [{{ $stats['revenue_prism'] }}, {{ $stats['revenue_alphafin'] }}, {{ $stats['revenue_brandthirty'] }}],
                            backgroundColor: ['#f97316', '#38bdf8', '#E31E24'],
                            borderWidth: 0, hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '75%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15,23,42,0.9)', titleColor: '#fff',
                                bodyColor: '#cbd5e1', borderColor: '#334155', borderWidth: 1, padding: 10,
                                callbacks: {
                                    label: ctx => (ctx.label || '') + ': ' +
                                        new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(ctx.parsed)
                                }
                            }
                        }
                    }
                });
            }

            // ── Registry Search ───────────────────────────────────────────────────────
            document.getElementById('registry-search')?.addEventListener('input', function () {
                const q = this.value.toLowerCase();
                document.querySelectorAll('.registry-row').forEach(row => {
                    const text = [row.dataset.domain, row.dataset.ip, row.querySelector('.font-bold.text-white.text-sm')?.textContent]
                        .join(' ').toLowerCase();
                    row.style.display = text.includes(q) ? '' : 'none';
                });
            });

            // ── Toast Helper ─────────────────────────────────────────────────────────
            const toast = document.getElementById('toggle-toast');
            function showToast(msg, ok) {
                toast.textContent = msg;
                toast.className = `mx-3 mb-2 px-4 py-3 rounded-lg text-xs font-semibold border transition-all duration-300 ${ok ? 'bg-emerald-900/40 border-emerald-500/40 text-emerald-300'
                        : 'bg-red-900/40 border-red-500/40 text-red-300'
                    }`;
                toast.classList.remove('hidden');
                clearTimeout(toast._t);
                toast._t = setTimeout(() => toast.classList.add('hidden'), 5000);
            }

            // ── Live Activity Feed Updater ────────────────────────────────────────────
            function renderFeed(activities) {
                const feed = document.getElementById('activity-feed');
                if (!activities || !activities.length) return;
                feed.innerHTML = activities.map((a, i) => `
                <div class="flex gap-3 relative group activity-item">
                    <div class="w-px absolute top-6 bottom-[-20px] left-3 bg-slate-700/50 ${i === activities.length - 1 ? 'hidden' : ''}"></div>
                    <div class="relative z-10 w-6 h-6 rounded-full ${a.icon_bg} border flex items-center justify-center shrink-0 mt-1">
                        <i class="fas ${a.icon} ${a.icon_color} text-[9px]"></i>
                    </div>
                    <div>
                        <p class="text-[13px] text-slate-300 leading-tight">
                            <span class="font-bold ${a.site_color}">${a.site_name}:</span> ${a.description}
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-[10px] text-slate-500 font-mono">${a.human_time}</span>
                            ${a.duration_ms ? `<span class="text-[9px] text-slate-600 font-mono">· ${a.duration_ms}ms</span>` : ''}
                            ${a.status === 'failed' ? '<span class="text-[9px] text-red-400 font-bold uppercase">FAILED</span>' : ''}
                            ${a.status === 'queued' ? '<span class="text-[9px] text-amber-400 font-bold uppercase">QUEUED</span>' : ''}
                        </div>
                    </div>
                </div>`).join('');
            }

            // ── Module Toggle ─────────────────────────────────────────────────────────
            document.querySelectorAll('.module-toggle').forEach(cb => {
                cb.addEventListener('change', async function () {
                    const orig = !this.checked;
                    this.disabled = true;
                    try {
                        const res = await fetch('{{ route("admin.network.toggle") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            body: JSON.stringify({ site_key: this.dataset.siteKey, module: this.dataset.module, state: this.checked }),
                        });
                        const data = await res.json();
                        showToast(data.message, data.success || data.queued);
                        if (!data.success && !data.queued) this.checked = orig;
                        if (data.activity) renderFeed(data.activity);
                    } catch { this.checked = orig; showToast('Network error.', false); }
                    finally { this.disabled = false; }
                });
            });

            // ── God-Mode Button ───────────────────────────────────────────────────────
            let godmodeTimer = null;
            document.querySelectorAll('.god-mode-btn').forEach(btn => {
                btn.addEventListener('click', async function () {
                    const siteKey = this.dataset.siteKey;
                    const siteName = this.dataset.siteName;
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin text-amber-300 mr-1"></i> Generating…';

                    try {
                        const res = await fetch(`{{ route("admin.network.godmode") }}?site_key=${siteKey}`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                        });
                        const data = await res.json();

                        if (data.success) {
                            // Show modal with countdown
                            const modal = document.getElementById('godmode-modal');
                            const linkEl = document.getElementById('godmode-link');
                            const countdown = document.getElementById('godmode-countdown');

                            linkEl.href = data.url;
                            linkEl.title = `God-Mode → ${siteName}`;
                            modal.classList.remove('hidden');

                            clearInterval(godmodeTimer);
                            let secs = data.expires_in;
                            countdown.textContent = secs;
                            godmodeTimer = setInterval(() => {
                                secs--;
                                countdown.textContent = secs;
                                if (secs <= 0) {
                                    clearInterval(godmodeTimer);
                                    modal.classList.add('hidden');
                                }
                            }, 1000);

                            if (data.activity) renderFeed(data.activity);
                            showToast(`God-Mode link ready for ${siteName}. Valid 60s.`, true);
                        } else {
                            showToast(data.message || 'Failed to generate link.', false);
                        }
                    } catch { showToast('Could not reach the API.', false); }
                    finally {
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-bolt text-amber-300 mr-1"></i> Admin';
                    }
                });
            });

            // ── Sync All Platforms ────────────────────────────────────────────────────
            document.getElementById('sync-all-btn')?.addEventListener('click', async function () {
                const icon = document.getElementById('sync-icon');
                this.disabled = true;
                icon.classList.add('fa-spin');

                try {
                    const res = await fetch('{{ route("admin.network.syncall") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                        body: JSON.stringify({}),
                    });
                    const data = await res.json();

                    showToast(data.message, data.success);
                    if (data.activity) renderFeed(data.activity);

                    // Update active site count from sync results
                    if (data.synced_count !== undefined) {
                        document.getElementById('stat-active-sites').textContent = data.synced_count;
                    }
                } catch { showToast('Sync-All failed — network error.', false); }
                finally {
                    this.disabled = false;
                    icon.classList.remove('fa-spin');
                }
            });

            // ── 30-second Pulse Auto-Refresh ─────────────────────────────────────────
            const pulseLabel = document.getElementById('pulse-label');
            const lastPulseEl = document.getElementById('last-pulse-time');

            async function refreshPulse() {
                pulseLabel.textContent = 'Checking…';
                try {
                    const data = await (await fetch('{{ route("admin.network.pulse") }}')).json();

                    data.sites.forEach(site => {
                        const cell = document.querySelector(`[data-site-key="${site.db_key}"]`);
                        if (!cell) return;
                        const badge = cell.querySelector('.site-status-badge');
                        const pingEl = cell.querySelector('.site-ping');

                        if (site.status === 'Online') {
                            badge.className = 'site-status-badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/50 shadow-[0_0_10px_rgba(16,185,129,0.3)] text-xs px-2.5 py-1 rounded-full font-bold inline-flex items-center gap-1.5 tracking-wide';
                            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 relative"><span class="absolute inset-0 rounded-full bg-emerald-400 animate-ping opacity-75"></span></span> ONLINE`;
                            if (pingEl) { pingEl.textContent = site.ping ? `${site.ping}ms` : '— ms'; pingEl.className = 'site-ping block text-[10px] text-emerald-500/70 font-mono mt-1 ml-1'; }
                        } else {
                            badge.className = 'site-status-badge bg-slate-900/80 text-slate-400 border border-slate-700/80 text-xs px-2.5 py-1 rounded-full font-bold shadow-sm inline-flex items-center gap-1.5 tracking-wide';
                            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span> OFFLINE`;
                            if (pingEl) { pingEl.textContent = '— ms'; pingEl.className = 'site-ping block text-[10px] text-slate-600 font-mono mt-1 ml-1'; }
                        }
                    });

                    document.getElementById('stat-active-sites').textContent = data.active_count;
                    lastPulseEl.textContent = `Pulse: ${data.checked_at}`;
                    pulseLabel.textContent = 'Live';
                } catch { pulseLabel.textContent = 'Error'; }
            }

            refreshPulse();
            setInterval(refreshPulse, 30000);
        });
    </script>
@endsection