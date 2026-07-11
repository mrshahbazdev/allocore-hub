<div>
    <!-- Progress Stepper -->
    <div class="mb-12">
        <div class="flex justify-between items-center mb-4">
            <span class="text-xs font-bold uppercase tracking-widest text-primary">{{ __('Stage') }} {{ $currentStep }} {{ __('of') }}
                {{ max(1, count($pillars)) }}</span>
            <span
                class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ round((($currentStep - 1) / max(1, count($pillars))) * 100) }}%
                {{ __('Completed') }}</span>
        </div>
        <div class="grid gap-2 sm:gap-4"
            style="grid-template-columns: repeat({{ max(1, count($pillars)) }}, minmax(0, 1fr));">
            @foreach($pillars as $index => $pillar)
                <div class="flex flex-col gap-2">
                    <div
                        class="h-2 rounded-full {{ ($index + 1) <= $currentStep ? 'bg-primary' : 'bg-slate-200 dark:bg-slate-800' }}">
                    </div>
                    <span
                        class="text-[10px] sm:text-xs font-bold {{ ($index + 1) <= $currentStep ? 'text-primary' : 'text-slate-400' }} text-center uppercase truncate"
                        title="{{ __($pillar->name) }}">{{ __($pillar->name) }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Validation Error Banner -->
    @if(session()->has('error'))
        <div
            class="mb-6 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-5 py-4 rounded-xl font-semibold text-sm">
            <span class="material-symbols-outlined text-red-500">error</span>
            {{ session('error') }}
        </div>
    @endif

    <!-- Pillar Intro -->
    <div
        class="mb-10 bg-white dark:bg-slate-900 p-6 sm:p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="bg-primary/10 text-primary p-3 rounded-xl">
                <span
                    class="material-symbols-outlined text-3xl">{{ $pillars[$currentStep - 1]->icon ?? 'account_tree' }}</span>
            </div>
            <div>
                <h2 class="text-2xl font-bold mb-2">{{ __('Pillar') }} {{ $currentStep }}: {{ __($currentLevelName) }}</h2>
                <p class="text-slate-600 dark:text-slate-400 max-w-2xl">
                    {{ __('Please answer the following questions to assess your maturity level in this pillar.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Audit Questions Form -->
    <div class="space-y-6 pb-32 sm:pb-24">
        @foreach($currentQuestions as $index => $question)
            <div
                class="bg-white dark:bg-slate-900 p-5 sm:p-8 rounded-xl border {{ !empty($answers[$question->id]['score']) ? 'border-primary/50 ring-1 ring-primary/20' : 'border-slate-200 dark:border-slate-800' }} shadow-sm transition-all hover:border-primary/30">
                <div class="flex flex-col gap-6">
                    <div class="flex justify-between items-start">
                        <h3 class="text-lg font-bold leading-snug">{{ $index + 1 }}.
                            {{ __($question->question ?? $question->criterion) }}</h3>
                        @if($question->is_required && empty($answers[$question->id]['score']))
                            <span
                                class="text-xs font-bold text-orange-500 px-2 py-1 bg-orange-50 dark:bg-orange-900/30 rounded">{{ __('REQUIRED') }}</span>
                        @elseif($question->is_required === false && empty($answers[$question->id]['score']))
                            <span
                                class="text-xs font-bold text-slate-500 px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded">{{ __('OPTIONAL') }}</span>
                        @else
                            <span
                                class="text-xs font-bold text-green-500 px-2 py-1 bg-green-50 dark:bg-green-900/30 rounded flex items-center gap-1"><span
                                    class="material-symbols-outlined text-[14px]">check</span> {{ __('ANSWERED') }}</span>
                        @endif
                    </div>

                    <p class="text-slate-600 dark:text-slate-400">{{ __($question->description) }}</p>

                    @switch($question->question_type)
                        @case('yes_no')
                            <div class="flex gap-4">
                                <button type="button" wire:click="setScore({{ $question->id }}, 1)"
                                    class="flex-1 flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all
                                        {{ ($answers[$question->id]['score'] ?? null) === 1
                                            ? 'border-green-500 bg-green-500/10 text-green-600 shadow-sm shadow-green-500/20'
                                            : 'border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 hover:border-green-500/40 hover:bg-green-500/5' }}">
                                    <span class="material-symbols-outlined text-2xl mb-1">thumb_up</span>
                                    <span class="font-bold">{{ __('Yes') }}</span>
                                </button>
                                <button type="button" wire:click="setScore({{ $question->id }}, 0)"
                                    class="flex-1 flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all
                                        {{ ($answers[$question->id]['score'] ?? null) === 0
                                            ? 'border-red-500 bg-red-500/10 text-red-600 shadow-sm shadow-red-500/20'
                                            : 'border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 hover:border-red-500/40 hover:bg-red-500/5' }}">
                                    <span class="material-symbols-outlined text-2xl mb-1">thumb_down</span>
                                    <span class="font-bold">{{ __('No') }}</span>
                                </button>
                            </div>
                            @break

                        @case('text_input')
                            <div>
                                <textarea wire:model.blur="answers.{{ $question->id }}.score"
                                    class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30 focus:border-primary focus:ring-primary text-sm p-4 min-h-[100px]"
                                    placeholder="{{ __('Enter your detailed response here...') }}"></textarea>
                            </div>
                            @break

                        @case('file_upload')
                            <div class="border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-xl p-8 text-center bg-slate-50 dark:bg-slate-800/30 hover:border-primary/50 transition-colors">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-4xl text-slate-400">cloud_upload</span>
                                    <p class="text-sm text-slate-600 dark:text-slate-400 font-semibold mb-2">{{ __('Click below to upload evidence') }}</p>
                                    <input type="file" wire:model="answers.{{ $question->id }}.score" class="w-full max-w-xs text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer"/>
                                    @if(is_string($answers[$question->id]['score'] ?? null))
                                        <!-- Show indicator if previously uploaded string path exists -->
                                        <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 text-green-600 rounded-full text-xs font-bold">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span> {{ __('File already uploaded') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @break

                        @case('select')
                            <div>
                                <select wire:model="answers.{{ $question->id }}.score" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30 focus:border-primary focus:ring-primary text-sm cursor-pointer p-3">
                                    <option value="">{{ __('-- Choose an option --') }}</option>
                                    @foreach($question->options as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @break

                        @case('checkbox')
                            <div class="space-y-3">
                                @foreach($question->options as $option)
                                    <button type="button" wire:click="toggleCheckbox({{ $question->id }}, '{{ $option }}')" class="w-full flex items-center justify-between p-4 rounded-xl border-2 transition-all text-left
                                        {{ in_array($option, $answers[$question->id]['score'] ?? [])
                                            ? 'border-primary bg-primary/5 text-primary shadow-sm shadow-primary/10'
                                            : 'border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/30 text-slate-600 dark:text-slate-400 hover:border-primary/40' }}">
                                        <span class="font-semibold text-sm">{{ $option }}</span>
                                        <div class="w-5 h-5 rounded border border-slate-300 dark:border-slate-600 flex items-center justify-center
                                            {{ in_array($option, $answers[$question->id]['score'] ?? []) ? 'bg-primary border-primary' : 'bg-white dark:bg-slate-900' }}">
                                            @if(in_array($option, $answers[$question->id]['score'] ?? []))
                                                <span class="material-symbols-outlined text-white" style="font-size: 14px;">check</span>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                            @break

                        @default
                            <div class="flex flex-wrap gap-3">
                                @foreach(range(1, 5) as $score)
                                    <button type="button" wire:click="setScore({{ $question->id }}, {{ $score }})"
                                        class="flex-1 min-w-[60px] flex flex-col items-center justify-center p-4 rounded-xl border-2 transition-all
                                            {{ ($answers[$question->id]['score'] ?? null) == $score
                                                ? 'border-primary bg-primary/10 text-primary shadow-sm shadow-primary/20'
                                                : 'border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 hover:border-primary/40 hover:bg-primary/5' }}">
                                        <span class="text-lg font-bold">{{ $score }}</span>
                                        <span class="text-[10px] uppercase font-bold mt-1 opacity-70">
                                            {{ $score == 1 ? __('Poor') : ($score == 2 ? __('Fair') : ($score == 3 ? __('Good') : ($score == 4 ? __('Great') : __('Excel')))) }}
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                            @break
                    @endswitch

                    @if($question->question_type !== 'text_input')
                        <div class="space-y-2 mt-2">
                            <label class="text-sm font-semibold text-slate-500">{{ __('Additional Observations (Optional)') }}</label>
                            <textarea wire:model.blur="answers.{{ $question->id }}.comment"
                                class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/30 focus:border-primary focus:ring-primary text-sm"
                                placeholder="{{ __('Provide context for your rating...') }}" rows="2"></textarea>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Bottom Navigation Footer -->
    <footer
        class="fixed bottom-0 left-0 right-0 bg-white/90 dark:bg-background-dark/90 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 py-3 sm:py-4 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-3">
                @if($currentStep > 1)
                    <button type="button" wire:click="previousStep"
                        class="flex items-center gap-1 sm:gap-2 px-4 sm:px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-sm sm:text-base">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        <span class="hidden sm:inline">{{ __('Back') }}</span>
                    </button>
                @else
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-1 sm:gap-2 px-4 sm:px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-sm sm:text-base">
                        {{ __('Cancel') }}
                    </a>
                @endif

                <div class="flex gap-2 sm:gap-4">
                    <button type="button" wire:click="saveDraft"
                        class="hidden sm:flex items-center gap-2 px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        {{ __('Save Draft') }}
                    </button>
                    <button type="button" wire:click="nextStep"
                        class="flex items-center gap-1 sm:gap-2 px-5 sm:px-8 py-3 rounded-xl bg-primary text-white font-bold shadow-lg shadow-primary/25 hover:bg-primary/90 transition-all text-sm sm:text-base">
                        {{ $currentStep == count($pillars) ? __('Finish Audit') : __('Next Step') }}
                        @if($currentStep < count($pillars))
                            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        @else
                            <span class="material-symbols-outlined text-[20px]">done_all</span>
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </footer>
</div>