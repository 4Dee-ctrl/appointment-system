<template>
    <UserLayout>
        <div class="space-y-8">
            <div>
                <h1 class="text-[28px] font-semibold text-[#1d1d1f] tracking-tight">New Appointment</h1>
                <p class="text-[#86868b] mt-1">Select a date and time for your appointment</p>
            </div>

            <div v-if="appointmentStore.pendingCount >= 2" class="bg-[#ff9500]/10 border border-[#ff9500]/20 rounded-2xl p-5">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-[#ff9500]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm text-[#1d1d1f]">You have reached the maximum of 2 pending appointments.</p>
                </div>
            </div>

            <div v-else class="card p-8">
                <form @submit.prevent="handleSubmit" class="space-y-8">
                    <div v-if="error" class="bg-[#ff3b30]/10 text-[#ff3b30] px-4 py-3 rounded-xl text-sm font-medium">
                        {{ error }}
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Select Date</label>
                        <input
                            v-model="form.appointment_date"
                            type="date"
                            :min="minDate"
                            required
                            @change="loadAvailableSlots"
                            class="input-field"
                        />
                    </div>

                    <div v-if="form.appointment_date">
                        <label class="block text-sm font-medium text-[#1d1d1f] mb-4">Available Time Slots</label>
                        <div v-if="loadingSlots" class="text-center py-8">
                            <div class="w-8 h-8 border-2 border-[#0071e3] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        </div>
                        <div v-else-if="appointmentStore.availableSlots.length === 0" class="text-center py-8">
                            <p class="text-[#86868b]">No available slots for this date</p>
                        </div>
                        <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <button
                                v-for="slot in appointmentStore.availableSlots"
                                :key="slot.id"
                                type="button"
                                @click="form.time_slot_id = slot.id"
                                class="time-slot-btn"
                                :class="{ 'selected': form.time_slot_id === slot.id }"
                            >
                                {{ slot.start_time }} - {{ slot.end_time }}
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Notes (optional)</label>
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="input-field resize-none"
                            placeholder="Add any additional information..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <router-link to="/appointments" class="btn-secondary">Cancel</router-link>
                        <button type="submit" :disabled="!form.time_slot_id || submitting" class="btn-primary">
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

const minDate = computed(() => new Date().toISOString().split('T')[0]);

const loadAvailableSlots = async () => {
    if (!form.appointment_date) return;
    form.time_slot_id = null;
    loadingSlots.value = true;
    try {
        await appointmentStore.fetchAvailableSlots(form.appointment_date);
    } catch (e) {
        error.value = e.response?.data?.message || 'Failed to load slots';
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

<style scoped>
.card {
    @apply bg-white rounded-2xl shadow-md;
}

.input-field {
    @apply mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm;
}

.time-slot-btn {
    @apply p-3 text-sm border rounded-md text-center transition-colors;
}

.time-slot-btn.selected {
    @apply border-indigo-500 bg-indigo-50 text-indigo-700;
}

.btn-primary {
    @apply px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50;
}

.btn-secondary {
    @apply px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50;
}
</style>
