<template>
    <AdminLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-semibold text-gray-900">Pending Appointments</h1>

            <div v-if="loading" class="text-center text-gray-500">Loading...</div>

            <div v-else-if="adminStore.pendingAppointments.length === 0" class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                No pending appointments
            </div>

            <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                <ul class="divide-y divide-gray-200">
                    <li v-for="appointment in adminStore.pendingAppointments" :key="appointment.id" class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ appointment.user?.name }}</p>
                                <p class="text-sm text-gray-500">{{ appointment.user?.email }}</p>
                                <p class="text-sm text-gray-700 mt-1">
                                    {{ formatDate(appointment.appointment_date) }} at
                                    {{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}
                                </p>
                                <p v-if="appointment.notes" class="text-sm text-gray-400 mt-1">
                                    Notes: {{ appointment.notes }}
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    @click="approve(appointment.id)"
                                    :disabled="processing === appointment.id"
                                    class="px-3 py-1 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50"
                                >
                                    Approve
                                </button>
                                <button
                                    @click="openRejectModal(appointment)"
                                    :disabled="processing === appointment.id"
                                    class="px-3 py-1 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50"
                                >
                                    Reject
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Appointment</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason (optional)</label>
                        <textarea
                            v-model="rejectReason"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Enter rejection reason..."
                        ></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeRejectModal"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmReject"
                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700"
                        >
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAdminStore } from '../../stores';
import AdminLayout from '../../layouts/AdminLayout.vue';

const adminStore = useAdminStore();
const loading = ref(true);
const processing = ref(null);
const showRejectModal = ref(false);
const selectedAppointment = ref(null);
const rejectReason = ref('');

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

const approve = async (id) => {
    processing.value = id;
    try {
        await adminStore.approveAppointment(id);
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to approve');
    } finally {
        processing.value = null;
    }
};

const openRejectModal = (appointment) => {
    selectedAppointment.value = appointment;
    rejectReason.value = '';
    showRejectModal.value = true;
};

const closeRejectModal = () => {
    showRejectModal.value = false;
    selectedAppointment.value = null;
    rejectReason.value = '';
};

const confirmReject = async () => {
    if (!selectedAppointment.value) return;
    processing.value = selectedAppointment.value.id;
    try {
        await adminStore.rejectAppointment(selectedAppointment.value.id, rejectReason.value);
        closeRejectModal();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to reject');
    } finally {
        processing.value = null;
    }
};

onMounted(async () => {
    try {
        await adminStore.fetchPendingAppointments();
    } finally {
        loading.value = false;
    }
});
</script>
