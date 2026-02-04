<template>
    <div class="min-h-screen bg-gradient-to-b from-[#f5f5f7] to-white flex">
        <aside class="w-64 glass border-r border-black/5 min-h-screen p-4 fixed left-0 top-0">
            <div class="mb-8 px-4 pt-4">
                <h1 class="text-xl font-semibold text-[#1d1d1f] tracking-tight">Admin</h1>
                <p class="text-sm text-[#86868b]">Appointment System</p>
            </div>
            <nav class="space-y-1">
                <router-link
                    to="/admin"
                    class="sidebar-link"
                    :class="{ 'active': $route.name === 'admin-dashboard' }"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Dashboard
                </router-link>
                <router-link
                    to="/admin/appointments/pending"
                    class="sidebar-link"
                    :class="{ 'active': $route.name === 'admin-pending' }"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Pending Requests
                </router-link>
                <router-link
                    to="/admin/appointments/history"
                    class="sidebar-link"
                    :class="{ 'active': $route.name === 'admin-history' }"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    History
                </router-link>
                <router-link
                    to="/admin/time-slots"
                    class="sidebar-link"
                    :class="{ 'active': $route.name === 'admin-time-slots' }"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Time Slots
                </router-link>
                <router-link
                    to="/admin/disabled-dates"
                    class="sidebar-link"
                    :class="{ 'active': $route.name === 'admin-disabled-dates' }"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Disabled Dates
                </router-link>
                <router-link
                    to="/admin/conflicts"
                    class="sidebar-link"
                    :class="{ 'active': $route.name === 'admin-conflicts' }"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Conflicts
                </router-link>
            </nav>
            <div class="absolute bottom-4 left-4 right-4">
                <button
                    @click="logout"
                    class="sidebar-link w-full text-[#ff3b30]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign Out
                </button>
            </div>
        </aside>
        <main class="flex-1 ml-64 p-10">
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
