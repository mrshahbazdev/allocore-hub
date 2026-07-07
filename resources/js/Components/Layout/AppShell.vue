<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-56 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 hidden lg:block">
      <div class="flex items-center gap-2 px-4 h-14 border-b border-gray-200 dark:border-gray-700">
        <div class="w-7 h-7 bg-primary-600 rounded-lg flex items-center justify-center">
          <span class="text-white text-xs font-bold">K</span>
        </div>
        <span class="font-bold text-gray-900 dark:text-white text-sm">KPI Tool</span>
      </div>
      <nav class="p-3 space-y-1">
        <a
          v-for="item in navItems"
          :key="item.href"
          :href="item.href"
          class="flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg transition-colors"
          :class="isActive(item.href) ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
        >
          <span v-html="item.icon" class="w-4 h-4 flex-shrink-0"></span>
          {{ item.label }}
        </a>
      </nav>
      <!-- Locale switcher -->
      <div class="absolute bottom-0 left-0 right-0 p-3 border-t border-gray-200 dark:border-gray-700">
        <div class="flex gap-1">
          <a
            href="/locale/de"
            class="flex-1 text-center py-1.5 text-xs font-medium rounded-md transition-colors"
            :class="locale === 'de' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'"
          >DE</a>
          <a
            href="/locale/en"
            class="flex-1 text-center py-1.5 text-xs font-medium rounded-md transition-colors"
            :class="locale === 'en' ? 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'"
          >EN</a>
        </div>
      </div>
    </aside>

    <!-- Mobile top bar -->
    <div class="lg:hidden fixed top-0 inset-x-0 z-30 h-14 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center px-4 gap-3">
      <button @click="mobileOpen = !mobileOpen" class="text-gray-600 dark:text-gray-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <span class="font-bold text-gray-900 dark:text-white text-sm">KPI Tool</span>
    </div>

    <!-- Mobile drawer -->
    <div v-if="mobileOpen" class="fixed inset-0 z-40 lg:hidden" @click="mobileOpen = false">
      <div class="absolute inset-0 bg-black/50"></div>
      <aside class="absolute inset-y-0 left-0 w-56 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700" @click.stop>
        <div class="flex items-center gap-2 px-4 h-14 border-b border-gray-200 dark:border-gray-700">
          <span class="font-bold text-gray-900 dark:text-white text-sm">KPI Tool</span>
        </div>
        <nav class="p-3 space-y-1">
          <a
            v-for="item in navItems"
            :key="item.href"
            :href="item.href"
            class="flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg transition-colors"
            :class="isActive(item.href) ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50'"
          >
            {{ item.label }}
          </a>
        </nav>
      </aside>
    </div>

    <!-- Main Content -->
    <main class="lg:pl-56 pt-14 lg:pt-0">
      <div class="p-4 sm:p-6 lg:p-8">
        <!-- Flash message -->
        <div v-if="flash?.success" class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400">
          {{ flash.success }}
        </div>
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const flash = computed(() => page.props.flash);
const mobileOpen = ref(false);

const navItems = computed(() => [
  { href: '/kpis/spreadsheet', label: t('nav.spreadsheet'), icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M.99 5.24A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25v9.5A2.25 2.25 0 0116.75 17H3.25A2.25 2.25 0 011 14.75v-9.5zm1.5 0v2.25h15v-2.25a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75zm15 3.75h-15v5.75c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V8.99zM6.5 9.75a.75.75 0 00-.75.75v2.5c0 .414.336.75.75.75h7a.75.75 0 00.75-.75v-2.5a.75.75 0 00-.75-.75h-7z" clip-rule="evenodd"/></svg>' },
  { href: '/kpis', label: t('nav.definitions'), icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 3.75a2 2 0 10-4 0 2 2 0 004 0zM17.25 4.5a.75.75 0 000-1.5h-5.5a.75.75 0 000 1.5h5.5zM5 3.75a3 3 0 115.133 2.1l1.413 1.413a.75.75 0 01-1.06 1.06L9.073 6.91A3 3 0 015 3.75zM4.75 12a.75.75 0 01.75-.75h9a.75.75 0 010 1.5h-9a.75.75 0 01-.75-.75zM4.75 15.5a.75.75 0 01.75-.75h9a.75.75 0 010 1.5h-9a.75.75 0 01-.75-.75z"/></svg>' },
  { href: '/kpis/catalog', label: t('kpi.catalog'), icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 16.82A7.462 7.462 0 0115 15.5c.71 0 1.396.098 2.046.282A.75.75 0 0018 15.06V4.56a.75.75 0 00-.546-.721A9.006 9.006 0 0015 3.5a8.98 8.98 0 00-4.25 1.065v12.255zM9.25 4.565A8.98 8.98 0 005 3.5a9.006 9.006 0 00-2.454.339A.75.75 0 002 4.56v10.5a.75.75 0 00.954.721A7.462 7.462 0 015 15.5c1.579 0 3.042.487 4.25 1.32V4.565z"/></svg>' },
]);

function isActive(href) {
  const path = typeof window !== 'undefined' ? window.location.pathname : '';
  if (href === '/kpis/spreadsheet') return path === '/kpis/spreadsheet';
  if (href === '/kpis/catalog') return path.startsWith('/kpis/catalog');
  if (href === '/kpis') return path === '/kpis' || (path.startsWith('/kpis/') && !path.includes('spreadsheet') && !path.includes('catalog'));
  return path === href;
}
</script>
