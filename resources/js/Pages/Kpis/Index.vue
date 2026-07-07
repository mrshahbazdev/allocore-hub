<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ $t('kpi.definitions') }}</h1>
        </div>
        <div class="flex gap-2">
          <a href="/kpis/catalog" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
            {{ $t('kpi.catalog') }}
          </a>
          <a href="/kpis/create" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            {{ $t('kpi.create') }}
          </a>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex gap-3">
        <input
          v-model="search"
          type="text"
          :placeholder="$t('common.search') + '...'"
          class="flex-1 max-w-xs rounded-lg border-gray-300 text-sm"
          @input="debouncedFilter"
        />
        <select
          v-model="category"
          class="rounded-lg border-gray-300 text-sm"
          @change="applyFilters"
        >
          <option value="">{{ $t('kpi.all_categories') }}</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>

      <!-- KPI Cards -->
      <div v-if="kpis.data.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a
          v-for="kpi in kpis.data"
          :key="kpi.id"
          :href="`/kpis/${kpi.id}`"
          class="bg-white rounded-xl border border-gray-200 p-4 hover:border-primary-300 transition-colors group"
        >
          <div class="flex items-start justify-between mb-2">
            <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 text-sm">
              {{ locale === 'de' ? kpi.name_de : kpi.name_en }}
            </h3>
            <span v-if="kpi.latest_value" class="text-xs px-2 py-0.5 rounded-full font-medium"
              :class="{
                'bg-green-100 text-green-700': kpi.latest_value.status === 'on_target',
                'bg-yellow-100 text-yellow-700': kpi.latest_value.status === 'warning',
                'bg-red-100 text-red-700': kpi.latest_value.status === 'critical',
              }"
            >
              {{ kpi.latest_value.status === 'on_target' ? $t('kpi.on_target') : kpi.latest_value.status === 'warning' ? $t('kpi.warning') : $t('kpi.critical') }}
            </span>
          </div>
          <div class="text-xs text-gray-500 space-y-1">
            <div>{{ kpi.category || '—' }} &middot; {{ kpi.unit || '—' }} &middot; {{ kpi.frequency }}</div>
            <div v-if="kpi.formula" class="font-mono text-[10px] text-gray-400 truncate">{{ kpi.formula }}</div>
          </div>
          <div v-if="kpi.latest_value" class="mt-3 pt-2 border-t border-gray-100">
            <span class="text-lg font-bold text-gray-900">{{ Number(kpi.latest_value.value).toLocaleString() }}</span>
            <span class="text-xs text-gray-400 ml-1">{{ kpi.unit }}</span>
          </div>
        </a>
      </div>

      <div v-else class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <p class="text-gray-500 mb-4">{{ $t('kpi.no_kpis') }}</p>
        <div class="flex gap-3 justify-center">
          <a href="/kpis/create" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">
            {{ $t('kpi.create') }}
          </a>
          <a href="/kpis/catalog" class="px-4 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">
            {{ $t('kpi.start_from_template') }}
          </a>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Components/Layout/AppShell.vue';

const { t } = useI18n();
const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const props = defineProps({
  kpis: { type: Object, required: true },
  filters: { type: Object, default: () => ({}) },
  categories: { type: Array, default: () => [] },
});

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');

let timeout = null;
function debouncedFilter() {
  clearTimeout(timeout);
  timeout = setTimeout(applyFilters, 300);
}

function applyFilters() {
  const params = {};
  if (search.value) params.search = search.value;
  if (category.value) params.category = category.value;
  router.get('/kpis', params, { preserveState: true, replace: true });
}
</script>
