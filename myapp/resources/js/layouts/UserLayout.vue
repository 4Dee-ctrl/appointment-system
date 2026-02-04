<template>
    <div class="min-h-screen bg-gradient-to-b from-[#f5f5f7] to-white">
        <nav class="glass sticky top-0 z-40 border-b border-black/5">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex justify-between h-16 items-center">
                    <router-link to="/dashboard" class="text-xl font-semibold text-[#1d1d1f] tracking-tight">
                        Appointments
                    </router-link>
                    <div class="flex items-center gap-2">
                        <router-link
                            to="/dashboard"
                            class="nav-pill"
                            :class="{ 'active': $route.name === 'user-dashboard' }"
                        >
                            Dashboard
                        </router-link>
                        <router-link
                            to="/appointments"
                            class="nav-pill"
                            :class="{ 'active': $route.name === 'appointments' }"
                        >
                            My Appointments
                        </router-link>
                        <router-link
                            to="/appointments/new"
                            class="btn-primary ml-4"
                        >
                            New Appointment
                        </router-link>
                        <button
                            @click="logout"
                            class="nav-pill ml-2"
                        >
                            Sign Out
                        </button>
                    </div>
                </div>
            </div>
        </nav>
        <main class="max-w-6xl mx-auto px-6 py-10">
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
