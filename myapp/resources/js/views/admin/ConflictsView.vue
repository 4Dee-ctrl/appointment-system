<template>
    <AdminLayout>
        <div class="space-y-6">
            <h1 class="text-2xl font-semibold text-gray-900">Conflict Attempts</h1>

            <div v-if="loading" class="text-center text-gray-500">Loading...</div>

            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Total Conflicts</p>
                        <p class="text-2xl font-semibold">{{ stats?.total || 0 }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Today</p>
                        <p class="text-2xl font-semibold">{{ stats?.today || 0 }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">This Week</p>
                        <p class="text-2xl font-semibold">{{ stats?.this_week || 0 }}</p>
                    </div>
                </div>

                <div v-if="conflicts.length === 0" class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                    No conflict attempts recorded
                </div>

                <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time Slot</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attempted At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="conflict in conflicts" :key="conflict.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ conflict.user?.name }}</div>
                                    <div class="text-sm text-gray-500">{{ conflict.user?.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(conflict.attempted_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ conflict.time_slot?.start_time }} - {{ conflict.time_slot?.end_time }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                        {{ conflict.reason }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDateTime(conflict.created_at) }}
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
            </template>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { adminService } from '../../services';
import AdminLayout from '../../layouts/AdminLayout.vue';

const loading = ref(true);
const conflicts = ref([]);
const stats = ref(null);
const pagination = ref(null);
const currentPage = ref(1);

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

const loadConflicts = async () => {
    loading.value = true;
    try {
        const [conflictsData, statsData] = await Promise.all([
            adminService.getConflictAttempts({ page: currentPage.value }),
            adminService.getConflictStats(),
        ]);
        conflicts.value = conflictsData.data;
        pagination.value = {
            current_page: conflictsData.current_page,
            last_page: conflictsData.last_page,
            total: conflictsData.total,
        };
        stats.value = statsData.stats;
    } finally {
        loading.value = false;
    }
};

const changePage = (page) => {
    currentPage.value = page;
    loadConflicts();
};

onMounted(loadConflicts);
</script>
