<template>
  <AppShell>
    <div class="max-w-5xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <a href="/kpis" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </a>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ locale === 'de' ? kpi.name_de : kpi.name_en }}
          </h1>
        </div>
        <div class="flex gap-2">
          <a :href="`/kpis/${kpi.id}/edit`" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
            {{ $t('common.edit') }}
          </a>
        </div>
      </div>

      <!-- Details Card -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
          <div>
            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $t('kpi.category') }}</span>
            <p class="font-medium text-gray-900 dark:text-white">{{ kpi.category || '—' }}</p>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $t('kpi.unit') }}</span>
            <p class="font-medium text-gray-900 dark:text-white">{{ kpi.unit || '—' }}</p>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $t('kpi.frequency') }}</span>
            <p class="font-medium text-gray-900 dark:text-white">{{ $t('kpi.' + kpi.frequency) }}</p>
          </div>
          <div>
            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $t('kpi.direction') }}</span>
            <p class="font-medium text-gray-900 dark:text-white">{{ $t('kpi.' + kpi.direction) }}</p>
          </div>
          <div v-if="kpi.formula" class="col-span-full">
            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $t('kpi.formula') }}</span>
            <p class="font-mono text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700/50 px-3 py-1.5 rounded-lg mt-1">{{ kpi.formula }}</p>
          </div>
          <div v-if="kpi.description_de || kpi.description_en" class="col-span-full">
            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $t('kpi.description') }}</span>
            <p class="text-gray-700 dark:text-gray-300">{{ locale === 'de' ? kpi.description_de : kpi.description_en }}</p>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6">
        <!-- Trend Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ $t('kpi.trend') }}</h3>
          <div v-if="values.length >= 2">
            <Line :data="trendChartData" :options="lineOptions" />
          </div>
          <div v-else class="text-center text-gray-400 dark:text-gray-500 py-12">
            {{ $t('kpi.no_data') }}
          </div>
        </div>

        <!-- Add Value Form -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">{{ $t('kpi.add_value') }}</h3>
          <form @submit.prevent="submitValue" class="space-y-3">
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('kpi.value') }}</label>
              <input v-model.number="valueForm.value" type="number" step="any" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
            </div>
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('common.date') }}</label>
              <input v-model="valueForm.recorded_at" type="date" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm" />
            </div>
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $t('common.notes') }}</label>
              <textarea v-model="valueForm.notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"></textarea>
            </div>
            <button type="submit" :disabled="valueForm.processing" class="w-full px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50">
              {{ $t('kpi.add_value') }}
            </button>
          </form>

          <!-- CSV Import -->
          <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">CSV Import</h4>
            <form @submit.prevent="submitImport">
              <input
                ref="csvInput"
                type="file"
                accept=".csv,.txt"
                @change="csvFile = $event.target.files[0]"
                class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-gray-100 file:text-gray-700 dark:file:bg-gray-700 dark:file:text-gray-300"
              />
              <p class="text-[10px] text-gray-400 mt-1">Format: date, value, notes</p>
              <button
                v-if="csvFile"
                type="submit"
                :disabled="importForm.processing"
                class="mt-2 w-full px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 disabled:opacity-50"
              >
                {{ importForm.processing ? '...' : 'Import CSV' }}
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Values Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="font-semibold text-gray-900 dark:text-white">{{ $t('kpi.history') }} <span class="text-sm font-normal text-gray-400">({{ values.length }})</span></h2>
        </div>
        <table v-if="values.length" class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 dark:bg-gray-700/50">
              <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">{{ $t('common.date') }}</th>
              <th class="px-4 py-2 text-right text-gray-600 dark:text-gray-300">{{ $t('kpi.value') }}</th>
              <th class="px-4 py-2 text-center text-gray-600 dark:text-gray-300">{{ $t('common.status') }}</th>
              <th class="px-4 py-2 text-left text-gray-600 dark:text-gray-300">{{ $t('common.notes') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="v in values" :key="v.id" class="border-t border-gray-100 dark:border-gray-700/50">
              <td class="px-4 py-2 text-gray-900 dark:text-white">{{ v.recorded_at }}</td>
              <td class="px-4 py-2 text-right font-mono text-gray-900 dark:text-white">{{ Number(v.value).toLocaleString() }}</td>
              <td class="px-4 py-2 text-center">
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium"
                  :class="{
                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': v.status === 'on_target',
                    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': v.status === 'warning',
                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400': v.status === 'critical',
                  }"
                >
                  {{ v.status === 'on_target' ? $t('kpi.on_target') : v.status === 'warning' ? $t('kpi.warning') : $t('kpi.critical') }}
                </span>
              </td>
              <td class="px-4 py-2 text-gray-500 dark:text-gray-400 text-xs">{{ v.notes || '' }}</td>
            </tr>
          </tbody>
        </table>
        <div v-else class="p-8 text-center text-gray-500 dark:text-gray-400">
          {{ $t('kpi.no_data') }}
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Chart as ChartJS, LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler } from 'chart.js';
import { Line } from 'vue-chartjs';
import AppShell from '@/Components/Layout/AppShell.vue';

ChartJS.register(LineElement, PointElement, CategoryScale, LinearScale, Tooltip, Filler);

const { t } = useI18n();
const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const props = defineProps({
  kpi: { type: Object, required: true },
  values: { type: Array, default: () => [] },
});

const valueForm = useForm({
  value: null,
  recorded_at: new Date().toISOString().split('T')[0],
  notes: '',
});

function submitValue() {
  valueForm.post(`/kpis/${props.kpi.id}/values`, {
    preserveScroll: true,
    onSuccess: () => valueForm.reset(),
  });
}

const csvFile = ref(null);
const csvInput = ref(null);
const importForm = useForm({ csv_file: null });

function submitImport() {
  if (!csvFile.value) return;
  importForm.csv_file = csvFile.value;
  importForm.post(`/kpis/${props.kpi.id}/values/import`, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      csvFile.value = null;
      if (csvInput.value) csvInput.value.value = '';
    },
  });
}

const trendChartData = computed(() => {
  const sorted = [...props.values].sort((a, b) => a.recorded_at.localeCompare(b.recorded_at));
  return {
    labels: sorted.map(v => v.recorded_at),
    datasets: [{
      label: locale.value === 'de' ? 'Wert' : 'Value',
      data: sorted.map(v => v.value),
      borderColor: '#2563eb',
      backgroundColor: 'rgba(37, 99, 235, 0.1)',
      fill: true,
      tension: 0.3,
      pointRadius: 3,
      pointBackgroundColor: sorted.map(v => ({
        on_target: '#22c55e',
        warning: '#eab308',
        critical: '#ef4444',
      }[v.status] || '#2563eb')),
    }],
  };
});

const lineOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: { legend: { display: false } },
  scales: {
    x: { display: true, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
    y: { display: true, beginAtZero: false },
  },
};
</script>
