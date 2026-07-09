<template>
  <AppShell>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('nav.team') }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Invite users and choose which KPIs each member can see.
        </p>
      </div>

      <div class="grid gap-6 lg:grid-cols-3">
        <!-- Create member -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Add user</h3>
          <form class="space-y-3" @submit.prevent="createUser">
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Name</label>
              <input v-model="form.name" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm" />
              <div v-if="form.errors.name" class="text-xs text-red-500 mt-1">{{ form.errors.name }}</div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Email</label>
              <input v-model="form.email" type="email" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm" />
              <div v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Role</label>
              <select v-model="form.role" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                <option value="member">Member (sees assigned KPIs)</option>
                <option value="manager">Manager (full access)</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Password</label>
              <input v-model="form.password" type="password" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm" />
              <div v-if="form.errors.password" class="text-xs text-red-500 mt-1">{{ form.errors.password }}</div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Confirm password</label>
              <input v-model="form.password_confirmation" type="password" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm" />
            </div>
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full px-3 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50"
            >Add user</button>
          </form>
        </div>

        <!-- Members list -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Users ({{ members.length }})</h3>
          </div>
          <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
            <div v-for="m in members" :key="m.id" class="p-4">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ m.name }}</div>
                  <div class="text-xs text-gray-400">{{ m.email }}</div>
                </div>
                <div class="flex items-center gap-2">
                  <span
                    class="text-[10px] px-2 py-0.5 rounded-full font-medium capitalize"
                    :class="{
                      'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': m.role === 'owner',
                      'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400': m.role === 'manager',
                      'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300': m.role === 'member',
                    }"
                  >{{ m.role }}</span>
                  <button
                    v-if="m.role !== 'owner'"
                    type="button"
                    class="text-xs text-red-500 hover:text-red-700"
                    @click="removeUser(m)"
                  >Remove</button>
                </div>
              </div>

              <!-- KPI assignment for members -->
              <div v-if="m.role === 'member'" class="mt-3">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Visible KPIs</div>
                <div class="flex flex-wrap gap-2">
                  <label
                    v-for="kpi in kpis"
                    :key="kpi.id"
                    class="flex items-center gap-1.5 text-xs px-2 py-1 rounded-lg border cursor-pointer"
                    :class="selection[m.id] && selection[m.id].includes(kpi.id)
                      ? 'border-primary-400 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300'
                      : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300'"
                  >
                    <input
                      type="checkbox"
                      class="hidden"
                      :value="kpi.id"
                      v-model="selection[m.id]"
                    />
                    {{ locale === 'de' ? kpi.name_de : kpi.name_en }}
                  </label>
                </div>
                <button
                  type="button"
                  class="mt-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600"
                  @click="saveAssignments(m)"
                >Save assignments</button>
              </div>
              <div v-else class="mt-2 text-xs text-gray-400">Full access to all company KPIs.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

const props = defineProps({
  members: { type: Array, default: () => [] },
  kpis: { type: Array, default: () => [] },
});

const form = useForm({
  name: '',
  email: '',
  role: 'member',
  password: '',
  password_confirmation: '',
});

const selection = reactive({});
props.members.forEach((m) => {
  selection[m.id] = [...(m.assigned_kpi_ids || [])];
});

function createUser() {
  form.post('/team', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
}

function saveAssignments(member) {
  router.post(`/team/${member.id}/assign`, { kpi_ids: selection[member.id] || [] }, { preserveScroll: true });
}

function removeUser(member) {
  if (!confirm(`Remove ${member.name}?`)) return;
  router.delete(`/team/${member.id}`, { preserveScroll: true });
}
</script>
