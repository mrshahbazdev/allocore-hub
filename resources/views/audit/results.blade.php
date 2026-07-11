@extends('layouts.app')

@section('content')
<div class="w-full">

        <main class="max-w-[1200px] mx-auto w-full p-4 md:p-10">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div class="flex flex-col gap-2">
                    <div class="flex items-center gap-2 text-primary font-semibold text-sm uppercase tracking-wider">
                        <span class="material-symbols-outlined text-base">verified</span>
                        {{ __('Final Audit Result') }}
                    </div>
                    <h1 class="text-slate-900 dark:text-white text-4xl font-black leading-tight tracking-tight">
                        {{ $audit->company->name ?? __('Your Company') }}
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-base font-normal">{{ __('Last audited:') }}
                        {{ $audit->updated_at->format('F d, Y') }} • {{ __('Performed by:') }}
                        {{ $audit->creator->name ?? Auth::user()->name }}
                    </p>
                </div>
                <div class="flex gap-3 w-full md:w-auto">
                    <a href="{{ route('audit.report', $audit) }}" target="_blank"
                        class="flex w-full md:w-auto items-center justify-center rounded-xl h-12 md:h-10 px-6 md:px-4 border border-primary text-primary text-base md:text-sm font-bold hover:bg-primary/5 transition-all">
                        <span class="material-symbols-outlined text-sm mr-2">picture_as_pdf</span>
                        {{ __('Export PDF') }}
                    </a>
                    <a href="{{ route('audit.assessment', $audit) }}"
                        class="flex w-full md:w-auto items-center justify-center rounded-xl h-12 md:h-10 px-6 md:px-4 bg-primary text-white text-base md:text-sm font-bold hover:bg-opacity-90 transition-all">
                        <span class="material-symbols-outlined text-sm mr-2">refresh</span>
                        {{ __('Retake Audit') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Column: Radar Chart and Score -->
                <div class="lg:col-span-7 flex flex-col gap-6">
                    <!-- Score Card -->
                    <div
                        class="bg-white dark:bg-slate-900 rounded-xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col items-center text-center">
                        <p
                            class="text-slate-500 dark:text-slate-400 text-sm font-medium mb-2 uppercase tracking-widest">
                            {{ __('Overall Maturity Score') }}</p>
                        <div class="text-6xl font-black text-primary mb-2">{{ number_format($overallScore, 1) }}</div>
                        <div class="px-4 py-1 bg-primary/10 text-primary rounded-full font-bold text-lg mb-4">
                            {{ $overallMaturity }}
                        </div>
                        <p class="max-w-md text-slate-600 dark:text-slate-400">{{ __('Your business has established stable foundations. You are moving forward in the maturity scale.') }}</p>
                    </div>

                    <!-- Radar Chart Container -->
                    <div
                        class="bg-white dark:bg-slate-900 rounded-xl p-8 border border-slate-200 dark:border-slate-800 shadow-sm flex-col">
                        <h3 class="text-slate-900 dark:text-white text-lg font-bold mb-8">{{ __('Pillar Overview (Radar)') }}</h3>
                        <div
                            class="relative w-full aspect-square max-w-[400px] mx-auto flex items-center justify-center">
                            
                            <?php
                            $pillars = $audit->template->pillars ?? collect();
                            $numPillars = max(3, $pillars->count()); // Support N pillars
                            
                            $angles = [];
                            for ($i = 0; $i < $numPillars; $i++) {
                                $angles[] = $i * (360 / $numPillars);
                            }

                            // 1. Generate background polygonal concentric rings
                            $bgRings = [];
                            for ($step = 1; $step <= 5; $step++) {
                                $ringPoints = [];
                                $ringRadius = ($step / 5) * 80;
                                foreach ($angles as $deg) {
                                    $rad = deg2rad($deg);
                                    $x = 100 + $ringRadius * sin($rad);
                                    $y = 100 - $ringRadius * cos($rad);
                                    $ringPoints[] = "$x,$y";
                                }
                                $bgRings[] = implode(" ", $ringPoints);
                            }

                            // 2. Generate axes
                            $axes = [];
                            foreach ($angles as $deg) {
                                $rad = deg2rad($deg);
                                $x = 100 + 80 * sin($rad);
                                $y = 100 - 80 * cos($rad);
                                $axes[] = ['x' => $x, 'y' => $y];
                            }

                            // 3. Generate data and target polygons
                            $values = [];
                            $targetValues = [];
                            foreach ($pillars as $pillar) {
                                $res = $audit->results->where('level', $pillar->name)->first();
                                $values[] = $res ? max(0, min(5, $res->average_score)) : 1;
                                $targetValues[] = max(0, min(5, (float)($pillar->target_score ?? 5.0)));
                            }
                            // Fallback if no pillars
                            if(empty($values)) {
                                $values = array_fill(0, $numPillars, 1);
                                $targetValues = array_fill(0, $numPillars, 5.0);
                            }

                            $dataPoints = [];
                            $targetPoints = [];
                            foreach ($angles as $i => $deg) {
                                $rad = deg2rad($deg);
                                
                                // Actual Score
                                $r = ($values[$i] / 5) * 80;
                                $x = 100 + $r * sin($rad);
                                $y = 100 - $r * cos($rad);
                                $dataPoints[] = "$x,$y";

                                // Target Score
                                $tr = ($targetValues[$i] / 5) * 80;
                                $tx = 100 + $tr * sin($rad);
                                $ty = 100 - $tr * cos($rad);
                                $targetPoints[] = "$tx,$ty";
                            }
                            $dataPolygon = implode(" ", $dataPoints);
                            $targetPolygon = implode(" ", $targetPoints);

                            // 4. Generate Labels
                            $labels = [];
                            foreach ($angles as $i => $deg) {
                                $rad = deg2rad($deg);
                                // Push labels a bit further out
                                $lx = 100 + 95 * sin($rad);
                                $ly = 100 - 95 * cos($rad);
                                
                                $anchor = 'middle';
                                if ($lx < 90) $anchor = 'end';
                                else if ($lx > 110) $anchor = 'start';
                                
                                // Adjust y for top/bottom
                                if ($ly < 20) $ly = 10;
                                if ($ly > 180) $ly = 190;
                                
                                $name = $pillars[$i]->name ?? 'N/A';
                                $labels[] = [
                                    'name' => strtoupper(substr($name, 0, 15)),
                                    'x' => $lx,
                                    'y' => $ly,
                                    'anchor' => $anchor
                                ];
                            }
                            ?>

                            <!-- Dynamic SVG Radar Chart -->
                            <svg class="w-full h-full" viewBox="0 0 200 200">
                                <!-- Concentric Rings -->
                                @foreach($bgRings as $ring)
                                    <polygon class="text-slate-200 dark:text-slate-800" fill="none"
                                        points="{{ $ring }}" stroke="currentColor" stroke-width="1">
                                    </polygon>
                                @endforeach

                                <!-- Axes -->
                                @foreach($axes as $axis)
                                    <line class="text-slate-200 dark:text-slate-800" stroke="currentColor" x1="100" x2="{{ $axis['x'] }}"
                                        y1="100" y2="{{ $axis['y'] }}"></line>
                                @endforeach

                                <!-- Target Score Area (Dashed) -->
                                <polygon class="text-slate-400 dark:text-slate-500 transition-all duration-700 ease-out"
                                    fill="currentColor" fill-opacity="0.05" points="{{ $targetPolygon }}" stroke="currentColor"
                                    stroke-width="1.5" stroke-dasharray="4"></polygon>

                                <!-- Actual Score Area -->
                                <polygon class="text-primary transition-all duration-700 ease-out"
                                    fill="currentColor" fill-opacity="0.2" points="{{ $dataPolygon }}" stroke="currentColor"
                                    stroke-width="2.5"></polygon>

                                <!-- Target Score Data Points -->
                                @foreach($targetPoints as $point)
                                    <?php        list($x, $y) = explode(',', $point); ?>
                                    <circle class="text-slate-400 dark:text-slate-500" cx="{{ $x }}" cy="{{ $y }}" r="2"
                                        fill="currentColor"></circle>
                                @endforeach

                                <!-- Actual Score Data Points -->
                                @foreach($dataPoints as $point)
                                    <?php        list($x, $y) = explode(',', $point); ?>
                                    <circle class="text-primary" cx="{{ $x }}" cy="{{ $y }}" r="3.5"
                                        fill="currentColor" stroke="white" stroke-width="1"></circle>
                                @endforeach

                                <!-- Labels -->
                                @foreach($labels as $label)
                                    <text class="text-[8px] font-bold fill-slate-500"
                                        style="font-size: 8px;" text-anchor="{{ $label['anchor'] }}" x="{{ $label['x'] }}" y="{{ $label['y'] }}">
                                        {{ __($label['name']) }}
                                    </text>
                                @endforeach
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Pillar Breakdown -->
                <div class="lg:col-span-5 flex flex-col gap-6">

                    @if($audit->results->isNotEmpty())
                        @php
                            $lowestResult = $audit->results->sortBy('average_score')->first();
                        @endphp
                        <!-- AI Insights Box -->
                        <div class="bg-primary/5 border border-primary/20 rounded-xl p-6 relative overflow-hidden">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="size-8 rounded-lg bg-primary flex items-center justify-center text-white">
                                    <span class="material-symbols-outlined text-xl">psychology</span>
                                </div>
                                <h3 class="text-primary font-bold">{{ __('AI Insights & Action Plan') }}</h3>
                            </div>
                            <p
                                class="text-slate-800 dark:text-slate-200 text-sm leading-relaxed italic border-l-4 border-primary pl-4">
                                "{!! __('Your lowest score is in <strong>:level</strong>. Focus on improving this area first to bring balance to your business maturity.', ['level' => $lowestResult->level]) !!}"
                            </p>
                            <div class="absolute top-[-20px] right-[-20px] opacity-10 pointer-events-none">
                                <span class="material-symbols-outlined text-9xl">auto_awesome</span>
                            </div>
                        </div>
                    @endif

                    <!-- Pillar Breakdown List -->
                    <div
                        class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col gap-4">
                        <h3 class="text-slate-900 dark:text-white text-lg font-bold mb-2">{{ __('Detailed Breakdown') }}</h3>

                        @foreach(($audit->template->pillars ?? collect()) as $pillar)
                            @php
                                $level = $pillar->name;
                                $res = $audit->results->where('level', $level)->first();
                                $score = $res ? $res->average_score : 0;
                                $maturity = $res ? $res->maturity_level : __('N/A');
                                $percent = ($score / 5) * 100;

                                $colorCode = 'bg-primary';
                                $textColor = 'text-primary';
                                if ($score >= 4) {
                                    $colorCode = 'bg-emerald-500';
                                    $textColor = 'text-emerald-500';
                                } elseif ($score <= 2) {
                                    $colorCode = 'bg-red-500';
                                    $textColor = 'text-red-500';
                                } elseif ($score < 3) {
                                    $colorCode = 'bg-amber-500';
                                    $textColor = 'text-amber-500';
                                }
                            @endphp
                            <div
                                class="flex flex-col gap-2 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ __($level) }}</span>
                                    <span class="text-sm font-bold {{ $textColor }}">{{ number_format($score, 1) }} •
                                        {{ __($maturity) }}</span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                    <div class="{{ $colorCode }} h-2 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Actionable Recommendations Section -->
            @php
                $lowScoringAnswers = $audit->answers()->with('question.pillar')->get()->filter(function($ans) {
                    $q = $ans->question;
                    if (!$q || empty($q->failure_recommendation)) return false;
                    
                    // Consider it a failure if they scored 1-2 on scale, answered No (mapped to 0 or 1 internally)
                    // Or if checkbox and they didn't get all of them.
                    $score = $ans->score;
                    if ($q->question_type === 'yes_no' && ($score === 0 || $score === 1 || $score === '0')) return true;
                    if ($q->question_type === 'scale_1_to_5' && (float)$score < 3.0) return true;
                    if ($q->question_type === 'checkbox') {
                         $opts = is_array($q->options) ? count($q->options) : 1;
                         $selected = is_array($ans->selected_options) ? count($ans->selected_options) : 0;
                         if (($selected / max(1, $opts)) < 0.6) return true; // Less than 60% of checkboxes
                    }
                    return false;
                });
            @endphp

            @if($lowScoringAnswers->count() > 0)
                <div class="mt-8 bg-white dark:bg-slate-900 rounded-xl p-8 border border-red-200 dark:border-red-900/50 shadow-sm border-t-4 border-t-red-500">
                    <h3 class="text-xl font-bold text-red-600 dark:text-red-400 mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined">warning</span>
                        {{ __('Actionable Recommendations') }}
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($lowScoringAnswers as $ans)
                            <div class="bg-red-50 dark:bg-red-900/10 p-5 rounded-xl border border-red-100 dark:border-red-900/30 flex gap-4">
                                <div class="text-red-500 mt-1">
                                    <span class="material-symbols-outlined">{{ $ans->question->pillar->icon ?? 'error' }}</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-red-400 uppercase tracking-wider mb-1">{{ $ans->question->pillar->name ?? __('General') }}</p>
                                    <p class="font-bold text-slate-800 dark:text-slate-200 mb-2 leading-tight">
                                        {{ __($ans->question->question) }}
                                    </p>
                                    <div class="text-sm text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 p-3 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm relative">
                                        <div class="absolute -left-2 top-4 w-0 h-0 border-t-8 border-t-transparent border-r-8 border-r-slate-200 dark:border-r-slate-700 border-b-8 border-b-transparent"></div>
                                        <div class="absolute -left-[7px] top-[17px] w-0 h-0 border-t-7 border-t-transparent border-r-7 border-r-white dark:border-r-slate-800 border-b-7 border-b-transparent"></div>
                                        <span class="font-bold block mb-1">{{ __('Recommendation:') }}</span>
                                        {{ __($ans->question->failure_recommendation) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif


            <!-- Call to Action -->
            <div
                class="mt-10 p-6 md:p-8 rounded-2xl bg-gradient-to-r from-primary to-[#ff8c50] text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-xl text-center md:text-left">
                <div class="max-w-xl text-center md:text-left">
                    <h2 class="text-2xl font-bold mb-2">{{ __('Ready to scale to the next level?') }}</h2>
                    <p class="text-white/80">{{ __('Our specialized consulting package targets your weakest pillars to help you reach a 4.0 maturity score by Q4.') }}</p>
                </div>
                <button
                    class="w-full md:w-auto px-8 py-4 md:py-3 bg-white text-primary rounded-xl font-bold text-lg hover:shadow-lg transition-all whitespace-nowrap">
                    {{ __('Book Analysis Call') }}
                </button>
            </div>
        </main>
</div>

@endsection