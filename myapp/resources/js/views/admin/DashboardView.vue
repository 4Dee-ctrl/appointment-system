<template>
    <AdminLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-semibold text-gray-900">Admin Dashboard</h1>

            <div v-if="loading" class="text-center text-gray-500">Loading...</div>

            <template v-else>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                    <span class="text-yellow-600 text-xl">⏳</span>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500">Pending</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ dashboard?.stats?.pending_appointments || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                    <span class="text-green-600 text-xl">✓</span>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500">Approved</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ dashboard?.stats?.approved_appointments || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                    <span class="text-blue-600 text-xl">📅</span>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500">Today</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ dashboard?.stats?.today_appointments || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                                    <span class="text-red-600 text-xl">⚠</span>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500">Conflicts Today</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ dashboard?.stats?.conflict_attempts_today || 0 }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Appointments</h3>
                        </div>
                        <ul v-if="dashboard?.recent_appointments?.length" class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            <li v-for="appt in dashboard.recent_appointments" :key="appt.id" class="px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ appt.user?.name }}</p>
                                        <p class="text-sm text-gray-500">{{ formatDate(appt.appointment_date) }}</p>
                                    </div>
                                    <span :class="statusClass(appt.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                        {{ appt.status }}
                                    </span>
                                </div>
                            </li>
                        </ul>
                        <div v-else class="p-4 text-center text-gray-500">No recent appointments</div>
                    </div>

                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Conflicts</h3>
                        </div>
                        <ul v-if="dashboard?.recent_conflicts?.length" class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            <li v-for="conflict in dashboard.recent_conflicts" :key="conflict.id" class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ conflict.user?.name }}</p>
                                    <p class="text-sm text-gray-500">{{ conflict.reason }}</p>
                                    <p class="text-xs text-gray-400">{{ formatDate(conflict.attempted_date) }}</p>
                                </div>
                            </li>
                        </ul>
                        <div v-else class="p-4 text-center text-gray-500">No recent conflicts</div>
                    </div>
                </div>
            </template>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAdminStore } from '../../stores';
import AdminLayout from '../../layouts/AdminLayout.vue';

const adminStore = useAdminStore();
const loading = ref(true);
const dashboard = ref(null);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const statusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(async () => {
    try {
        dashboard.value = await adminStore.fetchDashboard();
    } finally {
        loading.value = false;
    }
});
</script>
