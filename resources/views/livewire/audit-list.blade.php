<div>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight">{{ __('Audits') }}</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('All audit sessions for the current company') }}</p>
        </div>
    </div>

    <!-- Flash -->
    @if(session()->has('success'))
        <div class="mb-4 flex items-center gap-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300 px-5 py-3 rounded-xl font-semibold text-sm">
            <span class="material-symbols-outlined text-green-500">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-5 py-3 rounded-xl font-semibold text-sm">
            <span class="material-symbols-outlined text-red-500">error</span>
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats Row -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center">
            <p class="text-3xl font-black text-primary">{{ $stats['total'] }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wider">{{ __('Total') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center">
            <p class="text-3xl font-black text-green-500">{{ $stats['completed'] }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wider">{{ __('Completed') }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 text-center">
            <p class="text-3xl font-black text-orange-400">{{ $stats['in_progress'] }}</p>
            <p class="text-xs font-semibold text-slate-500 mt-1 uppercase tracking-wider">{{ __('In Progress') }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-3 mb-4">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
            <input wire:model.live.debounce.300ms="search" type="text"
                placeholder="{{ __('Search by company name...') }}"
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" />
        </div>
        <select wire:model.live="statusFilter"
            class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="in_progress">{{ __('In Progress') }}</option>
            <option value="completed">{{ __('Completed') }}</option>
        </select>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
        <table class="w-full text-left min-w-[600px]">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Company') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Performed By') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Status') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Score') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Date') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($audits as $audit)
                    @php $avgScore = $audit->results->avg('average_score'); @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4 text-sm font-mono text-slate-400">#{{ $audit->id }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="size-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($audit->company->name ?? 'NA', 0, 2)) }}
                                </div>
                                <span class="font-semibold text-sm">{{ $audit->company->name ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $audit->creator->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @if($audit->status === 'completed')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                    <span class="size-1.5 rounded-full bg-green-500 inline-block"></span>
                                    {{ __('Completed') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-orange-50 text-orange-500 dark:bg-orange-900/30">
                                    <span class="size-1.5 rounded-full bg-orange-400 inline-block animate-pulse"></span>
                                    {{ __('In Progress') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($audit->status === 'completed' && $avgScore)
                                <span class="font-bold text-primary">{{ number_format($avgScore, 1) }}/5</span>
                            @else
                                <span class="text-slate-400 text-sm">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">{{ $audit->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @if($audit->status === 'in_progress')
                                    <a href="{{ route('audit.assessment', $audit) }}" wire:navigate
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors" title="{{ __('Continue Audit') }}">
                                        <span class="material-symbols-outlined text-[18px]">play_circle</span>
                                    </a>
                                @else
                                    <a href="{{ route('audit.results', $audit) }}" wire:navigate
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors" title="{{ __('View Results') }}">
                                        <span class="material-symbols-outlined text-[18px]">bar_chart</span>
                                    </a>
                                @endif
                                <button type="button" wire:click="delete({{ $audit->id }})"
                                    wire:confirm="{{ __('Delete this audit? This cannot be undone.') }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer" title="{{ __('Delete') }}">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <span class="material-symbols-outlined text-5xl opacity-40">assignment_late</span>
                                <p class="font-semibold">{{ __('No audits found') }}</p>
                                <p class="text-sm">{{ $search || $statusFilter ? __('Try adjusting your filters.') : __('Start your first audit from the dashboard.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $audits->links() }}
    </div>
</div>
