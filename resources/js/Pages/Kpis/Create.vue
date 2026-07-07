<template>
  <AppShell>
    <div class="max-w-2xl mx-auto space-y-6">
      <div class="flex items-center gap-3">
        <a href="/kpis" class="text-gray-400 hover:text-gray-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('kpi.create') }}</h1>
      </div>

      <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">{{ $t('kpi.name') }} (DE)</label>
            <input v-model="form.name_de" type="text" required class="w-full rounded-lg border-gray-300 text-sm" />
            <p v-if="form.errors.name_de" class="text-red-500 text-xs mt-1">{{ form.errors.name_de }}</p>
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
            <input v-model="form.unit" type="text" placeholder="EUR, %, ..." class="w-full rounded-lg border-gray-300 text-sm" />
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

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <a href="/kpis" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">{{ $t('common.cancel') }}</a>
          <button type="submit" :disabled="form.processing" class="px-6 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50">
            {{ $t('common.save') }}
          </button>
        </div>
      </form>
    </div>
  </AppShell>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Components/Layout/AppShell.vue';

const { t } = useI18n();

defineProps({
  templates: { type: Array, default: () => [] },
});

const form = useForm({
  name_de: '',
  name_en: '',
  description_de: '',
  description_en: '',
  formula: '',
  unit: '',
  category: '',
  target_value: null,
  warning_threshold: null,
  critical_threshold: null,
  frequency: 'monthly',
  direction: 'higher_better',
});

function submit() {
  form.post('/kpis');
}
</script>
