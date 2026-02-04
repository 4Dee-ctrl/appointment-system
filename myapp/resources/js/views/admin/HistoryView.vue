<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Appointment History</h1>
                <select
                    v-model="statusFilter"
                    @change="loadHistory"
                    class="px-3 py-2 border border-gray-300 rounded-md text-sm"
                >
                    <option value="">All</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div v-if="loading" class="text-center text-gray-500">Loading...</div>

            <div v-else-if="appointments.length === 0" class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                No history found
            </div>

            <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Processed</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="appointment in appointments" :key="appointment.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ appointment.user?.name }}</div>
                                <div class="text-sm text-gray-500">{{ appointment.user?.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(appointment.appointment_date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ appointment.time_slot?.start_time }} - {{ appointment.time_slot?.end_time }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="statusClass(appointment.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                    {{ appointment.status }}
                                </span>
                                <p v-if="appointment.rejection_reason" class="text-xs text-red-500 mt-1">
                                    {{ appointment.rejection_reason }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDateTime(appointment.approved_at || appointment.rejected_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="pagination" class="px-6 py-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-700">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    <div class="flex space-x-2">
                        <button
                            @click="changePage(pagination.current_page - 1)"
                            :disabled="pagination.current_page === 1"
                            class="px-3 py-1 border rounded text-sm disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            @click="changePage(pagination.current_page + 1)"
                            :disabled="pagination.current_page === pagination.last_page"
                            class="px-3 py-1 border rounded text-sm disabled:opacity-50"
                        >
                            Next
                        </button>
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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatDateTime = (datetime) => {
    if (!datetime) return '-';
    return new Date(datetime).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const statusClass = (status) => {
    const classes = {
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const loadHistory = async () => {
    loading.value = true;
    try {
        const params = { page: currentPage.value };
        if (statusFilter.value) params.status = statusFilter.value;
        const data = await adminService.getAppointmentHistory(params);
        appointments.value = data.data;
        pagination.value = {
            current_page: data.current_page,
            last_page: data.last_page,
            total: data.total,
        };
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
