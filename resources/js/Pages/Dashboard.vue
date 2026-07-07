<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('nav.dashboard') }}</h1>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ year }}</span>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
          <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('kpi.definitions') }}</div>
          <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_kpis }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-green-200 dark:border-green-800/30 p-4">
          <div class="text-xs text-green-600 dark:text-green-400 mb-1">{{ $t('kpi.on_target') }}</div>
          <div class="text-2xl font-bold text-green-700 dark:text-green-400">{{ stats.on_target }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-yellow-200 dark:border-yellow-800/30 p-4">
          <div class="text-xs text-yellow-600 dark:text-yellow-400 mb-1">{{ $t('kpi.warning') }}</div>
          <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ stats.warning }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-red-200 dark:border-red-800/30 p-4">
          <div class="text-xs text-red-600 dark:text-red-400 mb-1">{{ $t('kpi.critical') }}</div>
          <div class="text-2xl font-bold text-red-700 dark:text-red-400">{{ stats.critical }}</div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Status Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ $t('common.status') }}</h3>
          <div v-if="stats.with_data > 0" class="flex items-center gap-6">
            <div class="relative w-32 h-32 flex-shrink-0">
              <Doughnut :data="statusChartData" :options="doughnutOptions" />
            </div>
            <div class="space-y-2 text-sm">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-gray-600 dark:text-gray-400">{{ $t('kpi.on_target') }}: {{ stats.on_target }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="text-gray-600 dark:text-gray-400">{{ $t('kpi.warning') }}: {{ stats.warning }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-gray-600 dark:text-gray-400">{{ $t('kpi.critical') }}: {{ stats.critical }}</span>
              </div>
            </div>
          </div>
          <div v-else class="text-center text-gray-400 dark:text-gray-500 py-8">{{ $t('kpi.no_data') }}</div>
        </div>

        <!-- Monthly Data Entries -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ $t('dashboard.monthly_entries') }}</h3>
          <div v-if="monthlyTotals.length">
            <Bar :data="monthlyChartData" :options="barOptions" />
          </div>
          <div v-else class="text-center text-gray-400 dark:text-gray-500 py-8">{{ $t('kpi.no_data') }}</div>
        </div>
      </div>

      <!-- Category Breakdown -->
      <div v-if="categoryBreakdown.length" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ $t('kpi.category') }}</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
          <div
            v-for="cat in categoryBreakdown"
            :key="cat.category"
            class="text-center p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg"
          >
            <div class="text-lg font-bold text-primary-600 dark:text-primary-400">{{ cat.count }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ cat.category }}</div>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-2 gap-6">
        <!-- Top KPIs -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $t('dashboard.top_kpis') }}</h3>
          </div>
          <div v-if="topKpis.length" class="divide-y divide-gray-100 dark:divide-gray-700/50">
            <a
              v-for="kpi in topKpis"
              :key="kpi.id"
              :href="`/kpis/${kpi.id}`"
              class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
            >
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ locale === 'de' ? kpi.name_de : kpi.name_en }}</div>
                <div class="text-xs text-gray-400">{{ kpi.category }}</div>
              </div>
              <div class="text-right">
                <div class="text-sm font-mono font-semibold text-gray-900 dark:text-white">{{ Number(kpi.value).toLocaleString() }} <span class="text-xs text-gray-400">{{ kpi.unit }}</span></div>
                <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium"
                  :class="{
                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': kpi.status === 'on_target',
                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': kpi.status === 'warning',
                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': kpi.status === 'critical',
                  }"
                >
                  {{ kpi.status === 'on_target' ? $t('kpi.on_target') : kpi.status === 'warning' ? $t('kpi.warning') : $t('kpi.critical') }}
                </span>
              </div>
            </a>
          </div>
          <div v-else class="p-8 text-center text-gray-400 dark:text-gray-500 text-sm">{{ $t('kpi.no_data') }}</div>
        </div>

        <!-- Recent Values -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $t('dashboard.recent_entries') }}</h3>
          </div>
          <div v-if="recentValues.length" class="divide-y divide-gray-100 dark:divide-gray-700/50">
            <a
              v-for="v in recentValues"
              :key="v.id"
              :href="`/kpis/${v.kpi_id}`"
              class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
            >
              <div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ locale === 'de' ? v.kpi_name_de : v.kpi_name_en }}</div>
                <div class="text-xs text-gray-400">{{ v.recorded_at }}</div>
              </div>
              <div class="text-sm font-mono font-semibold text-gray-900 dark:text-white">
                {{ Number(v.value).toLocaleString() }}
                <span class="text-xs text-gray-400">{{ v.unit }}</span>
              </div>
            </a>
          </div>
          <div v-else class="p-8 text-center text-gray-400 dark:text-gray-500 text-sm">{{ $t('kpi.no_data') }}</div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { Doughnut, Bar } from 'vue-chartjs';
import AppShell from '@/Components/Layout/AppShell.vue';

ChartJS.register(ArcElement, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const { t } = useI18n();
const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  monthlyTotals: { type: Array, default: () => [] },
  topKpis: { type: Array, default: () => [] },
  recentValues: { type: Array, default: () => [] },
  categoryBreakdown: { type: Array, default: () => [] },
  year: { type: Number, default: () => new Date().getFullYear() },
});

const statusChartData = computed(() => ({
  labels: [t('kpi.on_target'), t('kpi.warning'), t('kpi.critical')],
  datasets: [{
    data: [props.stats.on_target || 0, props.stats.warning || 0, props.stats.critical || 0],
    backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
    borderWidth: 0,
    cutout: '70%',
  }],
}));

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: { legend: { display: false } },
};

const monthLabels = {
  de: ['Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
  en: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
};

const monthlyChartData = computed(() => {
  const labels = monthLabels[locale.value] || monthLabels.en;
  const data = new Array(12).fill(0);
  props.monthlyTotals.forEach(m => {
    data[m.month - 1] = m.entries;
  });
  return {
    labels,
    datasets: [{
      label: t('dashboard.entries'),
      data,
      backgroundColor: '#2563eb',
      borderRadius: 4,
    }],
  };
});

const barOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, ticks: { precision: 0 } },
  },
};
</script>
