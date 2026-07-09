<template>
  <AppShell>
    <div class="space-y-6">
      <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('nav.tools') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-2xl">
          {{ $t('tools.intro') }}
        </p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div
          v-for="tool in tools"
          :key="tool.tool"
          class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 flex flex-col shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-start gap-3">
            <div
              class="h-11 w-11 rounded-xl flex items-center justify-center text-white text-lg font-bold shadow-sm"
              :class="meta(tool.tool).badge"
            >{{ meta(tool.tool).initial }}</div>
            <div class="min-w-0 flex-1">
              <div class="text-base font-semibold text-gray-900 dark:text-white truncate">{{ tool.label }}</div>
              <div class="text-xs text-gray-400 font-mono">{{ tool.tool }}</div>
            </div>
          </div>

          <p class="mt-3 text-xs text-gray-500 dark:text-gray-400 leading-relaxed flex-1">
            {{ meta(tool.tool).desc }}
          </p>

          <div class="mt-4 flex items-center justify-between">
            <span
              class="inline-flex items-center gap-1.5 text-[11px] px-2.5 py-1 rounded-full font-medium"
              :class="statusPill(tool).class"
            >
              <span class="h-1.5 w-1.5 rounded-full" :class="statusPill(tool).dot"></span>
              {{ statusPill(tool).label }}
            </span>
            <span v-if="tool.connected && tool.last_synced_at" class="text-[10px] text-gray-400">
              {{ tool.last_synced_at }}
            </span>
          </div>

          <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-100 dark:border-gray-700/60 pt-4">
            <button
              v-if="!tool.connected"
              type="button"
              class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors"
              @click="connect(tool)"
            >{{ $t('tools.connect') }}</button>

            <template v-else>
              <button
                type="button"
                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors"
                @click="openSetup(tool)"
              >{{ $t('tools.setup') }}</button>
              <button
                type="button"
                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600"
                @click="toggle(tool)"
              >{{ tool.enabled ? $t('tools.disable') : $t('tools.enable') }}</button>
              <button
                type="button"
                class="px-3 py-1.5 text-xs font-medium rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20"
                @click="disconnect(tool)"
              >{{ $t('tools.disconnect') }}</button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Guided setup modal -->
    <div
      v-if="setupTool"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
      <div class="absolute inset-0 bg-black/50" @click="closeSetup"></div>
      <div class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
          <div
            class="h-9 w-9 rounded-lg flex items-center justify-center text-white text-sm font-bold"
            :class="meta(setupTool.tool).badge"
          >{{ meta(setupTool.tool).initial }}</div>
          <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $t('tools.connect_title', { tool: setupTool.label }) }}</div>
            <div class="text-xs text-gray-400">{{ $t('tools.connect_subtitle') }}</div>
          </div>
          <button type="button" class="ml-auto text-gray-400 hover:text-gray-600 dark:hover:text-gray-200" @click="closeSetup">✕</button>
        </div>

        <div class="p-5 space-y-4">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ $t('tools.setup_step1') }}
          </p>

          <div class="rounded-xl bg-gray-900 dark:bg-black/60 p-4 relative">
            <pre class="text-[11px] leading-relaxed text-green-300 font-mono whitespace-pre-wrap break-all">{{ envSnippet }}</pre>
            <button
              type="button"
              class="absolute top-3 right-3 px-2.5 py-1 text-[11px] font-medium rounded-md bg-white/10 text-white hover:bg-white/20"
              @click="copy(envSnippet)"
            >{{ copied ? $t('tools.copied') : $t('tools.copy') }}</button>
          </div>

          <div v-if="!revealedKeyForSetup" class="text-[11px] text-amber-600 dark:text-amber-400 flex items-start gap-1.5">
            <span>⚠</span>
            <span>{{ $t('tools.key_hidden') }}
              <button type="button" class="underline font-medium" @click="regenerate(setupTool)">{{ $t('tools.regenerate') }}</button>
            </span>
          </div>
          <div v-else class="text-[11px] text-amber-600 dark:text-amber-400">
            {{ $t('tools.key_once') }}
          </div>

          <div class="rounded-lg bg-gray-50 dark:bg-gray-700/30 p-3 text-[11px] text-gray-500 dark:text-gray-400 space-y-1">
            <div>{{ $t('tools.setup_step2') }}</div>
            <div class="flex items-center gap-2 mt-1">
              <span class="h-1.5 w-1.5 rounded-full" :class="statusPill(setupTool).dot"></span>
              <span>{{ statusPill(setupTool).label }}</span>
              <span v-if="setupTool.last_synced_at" class="text-gray-400">· {{ setupTool.last_synced_at }}</span>
            </div>
          </div>
        </div>

        <div class="p-5 border-t border-gray-100 dark:border-gray-700 flex justify-between">
          <button
            type="button"
            class="px-3 py-1.5 text-xs font-medium rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
            @click="regenerate(setupTool)"
          >{{ $t('tools.regenerate') }}</button>
          <button
            type="button"
            class="px-4 py-1.5 text-xs font-semibold rounded-lg bg-primary-600 text-white hover:bg-primary-700"
            @click="closeSetup"
          >{{ $t('tools.done') }}</button>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppShell from '@/Components/Layout/AppShell.vue';

