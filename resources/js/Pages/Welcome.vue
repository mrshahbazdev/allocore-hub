<template>
  <Head title="KPI Tool — Professional KPI Management" />
  <div class="min-h-screen bg-white dark:bg-gray-950">
    <!-- Navbar -->
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/80 dark:bg-gray-950/80 backdrop-blur-lg border-b border-gray-100 dark:border-gray-800">
      <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
            <span class="text-white text-sm font-bold">K</span>
          </div>
          <span class="font-bold text-gray-900 dark:text-white text-lg">KPI Tool</span>
        </div>
        <div class="flex items-center gap-3">
          <a v-if="auth?.user" href="/dashboard" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            Dashboard
          </a>
          <template v-else>
            <a href="/login" class="px-4 py-2 text-gray-600 dark:text-gray-300 text-sm font-medium hover:text-gray-900 dark:hover:text-white transition-colors">
              Login
            </a>
            <a href="/register" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
              Register
            </a>
          </template>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="pt-32 pb-20 px-6">
      <div class="max-w-4xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-full text-xs font-medium text-blue-700 dark:text-blue-400 mb-6">
          <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a.75.75 0 01.75.75v5.59l1.95-2.1a.75.75 0 111.1 1.02l-3.25 3.5a.75.75 0 01-1.1 0L6.2 7.26a.75.75 0 011.1-1.02l1.95 2.1V2.75A.75.75 0 0110 2z"/><path d="M5.273 4.5a1.25 1.25 0 00-1.205.918l-1.523 5.52c-.006.02-.01.041-.015.062H6a1.25 1.25 0 011.177.833l.243.726a.25.25 0 00.236.166h4.688a.25.25 0 00.236-.166l.244-.726A1.25 1.25 0 0114 11h3.47a1.318 1.318 0 00-.015-.062l-1.523-5.52a1.25 1.25 0 00-1.205-.918h-.977a.75.75 0 010-1.5h.977a2.75 2.75 0 012.651 2.019l1.523 5.52c.066.239.099.485.099.732V15a2.75 2.75 0 01-2.75 2.75H3.75A2.75 2.75 0 011 15v-3.21c0-.247.033-.493.099-.732l1.523-5.52A2.75 2.75 0 015.273 3.5h.977a.75.75 0 010 1.5h-.977z"/></svg>
          Bilingual DE/EN
        </div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 dark:text-white leading-tight">
          Track Your <span class="text-blue-600">KPIs</span><br class="hidden sm:block" />
          Like a Pro
        </h1>
        <p class="mt-6 text-lg text-gray-500 dark:text-gray-400 max-w-2xl mx-auto leading-relaxed">
          Professional KPI management with Excel-style spreadsheets, interactive charts, automated target generation, and real-time status tracking. Built for teams that take performance seriously.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
          <a href="/register" class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-600/25">
            Get Started Free
          </a>
          <a href="/login" class="w-full sm:w-auto px-8 py-3.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            Sign In
          </a>
        </div>
      </div>
    </section>

    <!-- Features Grid -->
    <section class="py-20 px-6 bg-gray-50 dark:bg-gray-900/50">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Everything You Need</h2>
          <p class="mt-3 text-gray-500 dark:text-gray-400">Powerful features to manage and monitor your business KPIs</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="feature in features" :key="feature.title" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:border-blue-200 dark:hover:border-blue-800 transition-all duration-300">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4" :class="feature.iconBg">
              <span v-html="feature.icon" class="w-5 h-5" :class="feature.iconColor"></span>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ feature.title }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">{{ feature.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 px-6">
      <div class="max-w-5xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">How It Works</h2>
          <p class="mt-3 text-gray-500 dark:text-gray-400">Get up and running in minutes</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div v-for="(step, i) in steps" :key="i" class="text-center">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-lg font-bold mx-auto mb-4">
              {{ i + 1 }}
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ step.title }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section class="py-16 px-6 bg-blue-600">
      <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div v-for="stat in stats" :key="stat.label">
          <div class="text-3xl font-bold text-white">{{ stat.value }}</div>
          <div class="text-sm text-blue-200 mt-1">{{ stat.label }}</div>
        </div>
      </div>
    </section>

    <!-- KPI Categories -->
    <section class="py-20 px-6">
      <div class="max-w-5xl mx-auto">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white">35+ Pre-Built KPI Templates</h2>
          <p class="mt-3 text-gray-500 dark:text-gray-400">Start with professionally crafted KPI definitions across 6 categories</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          <div v-for="cat in categories" :key="cat.name" class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-700 transition-colors">
            <div class="text-2xl mb-2">{{ cat.emoji }}</div>
            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ cat.name }}</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ cat.count }} KPIs</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-6 bg-gray-50 dark:bg-gray-900/50">
      <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Ready to Track Your KPIs?</h2>
        <p class="mt-4 text-gray-500 dark:text-gray-400">Create your account and start monitoring your business performance in minutes.</p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
          <a href="/register" class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors shadow-lg shadow-blue-600/25">
            Create Free Account
          </a>
          <a href="/login" class="w-full sm:w-auto px-8 py-3.5 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-colors">
            Sign In
          </a>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 px-6 border-t border-gray-200 dark:border-gray-800">
      <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 bg-blue-600 rounded-md flex items-center justify-center">
            <span class="text-white text-[10px] font-bold">K</span>
          </div>
          <span class="text-sm text-gray-500 dark:text-gray-400">KPI Tool</span>
        </div>
        <div class="text-sm text-gray-400 dark:text-gray-500">
          Built with Laravel, Vue.js & Tailwind CSS
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const auth = computed(() => page.props.auth);

