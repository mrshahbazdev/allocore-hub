<template>
  <AppShell>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('nav.tools') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Connect your Allocore tools. Each tool pushes its metrics to the hub using its API key.
        </p>
      </div>

      <!-- Revealed key banner (shown once after connect / regenerate) -->
      <div
        v-if="revealedKey"
        class="bg-amber-50 dark:bg-amber-900/20 border border-amber-300 dark:border-amber-700 rounded-xl p-4"
      >
        <div class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-2">
          API key for {{ revealedTool }} — copy it now, it is shown only once.
        </div>
        <div class="flex items-center gap-2">
          <code class="flex-1 text-xs bg-white dark:bg-gray-900 rounded px-3 py-2 font-mono break-all border border-amber-200 dark:border-amber-800">{{ revealedKey }}</code>
          <button
            type="button"
            class="px-3 py-2 text-xs font-medium rounded-lg bg-amber-600 text-white hover:bg-amber-700"
            @click="copy(revealedKey)"
          >{{ copied ? 'Copied' : 'Copy' }}</button>
        </div>
        <div class="mt-3 text-xs text-amber-700 dark:text-amber-400">
          Header: <code class="font-mono">X-Allocore-Api-Key: {{ revealedKey }}</code><br />
          Endpoint: <code class="font-mono">POST {{ ingestUrl }}</code>
        </div>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div
          v-for="tool in tools"
          :key="tool.tool"
          class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5"
        >
          <div class="flex items-start justify-between">
            <div>
              <div class="text-base font-semibold text-gray-900 dark:text-white">{{ tool.label }}</div>
              <div class="text-xs text-gray-400 font-mono">{{ tool.tool }}</div>
            </div>
            <span
              class="text-[10px] px-2 py-0.5 rounded-full font-medium"
              :class="{
                'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400': !tool.connected,
                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': tool.connected && tool.enabled,
                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400': tool.connected && !tool.enabled,
              }"
            >
              {{ !tool.connected ? 'Not connected' : (tool.enabled ? (tool.status || 'connected') : 'disabled') }}
            </span>
          </div>

          <div v-if="tool.connected" class="mt-3 text-xs text-gray-500 dark:text-gray-400 space-y-1">
            <div v-if="tool.base_url">URL: {{ tool.base_url }}</div>
            <div>Last sync: {{ tool.last_synced_at || '—' }}</div>
          </div>

          <div class="mt-4 flex flex-wrap gap-2">
            <button
              v-if="!tool.connected"
              type="button"
              class="px-3 py-1.5 text-xs font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700"
              @click="connect(tool)"
            >Connect</button>

            <template v-else>
              <button
                type="button"
                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600"
                @click="regenerate(tool)"
              >Regenerate key</button>
              <button
                type="button"
                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600"
                @click="toggle(tool)"
              >{{ tool.enabled ? 'Disable' : 'Enable' }}</button>
              <button
                type="button"
                class="px-3 py-1.5 text-xs font-medium rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100"
                @click="disconnect(tool)"
              >Disconnect</button>
            </template>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  tools: { type: Array, default: () => [] },
  ingestUrl: { type: String, default: '' },
  revealedKey: { type: String, default: null },
  revealedTool: { type: String, default: null },
});

const copied = ref(false);

function copy(text) {
  navigator.clipboard?.writeText(text);
  copied.value = true;
  setTimeout(() => (copied.value = false), 2000);
}

function connect(tool) {
  router.post('/tools/connect', { tool: tool.tool }, { preserveScroll: true });
}

function regenerate(tool) {
  if (!confirm('Regenerate the API key? The old key stops working immediately.')) return;
  router.post(`/tools/${tool.id}/regenerate`, {}, { preserveScroll: true });
}

function toggle(tool) {
  router.post(`/tools/${tool.id}/toggle`, {}, { preserveScroll: true });
}

function disconnect(tool) {
  if (!confirm('Disconnect this tool? Its API key will be revoked.')) return;
  router.delete(`/tools/${tool.id}`, { preserveScroll: true });
}
</script>
