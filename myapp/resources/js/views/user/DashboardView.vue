<template>
    <UserLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Welcome, {{ authStore.user?.name }}</h1>
                <p class="mt-1 text-sm text-gray-600">Here's an overview of your appointments</p>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <span class="text-yellow-600 text-xl">⏳</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Pending</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ appointmentStore.pendingCount }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 text-xl">✓</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Approved</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ appointmentStore.approvedAppointments.length }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <span class="text-red-600 text-xl">✕</span>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Rejected</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ appointmentStore.rejectedAppointments.length }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="appointmentStore.pendingCount >= 2" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <p class="text-sm text-yellow-700">
                    You have reached the maximum of 2 pending appointments. Please wait for your appointments to be processed before requesting new ones.
                </p>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Upcoming Appointments</h3>
                </div>
                <div v-if="loading" class="p-6 text-center text-gray-500">Loading...</div>
                <div v-else-if="upcomingAppointments.length === 0" class="p-6 text-center text-gray-500">
                    No upcoming appointments
                </div>
                <ul v-else class="divide-y divide-gray-200">
                    <li v-for="appointment in upcomingAppointments" :key="appointment.id" class="px-4 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ formatDate(appointment.appointment_date) }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}
                                </p>
                            </div>
                            <span :class="statusClass(appointment.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ appointment.status }}
                            </span>
                        </div>
                    </li>
                </ul>
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
        await appointmentStore.fetchAppointments();
    } finally {
        loading.value = false;
    }
});
</script>
