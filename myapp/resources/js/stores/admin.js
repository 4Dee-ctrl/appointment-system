import { defineStore } from 'pinia';
import { adminService } from '../services';

export const useAdminStore = defineStore('admin', {
    state: () => ({
        dashboard: null,
        appointments: [],
        pendingAppointments: [],
        timeSlots: [],
        disabledDates: [],
        conflictAttempts: [],
        conflictStats: null,
        pagination: null,
        loading: false,
        error: null,
    }),

    actions: {
        async fetchDashboard() {
            this.loading = true;
            try {
                const data = await adminService.getDashboard();
                this.dashboard = data;
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch dashboard';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchAppointments(params = {}) {
            this.loading = true;
            try {
                const data = await adminService.getAppointments(params);
                this.appointments = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    per_page: data.per_page,
                    total: data.total,
                };
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch appointments';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchPendingAppointments() {
            this.loading = true;
            try {
                const data = await adminService.getPendingAppointments();
                this.pendingAppointments = data.appointments;
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch pending appointments';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async approveAppointment(id) {
            try {
                const data = await adminService.approveAppointment(id);
                this.pendingAppointments = this.pendingAppointments.filter(a => a.id !== id);
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to approve appointment';
                throw error;
            }
        },

        async rejectAppointment(id, reason = null) {
            try {
                const data = await adminService.rejectAppointment(id, reason);
                this.pendingAppointments = this.pendingAppointments.filter(a => a.id !== id);
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to reject appointment';
                throw error;
            }
        },

        async fetchTimeSlots() {
            this.loading = true;
            try {
                const data = await adminService.getTimeSlots();
                this.timeSlots = data.time_slots;
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch time slots';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createTimeSlot(slotData) {
            try {
                const data = await adminService.createTimeSlot(slotData);
                this.timeSlots.push(data.time_slot);
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to create time slot';
                throw error;
            }
        },

        async updateTimeSlot(id, slotData) {
            try {
                const data = await adminService.updateTimeSlot(id, slotData);
                const index = this.timeSlots.findIndex(s => s.id === id);
                if (index !== -1) {
                    this.timeSlots[index] = data.time_slot;
                }
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to update time slot';
                throw error;
            }
        },

        async deleteTimeSlot(id) {
            try {
                await adminService.deleteTimeSlot(id);
                this.timeSlots = this.timeSlots.filter(s => s.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to delete time slot';
                throw error;
            }
        },

        async toggleTimeSlot(id) {
            try {
                const data = await adminService.toggleTimeSlot(id);
                const index = this.timeSlots.findIndex(s => s.id === id);
                if (index !== -1) {
                    this.timeSlots[index] = data.time_slot;
                }
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to toggle time slot';
                throw error;
            }
        },

        async fetchDisabledDates(params = {}) {
            this.loading = true;
            try {
                const data = await adminService.getDisabledDates(params);
                this.disabledDates = data.disabled_dates;
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch disabled dates';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async disableDate(dateData) {
            try {
                const data = await adminService.disableDate(dateData);
                this.disabledDates.unshift(data.disabled_date);
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to disable date';
                throw error;
            }
        },

        async disableFullDay(dateData) {
            try {
                const data = await adminService.disableFullDay(dateData);
                this.disabledDates.unshift(data.disabled_date);
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to disable full day';
                throw error;
            }
        },

        async enableDate(id) {
            try {
                await adminService.enableDate(id);
                this.disabledDates = this.disabledDates.filter(d => d.id !== id);
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to enable date';
                throw error;
            }
        },

        async fetchConflictAttempts(params = {}) {
            this.loading = true;
            try {
                const data = await adminService.getConflictAttempts(params);
                this.conflictAttempts = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    per_page: data.per_page,
                    total: data.total,
                };
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch conflict attempts';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchConflictStats() {
            try {
                const data = await adminService.getConflictStats();
                this.conflictStats = data.stats;
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to fetch conflict stats';
                throw error;
            }
        },
    },
});
