<template>
  <AppShell>
    <div class="max-w-4xl mx-auto space-y-6">
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
            <p class="font-mono text-sm text-gray-900 dark:text-white">{{ kpi.formula }}</p>
          </div>
          <div v-if="kpi.description_de || kpi.description_en" class="col-span-full">
            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $t('kpi.description') }}</span>
            <p class="text-gray-700 dark:text-gray-300">{{ locale === 'de' ? kpi.description_de : kpi.description_en }}</p>
          </div>
        </div>
      </div>

      <!-- Values Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="font-semibold text-gray-900 dark:text-white">{{ $t('kpi.history') }}</h2>
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
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Components/Layout/AppShell.vue';

const { t } = useI18n();
const page = usePage();
const locale = computed(() => page.props.locale || 'de');

defineProps({
  kpi: { type: Object, required: true },
  values: { type: Array, default: () => [] },
});
</script>
