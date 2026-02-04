<template>
    <UserLayout>
        <div class="space-y-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">My Appointments</h1>
                    <p class="text-[#86868b] mt-1">View and manage your appointments</p>
                </div>
                <router-link to="/appointments/new" class="btn-primary">New Appointment</router-link>
            </div>

            <div class="card">
                <div class="px-6 py-4 border-b border-black/5">
                    <div class="flex gap-2">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="activeTab = tab.value"
                            class="nav-pill"
                            :class="{ 'active': activeTab === tab.value }"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <div v-if="loading" class="p-10 text-center">
                    <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin mx-auto"></div>
                </div>
                <div v-else-if="filteredAppointments.length === 0" class="p-10 text-center">
                    <div class="w-16 h-16 bg-[#f5f5f7] rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-[#86868b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                            />
                        </svg>
                    </div>
                    <p class="text-[#86868b]">No {{ activeTab }} appointments</p>
                </div>
                <div v-else class="divide-y divide-black/5">
                    <div
                        v-for="appointment in filteredAppointments"
                        :key="appointment.id"
                        class="px-6 py-5 flex items-center justify-between hover:bg-black/[0.02] transition-colors"
                    >
                        <div class="flex-1">
                            <p class="font-medium text-[#1d1d1f]">{{ formatDate(appointment.appointment_date) }}</p>
                            <p class="text-sm text-[#86868b] mt-1">
                                {{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}
                            </p>
                            <p v-if="appointment.notes" class="text-sm text-[#86868b] mt-2">{{ appointment.notes }}</p>
                            <p v-if="appointment.rejection_reason" class="text-sm text-[#ff3b30] mt-2">
                                Reason: {{ appointment.rejection_reason }}
                            </p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span :class="statusBadge(appointment.status)">{{ appointment.status }}</span>
                            <button
                                v-if="appointment.status === 'pending' && !isPast(appointment.appointment_date)"
                                @click="cancelAppointment(appointment.id)"
                                class="text-[#ff3b30] text-sm font-medium hover:underline"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAppointmentStore } from '../../stores';
import UserLayout from '../../layouts/UserLayout.vue';

const appointmentStore = useAppointmentStore();
const loading = ref(true);
const activeTab = ref('all');

const tabs = [
    { label: 'All', value: 'all' },
    { label: 'Pending', value: 'pending' },
    { label: 'Approved', value: 'approved' },
    { label: 'Rejected', value: 'rejected' },
];

const filteredAppointments = computed(() => {
    if (activeTab.value === 'all') return appointmentStore.appointments;
    return appointmentStore.appointments.filter(a => a.status === activeTab.value);
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const isPast = (date) => new Date(date) < new Date(new Date().toDateString());

const statusBadge = (status) => {
    const badges = {
        pending: 'badge badge-pending',
        approved: 'badge badge-approved',
        rejected: 'badge badge-rejected',
    };
    return badges[status] || 'badge';
};

const cancelAppointment = async (id) => {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        try {
            await appointmentStore.deleteAppointment(id);
        } catch (e) {
            alert(e.response?.data?.message || 'Failed to cancel');
        }
    }
};

onMounted(async () => {
    try {
        await appointmentStore.fetchAppointments();
    } finally {
        loading.value = false;
    }
});
</script>
