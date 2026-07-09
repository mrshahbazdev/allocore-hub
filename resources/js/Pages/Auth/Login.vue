<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="$t('auth.login')" />

        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $t('auth.login_title') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $t('auth.login_subtitle') }}</p>
        </div>

        <div v-if="status" class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 px-3 py-2 text-sm font-medium text-green-600 dark:text-green-400">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" :value="$t('auth.email')" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" :value="$t('auth.password')" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $t('auth.remember_me') }}</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
                >
                    {{ $t('auth.forgot_password') }}
                </Link>
            </div>

            <button
                type="submit"
                class="w-full inline-flex justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ $t('auth.login') }}
            </button>

            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                {{ $t('auth.no_account') }}
                <Link :href="route('register')" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">{{ $t('auth.register') }}</Link>
            </p>
        </form>
    </GuestLayout>
</template>
