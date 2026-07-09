<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const locale = computed(() => page.props.locale || 'de');
</script>

<template>
    <div class="min-h-screen grid lg:grid-cols-2 bg-gray-50 dark:bg-gray-900">
        <!-- Brand panel -->
        <div class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 p-12 text-white">
            <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-black/10 blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center font-bold text-xl">A</div>
                <span class="text-lg font-semibold tracking-tight">{{ $t('app.name') }}</span>
            </div>

            <div class="relative space-y-6">
                <h1 class="text-3xl font-bold leading-tight max-w-sm">{{ $t('app.tagline') }}</h1>
                <ul class="space-y-4 text-sm text-white/90 max-w-sm">
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 h-5 w-5 flex-shrink-0 rounded-full bg-white/20 flex items-center justify-center text-[11px]">✓</span>
                        <span>{{ $t('auth.perk_hub') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 h-5 w-5 flex-shrink-0 rounded-full bg-white/20 flex items-center justify-center text-[11px]">✓</span>
                        <span>{{ $t('auth.perk_connect') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 h-5 w-5 flex-shrink-0 rounded-full bg-white/20 flex items-center justify-center text-[11px]">✓</span>
                        <span>{{ $t('auth.perk_team') }}</span>
                    </li>
                </ul>
            </div>

            <div class="relative text-xs text-white/60">© {{ new Date().getFullYear() }} Allocore</div>
        </div>

        <!-- Form panel -->
        <div class="flex flex-col items-center justify-center px-6 py-10 relative">
            <div class="absolute top-4 right-4 flex items-center gap-1 text-xs">
                <a
                    href="/locale/de"
                    class="px-2 py-1 rounded-md transition-colors"
                    :class="locale === 'de' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800'"
                >DE</a>
                <a
                    href="/locale/en"
                    class="px-2 py-1 rounded-md transition-colors"
                    :class="locale === 'en' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800'"
                >EN</a>
            </div>

            <div class="w-full max-w-sm">
                <div class="lg:hidden mb-8 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-primary-600 text-white flex items-center justify-center font-bold text-lg">A</div>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $t('app.name') }}</span>
                </div>

                <Link href="/" class="hidden lg:block text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 mb-6">← {{ $t('app.name') }}</Link>

                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
