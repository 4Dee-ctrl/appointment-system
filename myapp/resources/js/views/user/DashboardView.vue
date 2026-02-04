<template>
    <UserLayout>
        <div class="space-y-8">
            <div>
                <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">Hello, {{ authStore.user?.name }}</h1>
                <p class="text-[#86868b] mt-1">Here's your appointment overview</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="stat-card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#ff9500]/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#ff9500]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[#86868b] text-sm">Pending</p>
                            <p class="text-2xl font-semibold text-[#1d1d1f]">{{ appointmentStore.pendingCount }}</p>
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
                            <p class="text-2xl font-semibold text-[#1d1d1f]">{{ appointmentStore.approvedAppointments.length }}</p>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-[#ff3b30]/10 flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#ff3b30]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[#86868b] text-sm">Rejected</p>
                            <p class="text-2xl font-semibold text-[#1d1d1f]">{{ appointmentStore.rejectedAppointments.length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="appointmentStore.pendingCount >= 2" class="bg-[#ff9500]/10 border border-[#ff9500]/20 rounded-2xl p-5">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-[#ff9500]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm text-[#1d1d1f]">You have reached the maximum of 2 pending appointments.</p>
                </div>
            </div>

            <div class="card">
                <div class="px-6 py-5 border-b border-black/5">
                    <h2 class="text-lg font-semibold text-[#1d1d1f]">Upcoming Appointments</h2>
                </div>
                <div v-if="loading" class="p-10 text-center text-[#86868b]">
                    <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin mx-auto"></div>
                </div>
                <div v-else-if="upcomingAppointments.length === 0" class="p-10 text-center">
                    <div class="w-16 h-16 bg-[#f5f5f7] rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-[#86868b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-[#86868b]">No upcoming appointments</p>
                    <router-link to="/appointments/new" class="btn-primary inline-block mt-4">Book an Appointment</router-link>
                </div>
                <div v-else class="divide-y divide-black/5">
                    <div v-for="appointment in upcomingAppointments" :key="appointment.id" class="px-6 py-4 flex items-center justify-between hover:bg-black/[0.02] transition-colors">
                        <div>
                            <p class="font-medium text-[#1d1d1f]">{{ formatDate(appointment.appointment_date) }}</p>
                            <p class="text-sm text-[#86868b] mt-1">{{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}</p>
                        </div>
                        <span :class="statusBadge(appointment.status)">{{ appointment.status }}</span>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAuthStore, useAppointmentStore } from '../../stores';
import UserLayout from '../../layouts/UserLayout.vue';

const authStore = useAuthStore();
const appointmentStore = useAppointmentStore();
const loading = ref(true);

const upcomingAppointments = computed(() => {
    const today = new Date().toISOString().split('T')[0];
    return appointmentStore.appointments
        .filter(a => a.appointment_date >= today && a.status !== 'rejected')
        .sort((a, b) => new Date(a.appointment_date) - new Date(b.appointment_date))
        .slice(0, 5);
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
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
        await appointmentStore.fetchAppointments();
    } finally {
        loading.value = false;
    }
});
</script>