const features = [
  {
    title: 'Excel-Style Spreadsheet',
    desc: 'Monthly grid with inline editing for Actuals & Targets. Auto-calculated Difference and % Deviation with color-coded indicators.',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M.99 5.24A2.25 2.25 0 013.25 3h13.5A2.25 2.25 0 0119 5.25v9.5A2.25 2.25 0 0116.75 17H3.25A2.25 2.25 0 011 14.75v-9.5zm1.5 0v2.25h15v-2.25a.75.75 0 00-.75-.75H3.25a.75.75 0 00-.75.75z" clip-rule="evenodd"/></svg>',
    iconBg: 'bg-green-100 dark:bg-green-900/30',
    iconColor: 'text-green-600 dark:text-green-400',
  },
  {
    title: 'Interactive Dashboard',
    desc: 'Summary cards, status doughnut chart, monthly bar chart, top KPIs, and recent entries — all at a glance.',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>',
    iconBg: 'bg-blue-100 dark:bg-blue-900/30',
    iconColor: 'text-blue-600 dark:text-blue-400',
  },
  {
    title: 'Chart.js Visualization',
    desc: 'Trend line charts on KPI detail pages with color-coded status points showing performance over time.',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>',
    iconBg: 'bg-purple-100 dark:bg-purple-900/30',
    iconColor: 'text-purple-600 dark:text-purple-400',
  },
  {
    title: 'CSV Import & Export',
    desc: 'Export your spreadsheet data as CSV. Bulk-import KPI values per definition with a simple CSV upload.',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>',
    iconBg: 'bg-amber-100 dark:bg-amber-900/30',
    iconColor: 'text-amber-600 dark:text-amber-400',
  },
  {
    title: 'Target Generator',
    desc: 'Auto-generate monthly targets with a custom growth rate formula — just like the original Excel tool\'s ~1% monthly growth.',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>',
    iconBg: 'bg-rose-100 dark:bg-rose-900/30',
    iconColor: 'text-rose-600 dark:text-rose-400',
  },
  {
    title: 'Dark Mode',
    desc: 'Full dark mode support with a one-click toggle. Persists across sessions via localStorage.',
    icon: '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>',
    iconBg: 'bg-gray-100 dark:bg-gray-700',
    iconColor: 'text-gray-600 dark:text-gray-300',
  },
];

const steps = [
  { title: 'Define Your KPIs', desc: 'Create KPI definitions with formulas, units, categories, and thresholds — or pick from 35+ ready-made templates.' },
  { title: 'Enter Your Data', desc: 'Use the Excel-style spreadsheet or KPI detail page to enter actual values. Import in bulk via CSV.' },
  { title: 'Monitor & Analyze', desc: 'View dashboard summaries, trend charts, and status indicators. Export reports as CSV anytime.' },
];

const stats = [
  { value: '35+', label: 'KPI Templates' },
  { value: '6', label: 'Categories' },
  { value: 'DE/EN', label: 'Bilingual' },
  { value: '100%', label: 'Open Source' },
];

const categories = [
  { name: 'Strategic', count: 5, emoji: '🎯' },
  { name: 'Sales', count: 6, emoji: '💰' },
  { name: 'Operations', count: 6, emoji: '⚙️' },
  { name: 'Marketing', count: 6, emoji: '📢' },
  { name: 'Financial', count: 6, emoji: '📊' },
  { name: 'HR', count: 6, emoji: '👥' },
];
</script>
