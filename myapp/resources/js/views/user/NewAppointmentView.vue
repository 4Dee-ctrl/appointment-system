<template>
    <UserLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Request New Appointment</h1>
                <p class="mt-1 text-sm text-gray-600">Select a date and available time slot</p>
            </div>

            <div v-if="appointmentStore.pendingCount >= 2" class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                <p class="text-sm text-yellow-700">
                    You have reached the maximum of 2 pending appointments. Please wait for your appointments to be processed.
                </p>
            </div>

            <div v-else class="bg-white shadow rounded-lg p-6">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md text-sm">
                        {{ error }}
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input
                            id="date"
                            v-model="form.appointment_date"
                            type="date"
                            :min="minDate"
                            required
                            @change="loadAvailableSlots"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        />
                    </div>

                    <div v-if="form.appointment_date">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Available Time Slots</label>
                        <div v-if="loadingSlots" class="text-gray-500 text-sm">Loading available slots...</div>
                        <div v-else-if="appointmentStore.availableSlots.length === 0" class="text-gray-500 text-sm">
                            No available slots for this date
                        </div>
                        <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <button
                                v-for="slot in appointmentStore.availableSlots"
                                :key="slot.id"
                                type="button"
                                @click="form.time_slot_id = slot.id"
                                :class="[
                                    'p-3 text-sm border rounded-md text-center transition-colors',
                                    form.time_slot_id === slot.id
                                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                        : 'border-gray-300 hover:border-gray-400'
                                ]"
                            >
                                {{ slot.start_time }} - {{ slot.end_time }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea
                            id="notes"
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Any additional information..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <router-link
                            to="/appointments"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </router-link>
                        <button
                            type="submit"
                            :disabled="!form.time_slot_id || submitting"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                        >
                            <span v-if="submitting">Submitting...</span>
                            <span v-else>Request Appointment</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </UserLayout>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAppointmentStore } from '../../stores';
import UserLayout from '../../layouts/UserLayout.vue';

const router = useRouter();
const appointmentStore = useAppointmentStore();

const form = reactive({
    appointment_date: '',
    time_slot_id: null,
    notes: '',
});

const loadingSlots = ref(false);
const submitting = ref(false);
const error = ref('');

const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const loadAvailableSlots = async () => {
    if (!form.appointment_date) return;
    form.time_slot_id = null;
    loadingSlots.value = true;
    try {
        await appointmentStore.fetchAvailableSlots(form.appointment_date);
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to load available slots';
    } finally {
        loadingSlots.value = false;
    }
};

const handleSubmit = async () => {
    submitting.value = true;
    error.value = '';
    try {
        await appointmentStore.createAppointment(form);
        router.push('/appointments');
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to create appointment';
    } finally {
        submitting.value = false;
    }
};

onMounted(async () => {
    await appointmentStore.fetchAppointments();
});
</script>
