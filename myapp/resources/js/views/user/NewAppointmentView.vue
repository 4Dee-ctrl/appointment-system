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
                            ref="dateInput"
                            v-model="form.appointment_date"
                            type="date"
                            :min="minDate"
                            required
                            @change="handleDateChange"
                            @input="handleDateInput"
                            class="input-field"
                        />
                        <p v-if="dateDisabledMessage" class="mt-2 text-sm text-[#ff3b30]">{{ dateDisabledMessage }}</p>
                    </div>

                    <div v-if="form.appointment_date && !dateDisabledMessage">
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

                    <div v-if="form.appointment_date && !dateDisabledMessage">
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
                        <button
                            type="submit"
                            :disabled="!form.time_slot_id || submitting"
                            class="px-6 py-3 bg-[#0071e3] text-white font-medium rounded-xl hover:bg-[#0077ed] transition-all disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
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

const dateInput = ref(null);
const loadingSlots = ref(false);
const submitting = ref(false);
const error = ref('');
const dateDisabledMessage = ref('');

const minDate = computed(() => new Date().toISOString().split('T')[0]);

const isDateDisabled = (dateString) => {
    return appointmentStore.disabledDates.includes(dateString);
};

const handleDateInput = (event) => {
    const selectedDate = event.target.value;
    if (selectedDate && isDateDisabled(selectedDate)) {
        dateDisabledMessage.value = 'This date is not available for appointments';
        form.time_slot_id = null;
        appointmentStore.availableSlots = [];
    } else {
        dateDisabledMessage.value = '';
    }
};

const handleDateChange = async () => {
    if (!form.appointment_date) return;

    if (isDateDisabled(form.appointment_date)) {
        dateDisabledMessage.value = 'This date is not available for appointments';
        form.time_slot_id = null;
        appointmentStore.availableSlots = [];
        return;
    }

    dateDisabledMessage.value = '';
    await loadAvailableSlots();
};

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
    if (isDateDisabled(form.appointment_date)) {
        error.value = 'This date is not available for appointments';
        return;
    }

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
    try {
        await appointmentStore.fetchAppointments();
    } catch (e) {
        console.error('Failed to fetch appointments:', e);
    }
    try {
        await appointmentStore.fetchDisabledDates();
    } catch (e) {
        console.error('Failed to fetch disabled dates:', e);
    }
});
</script>

<style scoped>
.card {
    background-color: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
}

.input-field {
    margin-top: 0.25rem;
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 0.875rem;
}

.input-field:focus {
    outline: none;
    border-color: #0071e3;
    box-shadow: 0 0 0 2px rgba(0, 113, 227, 0.2);
}

.time-slot-btn {
    padding: 0.75rem;
    font-size: 0.875rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background-color: white;
}

.time-slot-btn:hover {
    border-color: #0071e3;
    background-color: #f0f7ff;
}

.time-slot-btn.selected {
    border-color: #0071e3;
    background-color: #e0f0ff;
    color: #0071e3;
    font-weight: 500;
}

.btn-secondary {
    padding: 0.75rem 1.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    background-color: white;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-secondary:hover {
    background-color: #f9fafb;
}
</style>
