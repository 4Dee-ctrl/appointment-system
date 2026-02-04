<template>
    <AdminLayout>
        <div class="space-y-8">
            <div class="flex justify-between items-center">
                <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">Appointment History</h1>
                <select v-model="statusFilter" @change="loadHistory" class="input-field w-40">
                    <option value="">All</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div v-else-if="appointments.length === 0" class="card p-10 text-center">
                <p class="text-[#86868b]">No history found</p>
            </div>

            <div v-else class="card overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-black/5">
                            <th class="table-header text-left">User</th>
                            <th class="table-header text-left">Date</th>
                            <th class="table-header text-left">Time</th>
                            <th class="table-header text-left">Status</th>
                            <th class="table-header text-left">Processed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <tr v-for="appointment in appointments" :key="appointment.id" class="hover:bg-black/[0.02]">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#1d1d1f]">{{ appointment.user?.name }}</p>
                                <p class="text-sm text-[#86868b]">{{ appointment.user?.email }}</p>
                            </td>
                            <td class="px-6 py-4 text-[#1d1d1f]">{{ formatDate(appointment.appointment_date) }}</td>
                            <td class="px-6 py-4 text-[#86868b]">{{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}</td>
                            <td class="px-6 py-4">
                                <span :class="statusBadge(appointment.status)">{{ appointment.status }}</span>
                                <p v-if="appointment.rejection_reason" class="text-xs text-[#ff3b30] mt-1">{{ appointment.rejection_reason }}</p>
                            </td>
                            <td class="px-6 py-4 text-[#86868b]">{{ formatDateTime(appointment.approved_at || appointment.rejected_at) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="pagination" class="px-6 py-4 border-t border-black/5 flex justify-between items-center">
                    <span class="text-sm text-[#86868b]">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                    <div class="flex gap-2">
                        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="btn-secondary text-sm py-2">Previous</button>
                        <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="btn-secondary text-sm py-2">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { adminService } from '../../services';
import AdminLayout from '../../layouts/AdminLayout.vue';

const loading = ref(true);
const appointments = ref([]);
const pagination = ref(null);
const statusFilter = ref('');
const currentPage = ref(1);

const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const formatDateTime = (datetime) => datetime ? new Date(datetime).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';

const statusBadge = (status) => {
    const badges = { approved: 'badge badge-approved', rejected: 'badge badge-rejected' };
    return badges[status] || 'badge';
};

const loadHistory = async () => {
    loading.value = true;
    try {
        const params = { page: currentPage.value };
        if (statusFilter.value) params.status = statusFilter.value;
        const data = await adminService.getAppointmentHistory(params);
        appointments.value = data.data;
        pagination.value = { current_page: data.current_page, last_page: data.last_page, total: data.total };
    } finally {
        loading.value = false;
    }
};

const changePage = (page) => {
    currentPage.value = page;
    loadHistory();
};

onMounted(loadHistory);
</script>
