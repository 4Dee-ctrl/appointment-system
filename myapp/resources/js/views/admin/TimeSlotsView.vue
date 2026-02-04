<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Time Slot Management</h1>
                <button
                    @click="openModal()"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                >
                    Add Time Slot
                </button>
            </div>

            <div v-if="loading" class="text-center text-gray-500">Loading...</div>

            <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">End Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="slot in adminStore.timeSlots" :key="slot.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ slot.start_time }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ slot.end_time }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="slot.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 py-1 text-xs font-medium rounded-full"
                                >
                                    {{ slot.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button
                                    @click="toggleSlot(slot.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    {{ slot.is_active ? 'Disable' : 'Enable' }}
                                </button>
                                <button
                                    @click="openModal(slot)"
                                    class="text-blue-600 hover:text-blue-900"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteSlot(slot.id)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ editingSlot ? 'Edit Time Slot' : 'Add Time Slot' }}
                    </h3>
                    <form @submit.prevent="saveSlot" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input
                                v-model="form.start_time"
                                type="time"
                                required
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Time</label>
                            <input
                                v-model="form.end_time"
                                type="time"
                                required
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md"
                            />
                        </div>
                        <div class="flex items-center">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                id="is_active"
                                class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                            />
                            <label for="is_active" class="ml-2 text-sm text-gray-700">Active</label>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4">
                            <button
                                type="button"
                                @click="closeModal"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                            >
                                Save
                            </button>
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

const form = reactive({
    start_time: '',
    end_time: '',
    is_active: true,
});

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
        alert(e.response?.data?.message || 'Failed to save time slot');
    }
};

const toggleSlot = async (id) => {
    try {
        await adminStore.toggleTimeSlot(id);
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to toggle time slot');
    }
};

const deleteSlot = async (id) => {
    if (!confirm('Are you sure you want to delete this time slot?')) return;
    try {
        await adminStore.deleteTimeSlot(id);
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to delete time slot');
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
