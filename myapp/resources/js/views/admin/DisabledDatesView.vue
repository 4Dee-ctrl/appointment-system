<template>
    <AdminLayout>
        <div class="space-y-8">
            <div class="flex justify-between items-center">
                <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">Disabled Dates</h1>
                <button @click="openModal()" class="btn-primary">Disable Date</button>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div v-else-if="adminStore.disabledDates.length === 0" class="card p-10 text-center">
                <div class="w-16 h-16 bg-[#f5f5f7] rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#86868b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-[#86868b]">No disabled dates</p>
            </div>

            <div v-else class="card overflow-hidden">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-black/5">
                            <th class="table-header text-left">Date</th>
                            <th class="table-header text-left">Time Slot</th>
                            <th class="table-header text-left">Reason</th>
                            <th class="table-header text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/5">
                        <tr v-for="disabled in adminStore.disabledDates" :key="disabled.id" class="hover:bg-black/[0.02]">
                            <td class="px-6 py-4 text-[#1d1d1f]">{{ formatDate(disabled.date) }}</td>
                            <td class="px-6 py-4 text-[#86868b]">{{ disabled.time_slot ? `${disabled.time_slot.start_time} - ${disabled.time_slot.end_time}` : 'Full Day' }}</td>
                            <td class="px-6 py-4 text-[#86868b]">{{ disabled.reason || '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <button @click="enableDate(disabled.id)" class="text-sm font-medium text-[#34c759] hover:underline">Enable</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                <div class="modal-content">
                    <h3 class="text-xl font-semibold text-[#1d1d1f] mb-6">Disable Date</h3>
                    <form @submit.prevent="saveDisabled" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Date</label>
                            <input v-model="form.date" type="date" :min="minDate" required class="input-field"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Type</label>
                            <select v-model="form.type" class="input-field">
                                <option value="full">Full Day</option>
                                <option value="slot">Specific Time Slot</option>
                            </select>
                        </div>
                        <div v-if="form.type === 'slot'">
                            <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Time Slot</label>
                            <select v-model="form.time_slot_id" required class="input-field">
                                <option value="">Select a slot</option>
                                <option v-for="slot in adminStore.timeSlots" :key="slot.id" :value="slot.id">{{ slot.start_time }} - {{ slot.end_time }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Reason (optional)</label>
                            <input v-model="form.reason" type="text" class="input-field" placeholder="e.g., Holiday"/>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-danger">Disable</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useAdminStore } from '../../stores';
import AdminLayout from '../../layouts/AdminLayout.vue';

const adminStore = useAdminStore();
const loading = ref(true);
const showModal = ref(false);

const form = reactive({ date: '', type: 'full', time_slot_id: '', reason: '' });
const minDate = computed(() => new Date().toISOString().split('T')[0]);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
};

const openModal = () => {
    form.date = '';
    form.type = 'full';
    form.time_slot_id = '';
    form.reason = '';
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const saveDisabled = async () => {
    try {
        if (form.type === 'full') {
            await adminStore.disableFullDay({ date: form.date, reason: form.reason });
        } else {
            await adminStore.disableDate({ date: form.date, time_slot_id: form.time_slot_id, reason: form.reason });
        }
        closeModal();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to disable');
    }
};

const enableDate = async (id) => {
    if (!confirm('Enable this date?')) return;
    try {
        await adminStore.enableDate(id);
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to enable');
    }
};

onMounted(async () => {
    try {
        await Promise.all([adminStore.fetchDisabledDates(), adminStore.fetchTimeSlots()]);
    } finally {
        loading.value = false;
    }
});
</script>
