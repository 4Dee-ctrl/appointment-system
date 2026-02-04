<template>
    <AdminLayout>
        <div class="space-y-8">
            <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">Pending Requests</h1>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div v-else-if="adminStore.pendingAppointments.length === 0" class="card p-10 text-center">
                <div class="w-16 h-16 bg-[#f5f5f7] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#86868b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-[#86868b]">No pending appointments</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="appointment in adminStore.pendingAppointments" :key="appointment.id" class="card p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#0071e3]/10 flex items-center justify-center">
                                    <span class="text-[#0071e3] font-semibold">{{ appointment.user?.name?.charAt(0) }}</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-[#1d1d1f]">{{ appointment.user?.name }}</p>
                                    <p class="text-sm text-[#86868b]">{{ appointment.user?.email }}</p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center gap-6">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#86868b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm text-[#1d1d1f]">{{ formatDate(appointment.appointment_date) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#86868b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm text-[#1d1d1f]">{{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}</span>
                                </div>
                            </div>
                            <p v-if="appointment.notes" class="mt-3 text-sm text-[#86868b]">{{ appointment.notes }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="approve(appointment.id)" :disabled="processing === appointment.id" class="btn-success">Approve</button>
                            <button @click="openRejectModal(appointment)" :disabled="processing === appointment.id" class="btn-danger">Reject</button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="showRejectModal" class="modal-overlay" @click.self="closeRejectModal">
                <div class="modal-content">
                    <h3 class="text-xl font-semibold text-[#1d1d1f] mb-6">Reject Appointment</h3>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Reason (optional)</label>
                        <textarea v-model="rejectReason" rows="3" class="input-field resize-none" placeholder="Enter rejection reason..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button @click="closeRejectModal" class="btn-secondary">Cancel</button>
                        <button @click="confirmReject" class="btn-danger">Reject</button>
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
    return new Date(date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
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
