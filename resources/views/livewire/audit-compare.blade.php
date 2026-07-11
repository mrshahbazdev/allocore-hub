<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h2 class="text-2xl sm:text-3xl font-black tracking-tight">{{ __('Audit Comparison') }}</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">{{ __('Compare results side-by-side to track progress over time or contrast different companies.') }}</p>
    </div>

    <!-- Comparison Selection -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Selection A -->
        <div class="bg-white dark:bg-slate-900 border-2 {{ $audit1 ? 'border-primary' : 'border-slate-200 dark:border-slate-800' }} rounded-2xl p-5 shadow-sm transition-all focus-within:ring-4 focus-within:ring-primary/10">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">{{ __('Audit A (Baseline)') }}</label>
            <div class="relative">
                <select wire:model.live="audit1Id" class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-base font-semibold focus:ring-0 focus:border-primary outline-none appearance-none cursor-pointer">
                    <option value="">{{ __('Select an audit...') }}</option>
                    @foreach($availableAudits as $a)
                        <option value="{{ $a->id }}">{{ $a->company->name ?? __('N/A') }} – {{ $a->updated_at->format('M d, Y') }} ({{ __('Score') }}: {{ number_format($a->results->avg('average_score') ?? 0, 1) }})</option>
                    @endforeach
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <span class="material-symbols-outlined">expand_more</span>
                </div>
            </div>
        </div>

        <!-- Selection B -->
        <div class="bg-white dark:bg-slate-900 border-2 {{ $audit2 ? 'border-blue-500' : 'border-slate-200 dark:border-slate-800' }} rounded-2xl p-5 shadow-sm transition-all focus-within:ring-4 focus-within:ring-blue-500/10">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">{{ __('Audit B (Comparison)') }}</label>
            <div class="relative">
                <select wire:model.live="audit2Id" class="w-full pl-4 pr-10 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-base font-semibold focus:ring-0 focus:border-blue-500 outline-none appearance-none cursor-pointer">
                    <option value="">{{ __('Select an audit to compare...') }}</option>
                    @foreach($availableAudits as $a)
                        @if($a->id != $audit1Id)
                            <option value="{{ $a->id }}">{{ $a->company->name ?? __('N/A') }} – {{ $a->updated_at->format('M d, Y') }} ({{ __('Score') }}: {{ number_format($a->results->avg('average_score') ?? 0, 1) }})</option>
                        @endif
                    @endforeach
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <span class="material-symbols-outlined">expand_more</span>
                </div>
            </div>
        </div>
    </div>

    @if(!$audit1 && !$audit2)
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl py-24 text-center">
            <div class="inline-flex size-20 rounded-full bg-slate-50 dark:bg-slate-800 items-center justify-center text-slate-300 mb-6">
                <span class="material-symbols-outlined text-4xl">compare_arrows</span>
            </div>
            <h3 class="text-xl font-bold mb-2">{{ __('Select Audits to Compare') }}</h3>
            <p class="text-slate-500 max-w-sm mx-auto">{{ __('Choose two completed audits from the dropdowns above to generate a side-by-side maturity comparison.') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">
            
            <!-- Overall Meta & Radar -->
            <div class="lg:col-span-4 space-y-6 lg:space-y-8">
                <!-- Audit A Card -->
                @if($audit1)
                    <div class="bg-white dark:bg-slate-900 border-2 border-primary/20 rounded-2xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-bl-full -z-10"></div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="size-3 rounded-full bg-primary ring-4 ring-primary/20"></div>
                            <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-widest">{{ __('Audit A') }}</h3>
                        </div>
                        <h4 class="text-xl font-black mb-1 leading-tight">{{ $audit1['model']->company->name ?? __('Organization') }}</h4>
                        <p class="text-slate-500 text-sm font-medium mb-6 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                            {{ $audit1['model']->updated_at->format('d M Y') }}
                        </p>
                        
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Overall Score') }}</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-black text-primary leading-none">{{ number_format($audit1['overallScore'], 1) }}</span>
                                    <span class="text-sm font-bold text-slate-400">/ 5</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary">
                                    {{ $audit1['overallMaturity'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Audit B Card -->
                @if($audit2)
                    <div class="bg-white dark:bg-slate-900 border-2 border-blue-500/20 rounded-2xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-bl-full -z-10"></div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="size-3 rounded-full bg-blue-500 ring-4 ring-blue-500/20"></div>
                            <h3 class="font-bold text-slate-800 dark:text-slate-200 text-sm uppercase tracking-widest">{{ __('Audit B') }}</h3>
                        </div>
                        <h4 class="text-xl font-black mb-1 leading-tight">{{ $audit2['model']->company->name ?? __('Organization') }}</h4>
                        <p class="text-slate-500 text-sm font-medium mb-6 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                            {{ $audit2['model']->updated_at->format('d M Y') }}
                        </p>
                        
                        <div class="flex items-end justify-between">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ __('Overall Score') }}</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-black text-blue-500 leading-none">{{ number_format($audit2['overallScore'], 1) }}</span>
                                    <span class="text-sm font-bold text-slate-400">/ 5</span>
                                </div>
                                @if($audit1)
                                    @php $diff = $audit2['overallScore'] - $audit1['overallScore']; @endphp
                                    <div class="mt-2 flex items-center gap-1 {{ $diff > 0 ? 'text-green-500' : ($diff < 0 ? 'text-red-500' : 'text-slate-400') }} text-xs font-bold">
                                        <span class="material-symbols-outlined text-[14px]">{{ $diff > 0 ? 'trending_up' : ($diff < 0 ? 'trending_down' : 'remove') }}</span>
                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 1) }} {{ __('pt') }}{{ abs($diff) == 1 ? '' : 's' }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                    {{ $audit2['overallMaturity'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Radar Chart -->
                 @if($audit1 || $audit2)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm flex flex-col items-center justify-center">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200 uppercase tracking-widest self-start mb-6">{{ __('Maturity Radar') }}</h3>
                        
                        <div class="relative w-full aspect-square max-w-[300px]">
                            <!-- Keep the livewire component from re-rendering the chart container completely -->
                            <div wire:ignore class="absolute inset-0">
                                <canvas id="comparisonRadarChart"></canvas>
                            </div>
                        </div>
                    </div>
                 @endif

            </div>
            
            <!-- Side-by-Side Breakdown -->
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="font-bold text-lg">{{ __('Detailed Breakdown') }}</h3>
                        <div class="flex items-center gap-4">
                            @if($audit1)
                                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-500">
                                    <div class="w-3 h-3 rounded bg-primary"></div> A
                                </div>
                            @endif
                            @if($audit2)
                                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-500">
                                    <div class="w-3 h-3 rounded bg-blue-500"></div> B
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @php 
                            $pillars = $audit1 ? $audit1['pillars'] : ($audit2 ? $audit2['pillars'] : collect());
                        @endphp

                        @foreach($pillars as $pillar)
                            <div class="p-6 sm:p-8 hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="bg-slate-100 dark:bg-slate-800 text-slate-500 p-2.5 rounded-xl">
                                        <span class="material-symbols-outlined text-[20px] block">{{ $pillar->icon ?? 'account_tree' }}</span>
                                    </div>
                                    <h4 class="text-base font-bold">{{ $pillar->name }}</h4>
                                </div>

                                <div class="space-y-5">
                                    <!-- Bar A -->
                                    @if($audit1)
                                        @php $score1 = $audit1['resultsByKey'][$pillar->name]->average_score ?? 0; @endphp
                                        <div class="flex items-center gap-4">
                                            <div class="w-6 text-center font-bold text-xs text-slate-400">A</div>
                                            <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-r-full rounded-l-md h-3.5 relative overflow-hidden">
                                                <div class="absolute top-0 left-0 h-full bg-primary rounded-r-full rounded-l-md transition-all duration-1000 ease-out" style="width: {{ ($score1 / 5) * 100 }}%"></div>
                                            </div>
                                            <div class="w-12 text-right font-black text-primary text-sm">{{ number_format($score1, 1) }}</div>
                                        </div>
                                    @endif

                                    <!-- Bar B -->
                                    @if($audit2)
                                        @php $score2 = $audit2['resultsByKey'][$pillar->name]->average_score ?? 0; @endphp
                                        <div class="flex items-center gap-4">
                                            <div class="w-6 text-center font-bold text-xs text-slate-400">B</div>
                                            <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-r-full rounded-l-md h-3.5 relative overflow-hidden">
                                                <div class="absolute top-0 left-0 h-full bg-blue-500 rounded-r-full rounded-l-md transition-all duration-1000 ease-out" style="width: {{ ($score2 / 5) * 100 }}%"></div>
                                            </div>
                                            <div class="w-12 text-right font-black text-blue-500 text-sm">{{ number_format($score2, 1) }}</div>
                                        </div>
                                        
                                        <!-- Diff indicator -->
                                        @if($audit1)
                                            @php $diff = $score2 - $score1; @endphp
                                            <div class="pl-14 text-xs font-semibold {{ $diff > 0 ? 'text-green-500' : ($diff < 0 ? 'text-red-500' : 'text-slate-400') }} flex items-center gap-1">
                                                <span class="material-symbols-outlined text-[14px]">{{ $diff > 0 ? 'trending_up' : ($diff < 0 ? 'trending_down' : 'remove') }}</span>
                                                {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 1) }} {{ __('from A') }}
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
        </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let chartInstance = null;

            const updateChart = (chartData) => {
                const canvas = document.getElementById('comparisonRadarChart');
                if (!canvas) return;
                
                const ctx = canvas.getContext('2d');
                
                if (chartInstance) {
                    chartInstance.destroy();
                }

                if (!chartData || (!chartData.audit1Data && !chartData.audit2Data)) {
                    console.log("Empty or invalid chart data received:", chartData);
                    return;
                }

                const datasets = [];
                
                if (chartData.audit1Data) {
                    datasets.push({
                        label: 'Audit A',
                        data: chartData.audit1Data,
                        backgroundColor: 'rgba(249, 115, 22, 0.2)',
                        borderColor: 'rgba(249, 115, 22, 1)',
                        pointBackgroundColor: 'rgba(249, 115, 22, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(249, 115, 22, 1)'
                    });
                }
                
                if (chartData.audit2Data) {
                    datasets.push({
                        label: 'Audit B',
                        data: chartData.audit2Data,
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(59, 130, 246, 1)'
                    });
                }

                chartInstance = new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: chartData.radarLabels || [],
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            r: {
                                beginAtZero: true,
                                max: 5,
                                ticks: { stepSize: 1, display: false },
                                grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)' },
                                pointLabels: { font: { size: 11, weight: 'bold', family: "'Public Sans', sans-serif" } }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: { 
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { family: "'Public Sans', sans-serif", size: 13, weight: 'bold' },
                                bodyFont: { family: "'Public Sans', sans-serif", size: 12 },
                                padding: 12,
                                boxPadding: 6,
                                cornerRadius: 8
                            }
                        }
                    }
                });
            };

            // Initial trigger when component loads using native PHP payload (no network request needed)
            updateChart({
                audit1Data: @json($audit1 ? $audit1['radarData'] : null),
                audit2Data: @json($audit2 ? $audit2['radarData'] : null),
                radarLabels: @json($audit1 ? $audit1['radarLabels'] : ($audit2 ? $audit2['radarLabels'] : []))
            });

            // Register an event listener for chart dynamic updates from Livewire dropdowns
            Livewire.on('refreshChart', (eventData) => {
                let chartData = eventData;
                if (Array.isArray(eventData) && eventData.length > 0) {
                    chartData = eventData[0];
                } else if (eventData && eventData.detail) {
                    chartData = eventData.detail;
                }
                updateChart(chartData);
            });
        });
    </script>
    @endpush
</div>