const { t } = useI18n();

const props = defineProps({
  tools: { type: Array, default: () => [] },
  hubUrl: { type: String, default: '' },
  ingestUrl: { type: String, default: '' },
  revealedKey: { type: String, default: null },
  revealedTool: { type: String, default: null },
});

const META = {
  audit: { initial: 'A', badge: 'bg-gradient-to-br from-indigo-500 to-indigo-700', desc: 'Enterprise readiness & the 5 maturity pillars (Umsatz, Gewinn, Ordnung, Einfluss, Vermächtnis).' },
  invoice: { initial: 'I', badge: 'bg-gradient-to-br from-emerald-500 to-emerald-700', desc: 'Revenue, outstanding invoices and cash-flow metrics from InvoiceMaker.' },
  easysop: { initial: 'E', badge: 'bg-gradient-to-br from-amber-500 to-amber-600', desc: 'Process compliance and SOP completion signals from EasySOP.' },
};

function meta(slug) {
  return META[slug] || { initial: (slug[0] || '?').toUpperCase(), badge: 'bg-gradient-to-br from-gray-500 to-gray-700', desc: '' };
}

const copied = ref(false);
const setupTool = ref(null);

// The key is only known right after connect/regenerate (revealed once).
const revealedKeyForSetup = computed(() => {
  if (!setupTool.value) return null;
  return props.revealedTool === setupTool.value.tool ? props.revealedKey : null;
});

const envSnippet = computed(() => {
  const key = revealedKeyForSetup.value || '<your-api-key>';
  return [
    '# Allocore Hub connection',
    'ALLOCORE_ENABLED=true',
    `ALLOCORE_HUB_URL=${props.hubUrl}`,
    `ALLOCORE_API_KEY=${key}`,
  ].join('\n');
});

function statusPill(tool) {
  if (!tool.connected) {
    return { label: t('tools.status_not_connected'), class: 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400', dot: 'bg-gray-400' };
  }
  if (!tool.enabled) {
    return { label: t('tools.status_disabled'), class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400', dot: 'bg-yellow-500' };
  }
  if (tool.last_synced_at) {
    return { label: t('tools.status_receiving'), class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400', dot: 'bg-green-500' };
  }
  return { label: t('tools.status_waiting'), class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', dot: 'bg-blue-500 animate-pulse' };
}

function copy(text) {
  navigator.clipboard?.writeText(text);
  copied.value = true;
  setTimeout(() => (copied.value = false), 2000);
}

function connect(tool) {
  router.post('/tools/connect', { tool: tool.tool }, {
    preserveScroll: true,
    onSuccess: () => openSetup(tool),
  });
}

function openSetup(tool) {
  setupTool.value = tool;
}

function closeSetup() {
  setupTool.value = null;
}

function regenerate(tool) {
  if (!confirm(t('tools.regenerate_confirm'))) return;
  router.post(`/tools/${tool.id}/regenerate`, {}, {
    preserveScroll: true,
    onSuccess: () => openSetup(tool),
  });
}

function toggle(tool) {
  router.post(`/tools/${tool.id}/toggle`, {}, { preserveScroll: true });
}

function disconnect(tool) {
  if (!confirm(t('tools.disconnect_confirm'))) return;
  router.delete(`/tools/${tool.id}`, { preserveScroll: true });
}

// Auto-open the setup modal right after a key is freshly generated.
onMounted(() => {
  if (props.revealedTool) {
    const tool = props.tools.find((x) => x.tool === props.revealedTool);
    if (tool) openSetup(tool);
  }
});
</script>
