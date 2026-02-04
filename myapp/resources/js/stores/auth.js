import { defineStore } from 'pinia';
import { authService } from '../services';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.is_admin || false,
    },

    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const data = await authService.login(credentials);
                this.setAuth(data.user, data.token);
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Login failed';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async register(userData) {
            this.loading = true;
            this.error = null;
            try {
                const data = await authService.register(userData);
                this.setAuth(data.user, data.token);
                return data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Registration failed';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await authService.logout();
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.clearAuth();
            }
        },

        async fetchUser() {
            if (!this.token) return;
            try {
                const data = await authService.getUser();
                this.user = data.user;
                localStorage.setItem('user', JSON.stringify(data.user));
            } catch (error) {
                this.clearAuth();
            }
        },

        setAuth(user, token) {
            this.user = user;
            this.token = token;
            localStorage.setItem('user', JSON.stringify(user));
            localStorage.setItem('token', token);
        },

        clearAuth() {
            this.user = null;
            this.token = null;
            localStorage.removeItem('user');
            localStorage.removeItem('token');
        },
    },
});
