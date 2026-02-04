<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">All Appointments</h1>
                <div class="flex space-x-4">
                    <select
                        v-model="filters.status"
                        @change="loadAppointments"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm"
                    >
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <input
                        v-model="filters.date"
                        type="date"
                        @change="loadAppointments"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm"
                    />
                </div>
            </div>

            <div v-if="loading" class="text-center text-gray-500">Loading...</div>

            <div v-else-if="adminStore.appointments.length === 0" class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                No appointments found
            </div>

            <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="appointment in adminStore.appointments" :key="appointment.id">
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
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDateTime(appointment.created_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="adminStore.pagination" class="px-6 py-3 border-t border-gray-200 flex justify-between items-center">
                    <span class="text-sm text-gray-700">
                        Page {{ adminStore.pagination.current_page }} of {{ adminStore.pagination.last_page }}
                    </span>
                    <div class="flex space-x-2">
                        <button
                            @click="changePage(adminStore.pagination.current_page - 1)"
                            :disabled="adminStore.pagination.current_page === 1"
                            class="px-3 py-1 border rounded text-sm disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            @click="changePage(adminStore.pagination.current_page + 1)"
                            :disabled="adminStore.pagination.current_page === adminStore.pagination.last_page"
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
import { ref, reactive, onMounted } from 'vue';
import { useAdminStore } from '../../stores';
import AdminLayout from '../../layouts/AdminLayout.vue';

const adminStore = useAdminStore();
const loading = ref(true);
const filters = reactive({
    status: '',
    date: '',
    page: 1,
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatDateTime = (datetime) => {
    return new Date(datetime).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
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
