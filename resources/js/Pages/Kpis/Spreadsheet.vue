<template>
  <AppShell>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('kpi.spreadsheet') }}</h1>
          <p class="text-sm text-gray-500 mt-1">
            {{ $t('kpi.spreadsheet_desc') }}
          </p>
        </div>
        <div class="flex gap-2">
          <button
            v-if="hasChanges"
            @click="saveAll"
            :disabled="saving"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50"
          >
            {{ saving ? $t('common.saving') : $t('common.save_all') }}
          </button>
          <a
            :href="`/kpis/spreadsheet/export?year=${filterYear}`"
            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            CSV Export
          </a>
          <button
            @click="showTargetGenerator = true"
            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors"
          >
            {{ $t('kpi.generate_targets') }}
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl border border-gray-200 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <select
            v-model="filterYear"
            class="rounded-lg border-gray-300 text-sm"
            @change="applyFilters"
          >
            <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
          </select>
          <select
            v-model="filterCategory"
            class="rounded-lg border-gray-300 text-sm"
            @change="applyFilters"
          >
            <option value="">{{ $t('kpi.all_categories') }}</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
      </div>

      <!-- View Toggle -->
      <div class="flex gap-1 bg-gray-100 rounded-lg p-1 w-fit">
        <button
          v-for="v in ['compact', 'detailed']"
          :key="v"
          @click="viewMode = v"
          class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
          :class="viewMode === v ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
        >
          {{ v === 'compact' ? $t('kpi.view_compact') : $t('kpi.view_detailed') }}
        </button>
      </div>

      <!-- Spreadsheet Table -->
      <div v-if="spreadsheetData.length" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-xs">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-3 py-2.5 text-left text-gray-600 font-semibold sticky left-0 bg-gray-50 z-10 min-w-[200px]">
                  {{ $t('kpi.kpi') }}
                </th>
                <th class="px-2 py-2.5 text-center text-gray-600 font-semibold min-w-[50px]">
                  {{ $t('kpi.type') }}
                </th>
                <th
                  v-for="m in months"
                  :key="m.num"
                  class="px-2 py-2.5 text-center text-gray-600 font-semibold min-w-[80px]"
                >
                  {{ m.label }}
                </th>
                <th class="px-2 py-2.5 text-center text-gray-600 font-semibold min-w-[90px] bg-gray-100">
                  YTD
                </th>
              </tr>
            </thead>
            <tbody>
              <template v-for="kpi in spreadsheetData" :key="kpi.id">
                <!-- Actual Row -->
                <tr class="border-t border-gray-100 hover:bg-blue-50/30">
                  <td
                    class="px-3 py-2 sticky left-0 bg-white z-10"
                    :rowspan="viewMode === 'detailed' ? 4 : 2"
                  >
                    <div class="font-medium text-gray-900 truncate">
                      {{ locale === 'de' ? kpi.name_de : kpi.name_en }}
                    </div>
                    <div class="text-[10px] text-gray-400">
                      {{ kpi.category }} &middot; {{ kpi.unit || '—' }}
                    </div>
                  </td>
                  <td class="px-2 py-1 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">
                      {{ $t('kpi.actual_short') }}
                    </span>
                  </td>
                  <td
                    v-for="m in months"
                    :key="'a'+m.num"
                    class="px-1 py-1 text-center"
                  >
                    <input
                      type="number"
                      step="any"
                      :value="getActual(kpi, m.num)"
                      @change="setActual(kpi, m.num, $event)"
                      class="w-full text-center text-xs rounded border-gray-200 py-1 px-1 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      :class="getActual(kpi, m.num) !== null ? '' : 'bg-yellow-50'"
                    />
                  </td>
                  <td class="px-2 py-1 text-center font-semibold text-gray-900 bg-gray-50">
                    {{ formatNum(kpi.ytd_actual) }}
                  </td>
                </tr>

                <!-- Target Row -->
                <tr class="hover:bg-orange-50/30">
                  <td class="px-2 py-1 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-orange-100 text-orange-700">
                      {{ $t('kpi.target_short') }}
                    </span>
                  </td>
                  <td
                    v-for="m in months"
                    :key="'t'+m.num"
                    class="px-1 py-1 text-center"
                  >
                    <input
                      type="number"
                      step="any"
                      :value="getTarget(kpi, m.num)"
                      @change="setTarget(kpi, m.num, $event)"
                      class="w-full text-center text-xs rounded border-orange-200 py-1 px-1 bg-orange-50/50 focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
                    />
                  </td>
                  <td class="px-2 py-1 text-center font-semibold text-gray-700 bg-gray-50">
                    {{ formatNum(kpi.ytd_target) }}
                  </td>
                </tr>

                <!-- Difference Row (detailed mode) -->
                <tr v-if="viewMode === 'detailed'" class="hover:bg-gray-50/50">
                  <td class="px-2 py-1 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                      {{ $t('kpi.diff_short') }}
                    </span>
                  </td>
                  <td
                    v-for="m in months"
                    :key="'d'+m.num"
                    class="px-2 py-1 text-center font-medium"
                    :class="diffColorClass(kpi.months[m.num]?.diff, kpi.direction)"
                  >
                    {{ formatDiff(kpi.months[m.num]?.diff) }}
                  </td>
                  <td class="px-2 py-1 text-center font-semibold bg-gray-50" :class="diffColorClass(kpi.ytd_diff, kpi.direction)">
                    {{ formatDiff(kpi.ytd_diff) }}
                  </td>
                </tr>

                <!-- % Deviation Row (detailed mode) -->
                <tr v-if="viewMode === 'detailed'" class="border-b-2 border-gray-200 hover:bg-gray-50/50">
                  <td class="px-2 py-1 text-center">
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-600">
                      %
                    </span>
                  </td>
                  <td
                    v-for="m in months"
                    :key="'p'+m.num"
                    class="px-2 py-1 text-center font-medium"
                    :class="pctColorClass(kpi.months[m.num]?.pct_dev, kpi.direction)"
                  >
                    {{ formatPct(kpi.months[m.num]?.pct_dev) }}
                  </td>
                  <td class="px-2 py-1 text-center font-semibold bg-gray-50" :class="pctColorClass(kpi.ytd_pct_dev, kpi.direction)">
                    {{ formatPct(kpi.ytd_pct_dev) }}
                  </td>
                </tr>

                <!-- Separator row in compact mode -->
                <tr v-if="viewMode === 'compact'" class="border-b-2 border-gray-200">
                  <td colspan="15" class="h-0"></td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!spreadsheetData.length" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <div class="max-w-sm mx-auto">
          <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
          <p class="text-gray-500 mb-4">{{ $t('kpi.no_kpis') }}</p>
          <div class="flex gap-3 justify-center">
            <a href="/kpis/create" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">
              {{ $t('kpi.create') }}
            </a>
            <a href="/kpis/catalog" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
              {{ $t('kpi.catalog') }}
            </a>
          </div>
        </div>
      </div>

      <!-- Target Generator Modal -->
      <div v-if="showTargetGenerator" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showTargetGenerator = false">
        <div class="bg-white rounded-xl w-full max-w-md p-6 space-y-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ $t('kpi.generate_targets') }}</h3>
          <p class="text-sm text-gray-500">{{ $t('kpi.generate_targets_desc') }}</p>
          <form @submit.prevent="submitGenerateTargets">
            <div class="space-y-3">
              <div>
                <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.select_kpi') }}</label>
                <select v-model="genForm.kpi_definition_id" required class="w-full rounded-lg border-gray-300 text-sm">
                  <option value="">—</option>
                  <option v-for="kpi in spreadsheetData" :key="kpi.id" :value="kpi.id">
                    {{ locale === 'de' ? kpi.name_de : kpi.name_en }}
                  </option>
                </select>
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.base_value') }}</label>
                  <input v-model.number="genForm.base_value" type="number" step="any" required class="w-full rounded-lg border-gray-300 text-sm" />
                </div>
                <div>
                  <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.growth_rate') }} (%)</label>
                  <input v-model.number="genForm.growth_rate" type="number" step="any" required class="w-full rounded-lg border-gray-300 text-sm" />
                </div>
              </div>
              <div>
                <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.start_month') }}</label>
                <select v-model.number="genForm.start_month" class="w-full rounded-lg border-gray-300 text-sm">
                  <option v-for="m in months" :key="m.num" :value="m.num">{{ m.label }}</option>
                </select>
              </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
              <button type="button" @click="showTargetGenerator = false" class="px-4 py-2 text-sm text-gray-600">{{ $t('common.cancel') }}</button>
              <button type="submit" :disabled="genForm.processing" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 disabled:opacity-50">
                {{ $t('kpi.generate') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed, reactive } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Components/Layout/AppShell.vue';

const { t } = useI18n();

const props = defineProps({
  spreadsheetData: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  year: { type: Number, default: () => new Date().getFullYear() },
  availableYears: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const monthLabels = {
  de: ['Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
  en: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
};

const months = computed(() => {
  const labels = monthLabels[locale.value] || monthLabels.en;
  return labels.map((label, i) => ({ num: i + 1, label }));
});

const viewMode = ref('detailed');
const saving = ref(false);
const showTargetGenerator = ref(false);

const filterYear = ref(props.filters.year || props.year);
const filterCategory = ref(props.filters.category || '');

const pendingActuals = reactive({});
const pendingTargets = reactive({});

const hasChanges = computed(() => {
  return Object.keys(pendingActuals).length > 0 || Object.keys(pendingTargets).length > 0;
});

function getActual(kpi, month) {
  const key = `${kpi.id}-${month}`;
  if (key in pendingActuals) return pendingActuals[key];
  return kpi.months[month]?.actual;
}

function getTarget(kpi, month) {
  const key = `${kpi.id}-${month}`;
  if (key in pendingTargets) return pendingTargets[key];
  return kpi.months[month]?.target;
}

function setActual(kpi, month, event) {
  const val = event.target.value;
  const key = `${kpi.id}-${month}`;
  if (val === '' || val === null) {
    delete pendingActuals[key];
  } else {
    pendingActuals[key] = parseFloat(val);
  }
}

function setTarget(kpi, month, event) {
  const val = event.target.value;
  const key = `${kpi.id}-${month}`;
  if (val === '' || val === null) {
    delete pendingTargets[key];
  } else {
    pendingTargets[key] = parseFloat(val);
  }
}

function saveAll() {
  saving.value = true;

  const actualsPayload = Object.entries(pendingActuals).map(([key, value]) => {
    const [kpiId, month] = key.split('-');
    return { kpi_definition_id: parseInt(kpiId), year: filterYear.value, month: parseInt(month), value };
  });

  const targetsPayload = Object.entries(pendingTargets).map(([key, value]) => {
    const [kpiId, month] = key.split('-');
    return { kpi_definition_id: parseInt(kpiId), year: filterYear.value, month: parseInt(month), target_value: value };
  });

  const promises = [];

  if (actualsPayload.length) {
    promises.push(
      new Promise((resolve) => {
        router.post('/kpis/spreadsheet/actuals', { actuals: actualsPayload }, {
          preserveState: true,
          preserveScroll: true,
          onFinish: resolve,
        });
      })
    );
  }

  if (targetsPayload.length) {
    promises.push(
      new Promise((resolve) => {
        router.post('/kpis/spreadsheet/targets', { targets: targetsPayload }, {
          preserveState: true,
          preserveScroll: true,
          onFinish: resolve,
        });
      })
    );
  }

  Promise.all(promises).then(() => {
    Object.keys(pendingActuals).forEach(k => delete pendingActuals[k]);
    Object.keys(pendingTargets).forEach(k => delete pendingTargets[k]);
    saving.value = false;
    applyFilters();
  });
}

function applyFilters() {
  const params = { year: filterYear.value };
  if (filterCategory.value) params.category = filterCategory.value;
  router.get('/kpis/spreadsheet', params, { preserveState: true, replace: true });
}

const genForm = useForm({
  kpi_definition_id: '',
  year: props.year,
  base_value: null,
  growth_rate: 1,
  start_month: 1,
});

function submitGenerateTargets() {
  genForm.year = filterYear.value;
  genForm.post('/kpis/spreadsheet/generate-targets', {
    preserveScroll: true,
    onSuccess: () => {
      showTargetGenerator.value = false;
      genForm.reset();
    },
  });
}

function formatNum(val) {
  if (val === null || val === undefined || val === 0) return '—';
  return locale.value === 'de'
    ? Number(val).toLocaleString('de-DE', { maximumFractionDigits: 2 })
    : Number(val).toLocaleString('en-US', { maximumFractionDigits: 2 });
}

function formatDiff(val) {
  if (val === null || val === undefined) return '—';
  const prefix = val > 0 ? '+' : '';
  return prefix + formatNum(val);
}

function formatPct(val) {
  if (val === null || val === undefined) return '—';
  const prefix = val > 0 ? '+' : '';
  return prefix + Number(val).toFixed(1) + '%';
}

function diffColorClass(val, direction) {
  if (val === null || val === undefined) return 'text-gray-400';
  const isGood = direction === 'lower_better' ? val < 0 : val > 0;
  const isBad = direction === 'lower_better' ? val > 0 : val < 0;
  if (isGood) return 'text-green-600';
  if (isBad) return 'text-red-600';
  return 'text-gray-600';
}

function pctColorClass(val, direction) {
  return diffColorClass(val, direction);
}
</script>
