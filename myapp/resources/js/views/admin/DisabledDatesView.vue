<template>
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Disabled Dates</h1>
                <button
                    @click="openModal()"
                    class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                >
                    Disable Date
                </button>
            </div>

            <div v-if="loading" class="text-center text-gray-500">Loading...</div>

            <div v-else-if="adminStore.disabledDates.length === 0" class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                No disabled dates
            </div>

            <div v-else class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time Slot</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="disabled in adminStore.disabledDates" :key="disabled.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(disabled.date) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ disabled.time_slot ? `${disabled.time_slot.start_time} - ${disabled.time_slot.end_time}` : 'Full Day' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ disabled.reason || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    @click="enableDate(disabled.id)"
                                    class="text-green-600 hover:text-green-900"
                                >
                                    Enable
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-full max-w-md">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Disable Date</h3>
                    <form @submit.prevent="saveDisabled" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input
                                v-model="form.date"
                                type="date"
                                :min="minDate"
                                required
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Disable Type</label>
                            <select
                                v-model="form.type"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md"
                            >
                                <option value="full">Full Day</option>
                                <option value="slot">Specific Time Slot</option>
                            </select>
                        </div>
                        <div v-if="form.type === 'slot'">
                            <label class="block text-sm font-medium text-gray-700">Time Slot</label>
                            <select
                                v-model="form.time_slot_id"
                                required
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md"
                            >
                                <option value="">Select a time slot</option>
                                <option v-for="slot in adminStore.timeSlots" :key="slot.id" :value="slot.id">
                                    {{ slot.start_time }} - {{ slot.end_time }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason (optional)</label>
                            <input
                                v-model="form.reason"
                                type="text"
                                class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md"
                                placeholder="e.g., Holiday, Maintenance"
                            />
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
                                class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700"
                            >
                                Disable
                            </button>
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

const form = reactive({
    date: '',
    type: 'full',
    time_slot_id: '',
    reason: '',
});

const minDate = computed(() => new Date().toISOString().split('T')[0]);

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        weekday: 'long',
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

const openModal = () => {
    form.date = '';
    form.type = 'full';
    form.time_slot_id = '';
    form.reason = '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const saveDisabled = async () => {
    try {
        if (form.type === 'full') {
            await adminStore.disableFullDay({
                date: form.date,
                reason: form.reason,
            });
        } else {
            await adminStore.disableDate({
                date: form.date,
                time_slot_id: form.time_slot_id,
                reason: form.reason,
            });
        }
        closeModal();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to disable date');
    }
};

const enableDate = async (id) => {
    if (!confirm('Are you sure you want to enable this date?')) return;
    try {
        await adminStore.enableDate(id);
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to enable date');
    }
};

onMounted(async () => {
    try {
        await Promise.all([
            adminStore.fetchDisabledDates(),
            adminStore.fetchTimeSlots(),
        ]);
    } finally {
        loading.value = false;
    }
});
</script>
