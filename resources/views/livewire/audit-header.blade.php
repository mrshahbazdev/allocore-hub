<div>
    <header
        class="h-16 flex items-center justify-between px-4 sm:px-8 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm z-10 w-full relative">

        <!-- Mobile Hamburger -->
        <button @click="sidebarOpen = true"
            class="lg:hidden p-2 -ml-2 mr-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
            <span class="material-symbols-outlined">menu</span>
        </button>

        <!-- Search (Hidden on small mobile, visible on sm+) -->
        <div class="flex-1 max-w-xl hidden sm:block">
            <div class="relative group">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors">search</span>
                <input
                    class="w-full pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/20 transition-all pointer-events-auto"
                    placeholder="{{ __('Search companies or audits...') }}" type="text" />
            </div>
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center gap-2 sm:gap-4 ml-auto">
            <button
                class="p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors relative pointer-events-auto hidden sm:block cursor-pointer">
                <span class="material-symbols-outlined">notifications</span>
            </button>
            <!-- Start Audit Button -->
            <button wire:click="$set('showAuditModal', true)"
                class="flex items-center gap-1 sm:gap-2 bg-primary text-white px-3 sm:px-4 py-2 rounded-xl font-bold text-sm shadow-sm hover:bg-primary/90 transition-all pointer-events-auto cursor-pointer">
                <span class="material-symbols-outlined text-sm">add</span>
                <span class="hidden sm:inline">{{ __('Start New Audit') }}</span>
                <span class="sm:hidden">{{ __('Audit') }}</span>
            </button>
        </div>
    </header>

    <!-- Start Audit Modal -->
    @if($showAuditModal)
        <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 transition-opacity bg-slate-900/70 backdrop-blur-sm" aria-hidden="true"
                    wire:click="$set('showAuditModal', false)"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-slate-900 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-md w-full sm:p-6 border border-slate-200 dark:border-slate-800">
                    <div>
                        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-primary/10 rounded-full">
                            <span class="material-symbols-outlined text-primary text-2xl">domain</span>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-bold text-slate-900 dark:text-white" id="modal-title">
                                {{ __('Select Company for Audit') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    {{ __('Choose the company you want to perform this audit for.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <form wire:submit="startAudit">
                            <div class="mb-4 text-left">
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Company') }}</label>
                                <div class="relative">
                                    <select wire:model="selectedCompanyId" required
                                        class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none appearance-none cursor-pointer">
                                        <option value="">{{ __('Select a company...') }}</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->name }} @if($company->industry) ({{ $company->industry }}) @endif</option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <span class="material-symbols-outlined">expand_more</span>
                                    </div>
                                </div>
                                @error('selectedCompanyId') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-5 text-left">
                                <label
                                    class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ __('Audit Template') }}</label>
                                <div class="relative">
                                    <select wire:model="selectedTemplateId" required
                                        class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none appearance-none cursor-pointer">
                                        <option value="">{{ __('Select a template...') }}</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                        <span class="material-symbols-outlined">expand_more</span>
                                    </div>
                                </div>
                                @error('selectedTemplateId') <span
                                class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                                <button type="submit"
                                    class="w-full inline-flex justify-center rounded-xl border border-transparent px-4 py-2.5 bg-primary text-base font-bold text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:w-auto sm:text-sm transition-colors">
                                    {{ __('Start Audit') }}
                                </button>
                                <button type="button" wire:click="$set('showAuditModal', false)"
                                    class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 dark:border-slate-700 px-4 py-2.5 bg-white dark:bg-slate-800 text-base font-bold text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm transition-colors cursor-pointer">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
