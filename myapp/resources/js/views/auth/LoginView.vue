<template>
    <div class="min-h-screen bg-gradient-to-b from-[#f5f5f7] to-white flex items-center justify-center px-6">
        <div class="w-full max-w-md">
            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-gradient-to-b from-[#0071e3] to-[#0077ed] rounded-[22px] mx-auto mb-6 flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h1 class="text-[32px] font-semibold text-[#1d1d1f] tracking-tight">Welcome back</h1>
                <p class="text-[#86868b] mt-2">Sign in to your account</p>
            </div>
            <div class="card p-8">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <div v-if="error" class="bg-[#ff3b30]/10 text-[#ff3b30] px-4 py-3 rounded-xl text-sm font-medium">
                        {{ error }}
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="input-field"
                            placeholder="name@example.com"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            class="input-field"
                            placeholder="Enter your password"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="btn-primary w-full mt-6"
                    >
                        <span v-if="loading">Signing in...</span>
                        <span v-else>Sign In</span>
                    </button>
                </form>
                <p class="text-center text-[#86868b] text-sm mt-6">
                    Don't have an account?
                    <router-link to="/register" class="text-[#0071e3] font-medium hover:underline">
                        Create one
                    </router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
    email: '',
    password: '',
});

const loading = ref(false);
const error = ref('');

const handleSubmit = async () => {
    loading.value = true;
    error.value = '';
    try {
        await authStore.login(form);
        if (authStore.isAdmin) {
            router.push('/admin');
        } else {
            router.push('/dashboard');
        }
    } catch (e) {
        error.value = e.response?.data?.message || 'Invalid credentials';
    } finally {
        loading.value = false;
    }
};
</script>
