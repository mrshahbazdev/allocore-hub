<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex items-center gap-3">
        <a href="/kpis" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('kpi.catalog') }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ $t('kpi.catalog_desc') }}</p>
        </div>
      </div>

      <!-- Group by category -->
      <div v-for="(items, category) in grouped" :key="category" class="space-y-3">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700 pb-2">
          {{ category }} <span class="text-sm font-normal text-gray-400">({{ items.length }})</span>
        </h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          <div
            v-for="tpl in items"
            :key="tpl.id"
            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4"
          >
            <div class="flex items-start justify-between mb-2">
              <h3 class="font-medium text-gray-900 dark:text-white text-sm">
                {{ locale === 'de' ? tpl.name_de : tpl.name_en }}
              </h3>
              <form @submit.prevent="useTemplate(tpl.id)">
                <button
                  type="submit"
                  class="text-xs px-2.5 py-1 bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/50 font-medium flex-shrink-0"
                >
                  {{ $t('kpi.use_template') }}
                </button>
              </form>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
              {{ locale === 'de' ? tpl.description_de : tpl.description_en }}
            </p>
            <div class="flex items-center gap-2 text-[10px] text-gray-400">
              <span v-if="tpl.formula" class="font-mono bg-gray-50 dark:bg-gray-700 px-1.5 py-0.5 rounded">{{ tpl.formula }}</span>
              <span v-if="tpl.unit" class="bg-gray-50 dark:bg-gray-700 px-1.5 py-0.5 rounded">{{ tpl.unit }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Components/Layout/AppShell.vue';

const { t } = useI18n();
const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const props = defineProps({
  templates: { type: Array, default: () => [] },
});

const grouped = computed(() => {
  const groups = {};
  props.templates.forEach(tpl => {
    const cat = tpl.category || 'Other';
    if (!groups[cat]) groups[cat] = [];
    groups[cat].push(tpl);
  });
  return groups;
});

function useTemplate(id) {
  router.post(`/kpis/catalog/${id}/use`, {});
}
</script>
