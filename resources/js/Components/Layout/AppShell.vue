<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-30 w-56 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 hidden lg:flex lg:flex-col">
      <div class="flex items-center gap-2 px-4 h-14 border-b border-gray-200 dark:border-gray-700">
        <div class="w-7 h-7 bg-primary-600 rounded-lg flex items-center justify-center">
          <span class="text-white text-xs font-bold">K</span>
        </div>
        <span class="font-bold text-gray-900 dark:text-white text-sm">KPI Tool</span>
      </div>
      <nav class="p-3 space-y-1 flex-1">
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

      <!-- Bottom: User + Locale -->
      <div class="border-t border-gray-200 dark:border-gray-700 p-3 space-y-3">
        <!-- Dark mode -->
        <button @click="toggleDark" class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 rounded-lg transition-colors">
          <svg v-if="!isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          {{ isDark ? $t('common.light_mode') : $t('common.dark_mode') }}
        </button>
        <!-- Locale -->
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
        <!-- User -->
        <div v-if="auth?.user" class="flex items-center gap-2 px-2">
          <div class="w-7 h-7 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-full flex items-center justify-center text-xs font-bold">
            {{ auth.user.name?.charAt(0)?.toUpperCase() }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ auth.user.name }}</div>
          </div>
          <a href="/profile" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </a>
        </div>
      </div>
    </aside>

    <!-- Mobile top bar -->
    <div class="lg:hidden fixed top-0 inset-x-0 z-30 h-14 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center px-4 gap-3">
      <button @click="mobileOpen = !mobileOpen" class="text-gray-600 dark:text-gray-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <span class="font-bold text-gray-900 dark:text-white text-sm">KPI Tool</span>
      <div class="ml-auto flex items-center gap-2">
        <button @click="toggleDark" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <svg v-if="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </button>
      </div>
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
        <div v-if="flash?.error" class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-sm text-red-700 dark:text-red-400">
          {{ flash.error }}
        </div>
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();
const locale = computed(() => page.props.locale || 'de');
const flash = computed(() => page.props.flash);
const auth = computed(() => page.props.auth);
const mobileOpen = ref(false);
const isDark = ref(false);

onMounted(() => {
  isDark.value = document.documentElement.classList.contains('dark') ||
    localStorage.getItem('theme') === 'dark' ||
    (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
  if (isDark.value) document.documentElement.classList.add('dark');
});

function toggleDark() {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

const navItems = computed(() => [
  { href: '/dashboard', label: t('nav.dashboard'), icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>' },
  { href: '/kpis/spreadsheet', label: t('nav.spreadsheet'), icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M.99 5.24A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25v9.5A2.25 2.25 0 0116.75 17H3.25A2.25 2.25 0 011 14.75v-9.5zm1.5 0v2.25h15v-2.25a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75zm15 3.75h-15v5.75c0 .414.336.75.75.75h13.5a.75.75 0 00.75-.75V8.99zM6.5 9.75a.75.75 0 00-.75.75v2.5c0 .414.336.75.75.75h7a.75.75 0 00.75-.75v-2.5a.75.75 0 00-.75-.75h-7z" clip-rule="evenodd"/></svg>' },
  { href: '/kpis', label: t('nav.definitions'), icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 3.75a2 2 0 10-4 0 2 2 0 004 0zM17.25 4.5a.75.75 0 000-1.5h-5.5a.75.75 0 000 1.5h5.5zM5 3.75a3 3 0 115.133 2.1l1.413 1.413a.75.75 0 01-1.06 1.06L9.073 6.91A3 3 0 015 3.75zM4.75 12a.75.75 0 01.75-.75h9a.75.75 0 010 1.5h-9a.75.75 0 01-.75-.75zM4.75 15.5a.75.75 0 01.75-.75h9a.75.75 0 010 1.5h-9a.75.75 0 01-.75-.75z"/></svg>' },
  { href: '/kpis/catalog', label: t('kpi.catalog'), icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 16.82A7.462 7.462 0 0115 15.5c.71 0 1.396.098 2.046.282A.75.75 0 0018 15.06V4.56a.75.75 0 00-.546-.721A9.006 9.006 0 0015 3.5a8.98 8.98 0 00-4.25 1.065v12.255zM9.25 4.565A8.98 8.98 0 005 3.5a9.006 9.006 0 00-2.454.339A.75.75 0 002 4.56v10.5a.75.75 0 00.954.721A7.462 7.462 0 015 15.5c1.579 0 3.042.487 4.25 1.32V4.565z"/></svg>' },
]);

function isActive(href) {
  const path = typeof window !== 'undefined' ? window.location.pathname : '';
  if (href === '/dashboard') return path === '/dashboard';
  if (href === '/kpis/spreadsheet') return path === '/kpis/spreadsheet';
  if (href === '/kpis/catalog') return path.startsWith('/kpis/catalog');
  if (href === '/kpis') return path === '/kpis' || (path.startsWith('/kpis/') && !path.includes('spreadsheet') && !path.includes('catalog'));
  return path === href;
}
</script>
