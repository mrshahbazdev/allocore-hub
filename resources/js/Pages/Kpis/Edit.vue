<template>
  <AppShell>
    <div class="max-w-2xl mx-auto space-y-6">
      <div class="flex items-center gap-3">
        <a :href="`/kpis/${kpi.id}`" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('kpi.edit_kpi') }}</h1>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.name') }} (DE)</label>
            <input v-model="form.name_de" type="text" required class="w-full rounded-lg border-gray-300 text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.name') }} (EN)</label>
            <input v-model="form.name_en" type="text" required class="w-full rounded-lg border-gray-300 text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.description') }} (DE)</label>
            <textarea v-model="form.description_de" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.description') }} (EN)</label>
            <textarea v-model="form.description_en" rows="2" class="w-full rounded-lg border-gray-300 text-sm"></textarea>
          </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.formula') }}</label>
            <input v-model="form.formula" type="text" class="w-full rounded-lg border-gray-300 text-sm font-mono" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.unit') }}</label>
            <input v-model="form.unit" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.category') }}</label>
            <input v-model="form.category" type="text" class="w-full rounded-lg border-gray-300 text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.target') }}</label>
            <input v-model.number="form.target_value" type="number" step="any" class="w-full rounded-lg border-gray-300 text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.warning_threshold') }}</label>
            <input v-model.number="form.warning_threshold" type="number" step="any" class="w-full rounded-lg border-gray-300 text-sm" />
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.critical_threshold') }}</label>
            <input v-model.number="form.critical_threshold" type="number" step="any" class="w-full rounded-lg border-gray-300 text-sm" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.frequency') }}</label>
            <select v-model="form.frequency" class="w-full rounded-lg border-gray-300 text-sm">
              <option value="daily">{{ $t('kpi.daily') }}</option>
              <option value="weekly">{{ $t('kpi.weekly') }}</option>
              <option value="monthly">{{ $t('kpi.monthly') }}</option>
              <option value="quarterly">{{ $t('kpi.quarterly') }}</option>
              <option value="yearly">{{ $t('kpi.yearly') }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.direction') }}</label>
            <select v-model="form.direction" class="w-full rounded-lg border-gray-300 text-sm">
              <option value="higher_better">{{ $t('kpi.higher_better') }}</option>
              <option value="lower_better">{{ $t('kpi.lower_better') }}</option>
            </select>
          </div>
        </div>

        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="confirmDelete"
            class="px-4 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg"
          >
            {{ $t('common.delete') }}
          </button>
          <div class="flex gap-3">
            <a :href="`/kpis/${kpi.id}`" class="px-4 py-2 text-sm text-gray-600">{{ $t('common.cancel') }}</a>
            <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50">
              {{ $t('common.save') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </AppShell>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Components/Layout/AppShell.vue';

const { t } = useI18n();

const props = defineProps({
  kpi: { type: Object, required: true },
});

const form = useForm({
  name_de: props.kpi.name_de,
  name_en: props.kpi.name_en,
  description_de: props.kpi.description_de || '',
  description_en: props.kpi.description_en || '',
  formula: props.kpi.formula || '',
  unit: props.kpi.unit || '',
  category: props.kpi.category || '',
  target_value: props.kpi.target_value,
  warning_threshold: props.kpi.warning_threshold,
  critical_threshold: props.kpi.critical_threshold,
  frequency: props.kpi.frequency,
  direction: props.kpi.direction,
});

function submit() {
  form.put(`/kpis/${props.kpi.id}`);
}

function confirmDelete() {
  if (window.confirm(t('common.confirm_delete'))) {
    router.delete(`/kpis/${props.kpi.id}`);
  }
}
</script>
