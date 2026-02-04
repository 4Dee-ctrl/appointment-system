<template>
    <AdminLayout>
        <div class="space-y-8">
            <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">Dashboard</h1>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin"></div>
            </div>

            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div class="stat-card">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-[#ff9500]/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#ff9500]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[#86868b] text-sm">Pending</p>
                                <p class="text-2xl font-semibold text-[#1d1d1f]">{{ dashboard?.stats?.pending_appointments || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-[#34c759]/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#34c759]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[#86868b] text-sm">Approved</p>
                                <p class="text-2xl font-semibold text-[#1d1d1f]">{{ dashboard?.stats?.approved_appointments || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-[#0071e3]/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#0071e3]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[#86868b] text-sm">Today</p>
                                <p class="text-2xl font-semibold text-[#1d1d1f]">{{ dashboard?.stats?.today_appointments || 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-[#ff3b30]/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-[#ff3b30]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-[#86868b] text-sm">Conflicts</p>
                                <p class="text-2xl font-semibold text-[#1d1d1f]">{{ dashboard?.stats?.conflict_attempts_today || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="card">
                        <div class="px-6 py-5 border-b border-black/5">
                            <h2 class="text-lg font-semibold text-[#1d1d1f]">Recent Appointments</h2>
                        </div>
                        <div v-if="dashboard?.recent_appointments?.length" class="divide-y divide-black/5 max-h-80 overflow-y-auto">
                            <div v-for="appt in dashboard.recent_appointments" :key="appt.id" class="px-6 py-4 flex items-center justify-between hover:bg-black/[0.02]">
                                <div>
                                    <p class="font-medium text-[#1d1d1f]">{{ appt.user?.name }}</p>
                                    <p class="text-sm text-[#86868b]">{{ formatDate(appt.appointment_date) }}</p>
                                </div>
                                <span :class="statusBadge(appt.status)">{{ appt.status }}</span>
                            </div>
                        </div>
                        <div v-else class="p-6 text-center text-[#86868b]">No recent appointments</div>
                    </div>

                    <div class="card">
                        <div class="px-6 py-5 border-b border-black/5">
                            <h2 class="text-lg font-semibold text-[#1d1d1f]">Recent Conflicts</h2>
                        </div>
                        <div v-if="dashboard?.recent_conflicts?.length" class="divide-y divide-black/5 max-h-80 overflow-y-auto">
                            <div v-for="conflict in dashboard.recent_conflicts" :key="conflict.id" class="px-6 py-4 hover:bg-black/[0.02]">
                                <p class="font-medium text-[#1d1d1f]">{{ conflict.user?.name }}</p>
                                <p class="text-sm text-[#ff3b30]">{{ conflict.reason }}</p>
                                <p class="text-xs text-[#86868b] mt-1">{{ formatDate(conflict.attempted_date) }}</p>
                            </div>
                        </div>
                        <div v-else class="p-6 text-center text-[#86868b]">No recent conflicts</div>
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
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const statusBadge = (status) => {
    const badges = {
        pending: 'badge badge-pending',
        approved: 'badge badge-approved',
        rejected: 'badge badge-rejected',
    };
    return badges[status] || 'badge';
};

onMounted(async () => {
    try {
        dashboard.value = await adminStore.fetchDashboard();
    } finally {
        loading.value = false;
    }
});
</script>
