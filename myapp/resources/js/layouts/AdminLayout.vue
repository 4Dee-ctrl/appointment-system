<template>
    <div class="min-h-screen bg-gray-100">
        <nav class="bg-indigo-800 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <router-link to="/admin" class="text-xl font-semibold text-white">
                            Admin Panel
                        </router-link>
                    </div>
                    <div class="flex items-center space-x-4">
                        <router-link
                            to="/admin"
                            class="text-indigo-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                            :class="{ 'text-white bg-indigo-900': $route.name === 'admin-dashboard' }"
                        >
                            Dashboard
                        </router-link>
                        <router-link
                            to="/admin/appointments/pending"
                            class="text-indigo-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                            :class="{ 'text-white bg-indigo-900': $route.name === 'admin-pending' }"
                        >
                            Pending
                        </router-link>
                        <router-link
                            to="/admin/appointments/history"
                            class="text-indigo-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                            :class="{ 'text-white bg-indigo-900': $route.name === 'admin-history' }"
                        >
                            History
                        </router-link>
                        <router-link
                            to="/admin/time-slots"
                            class="text-indigo-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                            :class="{ 'text-white bg-indigo-900': $route.name === 'admin-time-slots' }"
                        >
                            Time Slots
                        </router-link>
                        <router-link
                            to="/admin/disabled-dates"
                            class="text-indigo-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                            :class="{ 'text-white bg-indigo-900': $route.name === 'admin-disabled-dates' }"
                        >
                            Disabled Dates
                        </router-link>
                        <router-link
                            to="/admin/conflicts"
                            class="text-indigo-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
                            :class="{ 'text-white bg-indigo-900': $route.name === 'admin-conflicts' }"
                        >
                            Conflicts
                        </router-link>
                        <button
                            @click="logout"
                            class="text-indigo-200 hover:text-white px-3 py-2 rounded-md text-sm font-medium"
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
