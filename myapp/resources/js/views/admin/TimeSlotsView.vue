<template>
    <AdminLayout>
        <div class="space-y-8">
            <div class="flex justify-between items-center">
                <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">Time Slots</h1>
                <button @click="openModal()" class="btn-primary">Add Time Slot</button>
            </div>

            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin"></div>
            </div>

            <div v-else class="card overflow-hidden">
                <div class="table-container">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-black/5">
                                <th class="table-header text-left">Start Time</th>
                                <th class="table-header text-left">End Time</th>
                                <th class="table-header text-left">Status</th>
                                <th class="table-header text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5">
                            <tr v-for="slot in adminStore.timeSlots" :key="slot.id" class="hover:bg-black/[0.02]">
                                <td class="px-6 py-4 text-[#1d1d1f]">{{ slot.start_time }}</td>
                                <td class="px-6 py-4 text-[#1d1d1f]">{{ slot.end_time }}</td>
                                <td class="px-6 py-4">
                                    <span :class="slot.is_active ? 'badge badge-active' : 'badge badge-inactive'">
                                        {{ slot.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-4">
                                        <button @click="toggleSlot(slot.id)" class="text-sm font-medium text-[#0071e3] hover:underline">
                                            {{ slot.is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                        <button @click="openModal(slot)" class="text-sm font-medium text-[#0071e3] hover:underline">Edit</button>
                                        <button @click="deleteSlot(slot.id)" class="text-sm font-medium text-[#ff3b30] hover:underline">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
                <div class="modal-content">
                    <h3 class="text-xl font-semibold text-[#1d1d1f] mb-6">{{ editingSlot ? 'Edit Time Slot' : 'Add Time Slot' }}</h3>
                    <form @submit.prevent="saveSlot" class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Start Time</label>
                            <input v-model="form.start_time" type="time" required class="input-field"/>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#1d1d1f] mb-2">End Time</label>
                            <input v-model="form.end_time" type="time" required class="input-field"/>
                        </div>
                        <div class="flex items-center gap-3">
                            <input v-model="form.is_active" type="checkbox" id="is_active" class="w-5 h-5 rounded border-gray-300 text-[#0071e3] focus:ring-[#0071e3]"/>
                            <label for="is_active" class="text-sm text-[#1d1d1f]">Active</label>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
                            <button type="submit" class="btn-primary">Save</button>
                        </div>
                    </form>
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
const showModal = ref(false);
const editingSlot = ref(null);

const form = reactive({ start_time: '', end_time: '', is_active: true });

const openModal = (slot = null) => {
    editingSlot.value = slot;
    if (slot) {
        form.start_time = slot.start_time;
        form.end_time = slot.end_time;
        form.is_active = slot.is_active;
    } else {
        form.start_time = '';
        form.end_time = '';
        form.is_active = true;
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingSlot.value = null;
};

const saveSlot = async () => {
    try {
        if (editingSlot.value) {
            await adminStore.updateTimeSlot(editingSlot.value.id, form);
        } else {
            await adminStore.createTimeSlot(form);
        }
        closeModal();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to save');
    }
};

const toggleSlot = async (id) => {
    try {
        await adminStore.toggleTimeSlot(id);
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to toggle');
    }
};

const deleteSlot = async (id) => {
    if (!confirm('Delete this time slot?')) return;
    try {
        await adminStore.deleteTimeSlot(id);
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to delete');
    }
};

onMounted(async () => {
    try {
        await adminStore.fetchTimeSlots();
    } finally {
        loading.value = false;
    }
});
</script>
