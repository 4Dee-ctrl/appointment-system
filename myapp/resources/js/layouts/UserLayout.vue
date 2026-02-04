<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <router-link to="/dashboard" class="text-xl font-semibold text-gray-800">
                            Appointment System
                        </router-link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <router-link
                            to="/dashboard"
                            class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                            :class="{ 'text-indigo-600': $route.name === 'user-dashboard' }"
                        >
                            Dashboard
                        </router-link>
                        <router-link
                            to="/appointments"
                            class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                            :class="{ 'text-indigo-600': $route.name === 'appointments' }"
                        >
                            My Appointments
                        </router-link>
                        <router-link
                            to="/appointments/new"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
                        >
                            New Appointment
                        </router-link>
                        <button
                            @click="logout"
                            class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <slot />
        </main>
    </div>
</template>

<script setup>
import { useAuthStore } from '../stores';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const logout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>
