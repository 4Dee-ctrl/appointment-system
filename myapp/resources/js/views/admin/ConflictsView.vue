<template>
    <AdminLayout>
        <div class="space-y-8">
            <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">Conflict Attempts</h1>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin"></div>
            </div>

            <template v-else>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="stat-card">
                        <p class="text-[#86868b] text-sm">Total Conflicts</p>
                        <p class="text-2xl font-semibold text-[#1d1d1f] mt-1">{{ stats?.total || 0 }}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-[#86868b] text-sm">Today</p>
                        <p class="text-2xl font-semibold text-[#1d1d1f] mt-1">{{ stats?.today || 0 }}</p>
                    </div>
                    <div class="stat-card">
                        <p class="text-[#86868b] text-sm">This Week</p>
                        <p class="text-2xl font-semibold text-[#1d1d1f] mt-1">{{ stats?.this_week || 0 }}</p>
                    </div>
                </div>

                <div v-if="conflicts.length === 0" class="card p-10 text-center">
                    <div class="w-16 h-16 bg-[#f5f5f7] rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-[#86868b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-[#86868b]">No conflict attempts recorded</p>
                </div>

                <div v-else class="card overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-black/5">
                                <th class="table-header text-left">User</th>
                                <th class="table-header text-left">Date</th>
                                <th class="table-header text-left">Time Slot</th>
                                <th class="table-header text-left">Reason</th>
                                <th class="table-header text-left">Attempted</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5">
                            <tr v-for="conflict in conflicts" :key="conflict.id" class="hover:bg-black/[0.02]">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-[#1d1d1f]">{{ conflict.user?.name }}</p>
                                    <p class="text-sm text-[#86868b]">{{ conflict.user?.email }}</p>
                                </td>
                                <td class="px-6 py-4 text-[#1d1d1f]">{{ formatDate(conflict.attempted_date) }}</td>
                                <td class="px-6 py-4 text-[#86868b]">{{ conflict.time_slot?.start_time }} - {{ conflict.time_slot?.end_time }}</td>
                                <td class="px-6 py-4"><span class="badge badge-rejected">{{ conflict.reason }}</span></td>
                                <td class="px-6 py-4 text-[#86868b]">{{ formatDateTime(conflict.created_at) }}</td>
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

const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const formatDateTime = (datetime) => new Date(datetime).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

const loadConflicts = async () => {
    loading.value = true;
    try {
        const [conflictsData, statsData] = await Promise.all([
            adminService.getConflictAttempts({ page: currentPage.value }),
            adminService.getConflictStats(),
        ]);
        conflicts.value = conflictsData.data;
        pagination.value = { current_page: conflictsData.current_page, last_page: conflictsData.last_page, total: conflictsData.total };
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
