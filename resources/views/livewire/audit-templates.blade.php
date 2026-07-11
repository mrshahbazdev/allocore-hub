<div>
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight">{{ __('Audit Templates') }}</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                {{ __('Manage custom audit templates and pillars') }}</p>
        </div>
        <button type="button" wire:click="create"
            class="flex items-center gap-2 bg-primary text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm hover:bg-primary/90 transition-all cursor-pointer w-full sm:w-auto justify-center">
            <span class="material-symbols-outlined text-sm">add</span>
            {{ __('New Template') }}
        </button>
    </div>

    <!-- Search Bar -->
    <div class="mb-4 relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
        <input wire:model.live.debounce.300ms="search"
            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all outline-none"
            placeholder="{{ __('Search templates...') }}" type="text" />
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
        <table class="w-full text-left min-w-[500px]">
            <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        {{ __('Template Name') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        {{ __('Description') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">
                        {{ __('Pillars') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">
                        {{ __('Questions') }}</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">
                        {{ __('Actions') }}</th>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($templates as $template)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="size-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    <span class="material-symbols-outlined">description</span>
                                </div>
                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $template->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ Str::limit($template->description ?? __('No description'), 50) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="inline-flex items-center justify-center size-7 bg-blue-500/10 text-blue-600 font-bold text-xs rounded-full">
                                {{ $template->pillars_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span
                                class="inline-flex items-center justify-center size-7 bg-amber-500/10 text-amber-600 font-bold text-xs rounded-full">
                                {{ $template->questions_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('audit.templates.builder', $template->id) }}" wire:navigate
                                    title="{{ __('Build Template') }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors cursor-pointer inline-block">
                                    <span class="material-symbols-outlined text-[18px]">build</span>
                                </a>
                                <button type="button" wire:click="edit({{ $template->id }})"
                                    title="{{ __('Edit Name/Desc') }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button type="button" wire:click="delete({{ $template->id }})" title="{{ __('Delete') }}"
                                    wire:confirm="{{ __('Are you sure you want to delete :name?', ['name' => $template->name]) }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3 text-slate-400">
                                <span class="material-symbols-outlined text-5xl opacity-40">format_list_bulleted</span>
                                <p class="font-semibold">{{ __('No templates found') }}</p>
                                <p class="text-sm">
                                    {{ $search ? __('Try a different search term.') : __('Create your first audit template.') }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $templates->links() }}
    </div>

    <!-- Create / Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5);">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700"
                x-data @click.stop>
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold">{{ $isEditing ? __('Edit Template Info') : __('Create New Template') }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)"
                        class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit.prevent="save" class="p-6 space-y-4">
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Template Name') }}
                            <span class="text-red-500">*</span></label>
                        <input wire:model="name" type="text" placeholder="{{ __('e.g. Q1 Marketing Audit') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all" />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Description') }}</label>
                        <textarea wire:model="description" rows="3"
                            placeholder="{{ __('Brief explanation of this audit\'s purpose...') }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all"></textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white font-bold text-sm shadow-sm hover:bg-primary/90 transition-all cursor-pointer">
                            {{ $isEditing ? __('Save Changes') : __('Create Template') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>