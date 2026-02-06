import api from './api';

export const authService = {
    async login(credentials) {
        const response = await api.post('/auth/login', credentials);
        return response.data;
    },

    async register(data) {
        const response = await api.post('/auth/register', data);
        return response.data;
    },

    async logout() {
        const response = await api.post('/auth/logout');
        return response.data;
    },

    async getUser() {
        const response = await api.get('/auth/user');
        return response.data;
    },
};

export const timeSlotService = {
    async getAll() {
        const response = await api.get('/time-slots');
        return response.data;
    },

    async getAvailable(date) {
        const response = await api.get('/time-slots/available', { params: { date } });
        return response.data;
    },

    async getDisabledDates(params = {}) {
        const response = await api.get('/disabled-dates', { params });
        return response.data;
    },
};

export const appointmentService = {
    async getAll() {
        const response = await api.get('/appointments');
        return response.data;
    },

    async create(data) {
        const response = await api.post('/appointments', data);
        return response.data;
    },

    async get(id) {
        const response = await api.get(`/appointments/${id}`);
        return response.data;
    },

    async update(id, data) {
        const response = await api.put(`/appointments/${id}`, data);
        return response.data;
    },

    async delete(id) {
        const response = await api.delete(`/appointments/${id}`);
        return response.data;
    },
};

export const adminService = {
    async getDashboard() {
        const response = await api.get('/admin/dashboard');
        return response.data;
    },

    async getAppointments(params = {}) {
        const response = await api.get('/admin/appointments', { params });
        return response.data;
    },

    async getPendingAppointments() {
        const response = await api.get('/admin/appointments/pending');
        return response.data;
    },

    async getAppointmentHistory(params = {}) {
        const response = await api.get('/admin/appointments/history', { params });
        return response.data;
    },

    async approveAppointment(id) {
        const response = await api.post(`/admin/appointments/${id}/approve`);
        return response.data;
    },

    async rejectAppointment(id, reason = null) {
        const response = await api.post(`/admin/appointments/${id}/reject`, { rejection_reason: reason });
        return response.data;
    },

    async getTimeSlots() {
        const response = await api.get('/admin/time-slots');
        return response.data;
    },

    async createTimeSlot(data) {
        const response = await api.post('/admin/time-slots', data);
        return response.data;
    },

    async updateTimeSlot(id, data) {
        const response = await api.put(`/admin/time-slots/${id}`, data);
        return response.data;
    },

    async deleteTimeSlot(id) {
        const response = await api.delete(`/admin/time-slots/${id}`);
        return response.data;
    },

    async toggleTimeSlot(id) {
        const response = await api.post(`/admin/time-slots/${id}/toggle`);
        return response.data;
    },

    async getDisabledDates(params = {}) {
        const response = await api.get('/admin/disabled-dates', { params });
        return response.data;
    },

    async disableDate(data) {
        const response = await api.post('/admin/disabled-dates', data);
        return response.data;
    },

    async disableFullDay(data) {
        const response = await api.post('/admin/disabled-dates/full-day', data);
        return response.data;
    },

    async enableDate(id) {
        const response = await api.delete(`/admin/disabled-dates/${id}`);
        return response.data;
    },

    async getConflictAttempts(params = {}) {
        const response = await api.get('/admin/conflict-attempts', { params });
        return response.data;
    },

    async getConflictStats() {
        const response = await api.get('/admin/conflict-attempts/stats');
        return response.data;
    },
};
