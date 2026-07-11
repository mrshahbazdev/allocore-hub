<div>
    <!-- Page Header -->
    <div
        class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-6">
        <div>
            <div class="flex items-center gap-2 mb-2 text-sm text-slate-500 font-semibold">
                <a href="{{ route('audit.templates') }}" wire:navigate
                    class="hover:text-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span> {{ __('Back to Templates') }}
                </a>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black tracking-tight flex items-center gap-3">
                <span class="material-symbols-outlined text-primary text-3xl hidden sm:block">build_circle</span>
                {{ $template->name }}
            </h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 sm:ml-11">
                {{ $template->description ?? __('No description provided.') }}</p>
        </div>
        <button type="button" wire:click="createPillar"
            class="flex items-center gap-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm hover:bg-slate-800 dark:hover:bg-slate-100 transition-all cursor-pointer w-full sm:w-auto justify-center">
            <span class="material-symbols-outlined text-sm">add</span>
            {{ __('Add Pillar') }}
        </button>
    </div>

    <!-- Pillars & Questions Layout -->
    <div class="space-y-6 lg:space-y-8">
        @forelse($pillars as $pillar)
            <div
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <!-- Pillar Header -->
                <div
                    class="bg-slate-50 dark:bg-slate-800/50 p-4 sm:px-6 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800">
                    <div class="flex items-start gap-4">
                        <div class="bg-primary/10 text-primary p-3 rounded-xl flex-shrink-0">
                            <span class="material-symbols-outlined text-2xl">{{ $pillar->icon ?? 'account_tree' }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $pillar->name }}</h3>
                            @if($pillar->description)
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-2xl">{{ $pillar->description }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="createQuestion({{ $pillar->id }})" title="{{ __('Add Question') }}"
                            class="flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-lg font-bold text-xs transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">add</span> {{ __('Add Question') }}
                        </button>
                        <button type="button" wire:click="editPillar({{ $pillar->id }})" title="{{ __('Edit Pillar') }}"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                        </button>
                        <button type="button" wire:click="deletePillar({{ $pillar->id }})" title="{{ __('Delete Pillar') }}"
                            wire:confirm="{{ __('This will delete the pillar AND all its questions. Are you sure?') }}"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>
                </div>

                <!-- Questions List -->
                <div class="divide-y divide-slate-100 dark:divide-slate-800/60 p-4 sm:p-0">
                    @forelse($pillar->questions as $index => $question)
                        <div
                            class="sm:px-6 py-4 flex flex-col sm:flex-row gap-4 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                            <div
                                class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-xs text-slate-500 flex-shrink-0">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-1 leading-snug">
                                    {{ $question->question }}</p>
                                @if($question->description)
                                    <p class="text-xs text-slate-500 mb-2">{{ $question->description }}</p>
                                @endif
                                <span
                                    class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-500">
                                    {{ __(str_replace('_', ' ', $question->question_type)) }}
                                </span>
                            </div>
                            <!-- Question Actions -->
                            <div
                                class="flex items-center gap-1 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity self-start sm:self-center">
                                <button type="button" wire:click="editQuestion({{ $question->id }})" title="{{ __('Edit Question') }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-primary hover:bg-primary/10 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button type="button" wire:click="deleteQuestion({{ $question->id }})" title="{{ __('Delete Question') }}"
                                    wire:confirm="{{ __('Delete this question?') }}"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-500">
                            <span class="material-symbols-outlined text-4xl opacity-20 mb-2">help_outline</span>
                            <p class="text-sm font-semibold">{{ __('No questions added yet.') }}</p>
                            <button type="button" wire:click="createQuestion({{ $pillar->id }})"
                                class="text-primary hover:underline text-xs font-bold mt-1 cursor-pointer">{{ __('Add the first question') }}</button>
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div
                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl py-20 text-center shadow-sm">
                <div
                    class="inline-flex size-20 rounded-full bg-slate-50 dark:bg-slate-800 items-center justify-center text-slate-300 mb-6">
                    <span class="material-symbols-outlined text-4xl">view_column_2</span>
                </div>
                <h3 class="text-xl font-bold mb-2">{{ __('Empty Template') }}</h3>
                <p class="text-slate-500 max-w-sm mx-auto mb-6">{{ __('Start building your custom audit by adding your first pillar (category or section).') }}</p>
                <button type="button" wire:click="createPillar"
                    class="inline-flex items-center gap-2 bg-primary text-white px-5 py-3 rounded-xl font-bold text-sm shadow-sm hover:bg-primary/90 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    {{ __('Create First Pillar') }}
                </button>
            </div>
        @endforelse
    </div>

    <!-- Pillar Modal -->
    @if($showPillarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-data
            @click.stop>
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-md border border-slate-200 dark:border-slate-700 max-h-full flex flex-col">
                <div
                    class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800 shrink-0">
                    <h3 class="text-lg font-bold">{{ $isEditingPillar ? __('Edit Pillar') : __('Add New Pillar') }}</h3>
                    <button type="button" wire:click="$set('showPillarModal', false)"
                        class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="savePillar" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-6 space-y-4 overflow-y-auto w-full">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Pillar Name') }} <span
                                    class="text-red-500">*</span></label>
                            <input wire:model="pillarName" type="text" placeholder="{{ __('e.g. Finance, Marketing, HR') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all" />
                            @error('pillarName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Icon (Material Symbols)') }}</label>
                            <input wire:model="pillarIcon" type="text" placeholder="{{ __('e.g. account_tree, payments') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all" />
                            <p class="text-xs text-slate-500 mt-1">{{ __('Use a valid Material Symbol identifier.') }}</p>
                            @error('pillarIcon') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Description') }}</label>
                            <textarea wire:model="pillarDescription" rows="3"
                                placeholder="{{ __('Explain what this pillar evaluates...') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all"></textarea>
                            @error('pillarDescription') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Target Score (Benchmark)') }}
                                <span class="text-red-500">*</span></label>
                            <input wire:model="pillarTargetScore" type="number" step="0.1" min="0" max="10"
                                placeholder="{{ __('e.g. 4.0 or 5.0') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all" />
                            <p class="text-xs text-slate-500 mt-1">{{ __('The benchmark score expected for this pillar.') }}</p>
                            @error('pillarTargetScore') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div
                        class="px-6 py-5 border-t border-slate-100 dark:border-slate-800 shrink-0 flex gap-3 bg-white dark:bg-slate-900 rounded-b-2xl">
                        <button type="button" wire:click="$set('showPillarModal', false)"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">{{ __('Cancel') }}</button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white font-bold text-sm shadow-sm hover:bg-primary/90 transition-all cursor-pointer">{{ __('Save Pillar') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Question Modal -->
    @if($showQuestionModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" x-data
            @click.stop>
            <div
                class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-lg border border-slate-200 dark:border-slate-700 max-h-full flex flex-col">
                <div
                    class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800 shrink-0">
                    <h3 class="text-lg font-bold">{{ $isEditingQuestion ? __('Edit Question') : __('Add New Question') }}</h3>
                    <button type="button" wire:click="$set('showQuestionModal', false)"
                        class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <form wire:submit.prevent="saveQuestion" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-6 space-y-4 overflow-y-auto w-full max-h-[70vh]">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Question Text') }} <span
                                    class="text-red-500">*</span></label>
                            <textarea wire:model="questionText" rows="2"
                                placeholder="{{ __('e.g. Does the company have a documented marketing strategy?') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all"></textarea>
                            @error('questionText') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Helper Text (Optional)') }}</label>
                            <textarea wire:model="questionDescription" rows="2"
                                placeholder="{{ __('Provide extra context or hints for the auditor...') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all"></textarea>
                            @error('questionDescription') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Question Type') }} <span
                                        class="text-red-500">*</span></label>
                                <select wire:model.live="questionType"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all cursor-pointer">
                                    <option value="scale_1_to_5">{{ __('Scale 1-5 (Default)') }}</option>
                                    <option value="yes_no">{{ __('Yes / No') }}</option>
                                    <option value="text_input">{{ __('Text Input') }}</option>
                                    <option value="file_upload">{{ __('File Upload') }}</option>
                                    <option value="select">{{ __('Dropdown Select') }}</option>
                                    <option value="checkbox">{{ __('Multiple Checkboxes') }}</option>
                                </select>
                                @error('questionType') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Weight (Multiplier)') }}
                                    <span class="text-red-500">*</span></label>
                                <input wire:model="questionWeight" type="number" step="0.1" min="0.1"
                                    placeholder="{{ __('e.g. 1.0, 2.0') }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all" />
                                @error('questionWeight') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center gap-2 py-2">
                            <input type="checkbox" wire:model="questionIsRequired" id="isRequired"
                                class="rounded border-slate-300 text-primary shadow-sm focus:ring-primary">
                            <label for="isRequired" class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('This question is required') }}</label>
                            @error('questionIsRequired') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Dynamic Fields based on Type -->
                        @if(in_array($questionType, ['select', 'checkbox', 'radio']))
                            <div
                                class="space-y-1 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                                <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Options (Comma Separated)') }}</label>
                                <input wire:model="questionOptionsStr" type="text"
                                    placeholder="{{ __('e.g. Option A, Option B, Option C') }}"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all" />
                                <p class="text-xs text-slate-500 mt-1">{{ __('Provide a comma-separated list of choices the user can select.') }}</p>
                                @error('questionOptionsStr') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <!-- Failure Recommendation -->
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ __('Failure Recommendation (Optional)') }}</label>
                            <textarea wire:model="questionFailureRecommendation" rows="2"
                                placeholder="{{ __('If the user scores poorly here, what is the recommended action?') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all"></textarea>
                            <p class="text-[11px] text-slate-500 mt-1">{{ __('This will be shown on the Results Dashboard if the user\'s score on this question is below 3.') }}</p>
                            @error('questionFailureRecommendation') <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Branching Logic -->
                        <details
                            class="group border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                            <summary
                                class="flex items-center justify-between cursor-pointer px-4 py-3 font-semibold text-sm text-slate-700 dark:text-slate-300 select-none">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm text-slate-500">account_tree</span>
                                    {{ __('Advanced Routing (Conditional Branching)') }}
                                </div>
                                <span
                                    class="material-symbols-outlined text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
                            </summary>
                            <div class="p-4 pt-0 border-t border-slate-200 dark:border-slate-700 mt-2 space-y-4">
                                <p class="text-xs text-slate-500">{{ __('Only show this question if a previous question was answered a specific way.') }}</p>

                                <div class="space-y-1">
                                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Depends on Question (Optional)') }}</label>
                                    <select wire:model="questionDependsOnId"
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all cursor-pointer">
                                        <option value="">{{ __('Never Skip (Always Show)') }}</option>
                                        @foreach($pillars as $p)
                                            <optgroup label="{{ $p->name }}">
                                                @foreach($p->questions as $q)
                                                    @if($q->id != $activeQuestionId) <!-- Cannot depend on itself -->
                                                        <option value="{{ $q->id }}">{{ Str::limit($q->question, 60) }}</option>
                                                    @endif
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('questionDependsOnId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-1">
                                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ __('Triggering Answer (Exact Match)') }}</label>
                                    <input wire:model="questionDependsOnAnswer" type="text"
                                        placeholder="{{ __('e.g. 1 (for Yes), 0 (for No), or a string') }}"
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary/30 focus:border-primary text-sm outline-none transition-all" />
                                    <p class="text-[10px] text-slate-500 mt-1">{{ __('Only show if the parent question answer exactly matches this value. Leave blank to show as long as parent is answered at all.') }}</p>
                                    @error('questionDependsOnAnswer') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </details>
                    </div>

                    <div
                        class="px-6 py-5 border-t border-slate-100 dark:border-slate-800 shrink-0 flex gap-3 bg-white dark:bg-slate-900 rounded-b-2xl">
                        <button type="button" wire:click="$set('showQuestionModal', false)"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">{{ __('Cancel') }}</button>
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-primary text-white font-bold text-sm shadow-sm hover:bg-primary/90 transition-all cursor-pointer">{{ __('Save Question') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>