<aside
    class="w-full h-full flex-shrink-0 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-y-auto">
    <div class="flex flex-col h-full p-4">
        <div class="flex items-center gap-3 mb-10 px-2">
            <div class="bg-primary rounded-lg p-2 text-white">
                <span class="material-symbols-outlined block">query_stats</span>
            </div>
            <div>
                <h1 class="text-lg font-bold leading-tight">AuditPro</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Business Maturity') }}</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1">
            <a href="{{ route('dashboard') }}" wire:navigate
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span>{{ __('Dashboard') }}</span>
            </a>
            <a href="{{ route('companies.index') }}" wire:navigate
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('companies.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' }}">
                <span class="material-symbols-outlined">domain</span>
                <span>{{ __('Companies') }}</span>
            </a>
            <a href="{{ route('audit.templates') }}" wire:navigate
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('audit.templates') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' }}">
                <span class="material-symbols-outlined">format_list_bulleted</span>
                <span>{{ __('Templates') }}</span>
            </a>
            <a href="{{ route('audit.index') }}" wire:navigate
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('audit.index') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' }}">
                <span class="material-symbols-outlined">assignment_turned_in</span>
                <span>{{ __('Audits') }}</span>
            </a>
            <a href="{{ route('audit.compare') }}" wire:navigate
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('audit.compare') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' }}">
                <span class="material-symbols-outlined">compare_arrows</span>
                <span>{{ __('Compare') }}</span>
            </a>
            <a href="{{ route('dashboard') }}" wire:navigate
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('allocore.hub') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' }}">
                <span class="material-symbols-outlined">hub</span>
                <span>{{ __('AlloCore Hub') }}</span>
            </a>
        </nav>

        <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('profile.edit') }}" wire:navigate
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('profile.*') ? 'bg-primary/10 text-primary font-semibold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors' }}">
                <span class="material-symbols-outlined">settings</span>
                <span>{{ __('Settings') }}</span>
            </a>
            <div class="mt-4 flex items-center gap-3 px-3">
                <div
                    class="size-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-primary font-bold overflow-hidden">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ Auth::user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors" title="{{ __('Logout') }}">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
