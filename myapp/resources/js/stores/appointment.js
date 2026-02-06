import { defineStore } from 'pinia';
import { appointmentService, timeSlotService } from '../services';

export const useAppointmentStore = defineStore('appointment', {
    state: () => ({
        appointments: [],
        availableSlots: [],
        disabledDates: [],
        loading: false,
        error: null,
    }),

    getters: {
        pendingAppointments: (state) => state.appointments.filter(a => a.status === 'pending'),
        approvedAppointments: (state) => state.appointments.filter(a => a.status === 'approved'),
        rejectedAppointments: (state) => state.appointments.filter(a => a.status === 'rejected'),
        pendingCount: (state) => state.appointments.filter(a => a.status === 'pending').length,
    },

    actions: {
        async fetchAppointments() {
            this.loading = true;
            this.error = null;
            try {
                const data = await appointmentService.getAll();
                this.appointments = data.appointments;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch appointments';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchDisabledDates() {
            try {
                const data = await timeSlotService.getDisabledDates();
                this.disabledDates = data.disabled_dates;
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch disabled dates';
                throw error;
            }
        },

        async fetchAvailableSlots(date) {
            this.loading = true;
            try {
                const data = await timeSlotService.getAvailable(date);
                this.availableSlots = data.available_slots;
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch available slots';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createAppointment(appointmentData) {
            this.loading = true;
            this.error = null;
            try {
                const data = await appointmentService.create(appointmentData);
                this.appointments.unshift(data.appointment);
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create appointment';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async updateAppointment(id, appointmentData) {
            this.loading = true;
            this.error = null;
            try {
                const data = await appointmentService.update(id, appointmentData);
                const index = this.appointments.findIndex(a => a.id === id);
                if (index !== -1) {
                    this.appointments[index] = data.appointment;
                }
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update appointment';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async deleteAppointment(id) {
            this.loading = true;
            this.error = null;
            try {
                await appointmentService.delete(id);
                this.appointments = this.appointments.filter(a => a.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete appointment';
                throw error;
            } finally {
                this.loading = false;
            }
        },
    },
});
