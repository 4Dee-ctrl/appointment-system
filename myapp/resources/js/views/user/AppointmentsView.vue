<template>
    <UserLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">My Appointments</h1>
                <router-link
                    to="/appointments/new"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
                >
                    New Appointment
                </router-link>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <div class="flex space-x-4">
                        <button
                            v-for="tab in tabs"
                            :key="tab.value"
                            @click="activeTab = tab.value"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-md',
                                activeTab === tab.value
                                    ? 'bg-indigo-100 text-indigo-700'
                                    : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                </div>

                <div v-if="loading" class="p-6 text-center text-gray-500">Loading...</div>
                <div v-else-if="filteredAppointments.length === 0" class="p-6 text-center text-gray-500">
                    No {{ activeTab }} appointments found
                </div>
                <ul v-else class="divide-y divide-gray-200">
                    <li v-for="appointment in filteredAppointments" :key="appointment.id" class="px-4 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ formatDate(appointment.appointment_date) }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}
                                </p>
                                <p v-if="appointment.notes" class="text-sm text-gray-400 mt-1">
                                    {{ appointment.notes }}
                                </p>
                                <p v-if="appointment.rejection_reason" class="text-sm text-red-500 mt-1">
                                    Reason: {{ appointment.rejection_reason }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span :class="statusClass(appointment.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ appointment.status }}
                                </span>
                                <button
                                    v-if="appointment.status === 'pending' && !isPast(appointment.appointment_date)"
                                    @click="cancelAppointment(appointment.id)"
                                    class="text-red-600 hover:text-red-800 text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
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
    if (activeTab.value === 'all') {
        return appointmentStore.appointments;
    }
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

const isPast = (date) => {
    return new Date(date) < new Date(new Date().toDateString());
};

const statusClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const cancelAppointment = async (id) => {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        try {
            await appointmentStore.deleteAppointment(id);
        } catch (e) {
            alert(e.response?.data?.message || 'Failed to cancel appointment');
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
