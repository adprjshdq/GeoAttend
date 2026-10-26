<x-app-layout>
    <x-slot name="title">Audit Log Aktivitas</x-slot>
    <x-slot name="header">Audit Log Aktivitas & Jejak Keamanan Sistem</x-slot>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <!-- Filters Toolbar -->
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <form method="GET" action="{{ route('admin.audit_logs.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aksi, alasan, IP, nama..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <select name="action" class="py-2 px-3 text-xs rounded-xl bg-white border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>

                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-semibold hover:bg-slate-700 transition">
                    Filter Log
                </button>
                @if(request('search') || request('action'))
                    <a href="{{ route('admin.audit_logs.index') }}" class="text-xs text-slate-500 hover:text-slate-800 p-2">Reset</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-3.5 px-6">Waktu (WIB)</th>
                        <th class="py-3.5 px-6">Pelaku / Pengguna</th>
                        <th class="py-3.5 px-6">Aksi (Action)</th>
                        <th class="py-3.5 px-6">Entitas Target</th>
                        <th class="py-3.5 px-6">Keterangan / Alasan</th>
                        <th class="py-3.5 px-6">IP Address & Browser</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-[11px] font-mono text-slate-400">{{ $log->created_at->format('H:i:s') }} WIB</div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-900">{{ $log->user?->name ?? 'Sistem / Tamu' }}</div>
                                <div class="text-[11px] text-slate-400 font-medium">{{ $log->user?->role_label ?? '-' }}</div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2 py-0.5 rounded-sm font-mono text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-700">
                                {{ $log->entity_type }} #{{ $log->entity_id ?? '-' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 max-w-xs">
                                <p class="truncate" title="{{ $log->reason }}">{{ $log->reason ?? '-' }}</p>
                            </td>
                            <td class="py-4 px-6 font-mono text-[11px] text-slate-500">
                                <div>{{ $log->ip_address ?? '127.0.0.1' }}</div>
                                <div class="truncate max-w-[140px] text-[10px] text-slate-400" title="{{ $log->user_agent }}">{{ $log->user_agent ?? '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                Tidak ada log aktivitas yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
