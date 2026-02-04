<template>
    <AdminLayout>
        <div class="space-y-8">
            <div class="flex justify-between items-center">
                <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">All Appointments</h1>
                <div class="flex gap-3">
                    <select v-model="filters.status" @change="loadAppointments" class="input-field w-36">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input v-model="filters.date" type="date" @change="loadAppointments" class="input-field w-40"/>
                </div>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div v-else-if="adminStore.appointments.length === 0" class="card p-10 text-center">
                <p class="text-[#86868b]">No appointments found</p>
            </div>

            <div v-else class="card overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-black/5">
                            <th class="table-header text-left">User</th>
                            <th class="table-header text-left">Date</th>
                            <th class="table-header text-left">Time</th>
                            <th class="table-header text-left">Status</th>
                            <th class="table-header text-left">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <tr v-for="appointment in adminStore.appointments" :key="appointment.id" class="hover:bg-black/[0.02]">
                            <td class="px-6 py-4">
                                <p class="font-medium text-[#1d1d1f]">{{ appointment.user?.name }}</p>
                                <p class="text-sm text-[#86868b]">{{ appointment.user?.email }}</p>
                            </td>
                            <td class="px-6 py-4 text-[#1d1d1f]">{{ formatDate(appointment.appointment_date) }}</td>
                            <td class="px-6 py-4 text-[#86868b]">{{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}</td>
                            <td class="px-6 py-4"><span :class="statusBadge(appointment.status)">{{ appointment.status }}</span></td>
                            <td class="px-6 py-4 text-[#86868b]">{{ formatDateTime(appointment.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="adminStore.pagination" class="px-6 py-4 border-t border-black/5 flex justify-between items-center">
                    <span class="text-sm text-[#86868b]">Page {{ adminStore.pagination.current_page }} of {{ adminStore.pagination.last_page }}</span>
                    <div class="flex gap-2">
                        <button @click="changePage(adminStore.pagination.current_page - 1)" :disabled="adminStore.pagination.current_page === 1" class="btn-secondary text-sm py-2">Previous</button>
                        <button @click="changePage(adminStore.pagination.current_page + 1)" :disabled="adminStore.pagination.current_page === adminStore.pagination.last_page" class="btn-secondary text-sm py-2">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useAdminStore } from '../../stores';
import AdminLayout from '../../layouts/AdminLayout.vue';

const adminStore = useAdminStore();
const loading = ref(true);
const filters = reactive({ status: '', date: '', page: 1 });

const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const formatDateTime = (datetime) => new Date(datetime).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

const statusBadge = (status) => {
    const badges = { pending: 'badge badge-pending', approved: 'badge badge-approved', rejected: 'badge badge-rejected' };
    return badges[status] || 'badge';
};

const loadAppointments = async () => {
    loading.value = true;
    try {
        const params = { page: filters.page };
        if (filters.status) params.status = filters.status;
        if (filters.date) params.date = filters.date;
        await adminStore.fetchAppointments(params);
    } finally {
        loading.value = false;
    }
};

const changePage = (page) => {
    filters.page = page;
    loadAppointments();
};

onMounted(loadAppointments);
</script>
