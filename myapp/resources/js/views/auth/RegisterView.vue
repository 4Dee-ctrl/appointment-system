<template>
    <div class="min-h-screen bg-gradient-to-b from-[#f5f5f7] to-white flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            <div class="text-center mb-10">
                <div class="w-20 h-20 bg-gradient-to-b from-[#0071e3] to-[#0077ed] rounded-[22px] mx-auto mb-6 flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1 class="text-[32px] font-semibold text-[#1d1d1f] tracking-tight">Create account</h1>
                <p class="text-[#86868b] mt-2">Start scheduling appointments today</p>
            </div>
            <div class="card p-8">
                <form @submit.prevent="handleSubmit" class="space-y-5">
                    <div v-if="error" class="bg-[#ff3b30]/10 text-[#ff3b30] px-4 py-3 rounded-xl text-sm font-medium">
                        {{ error }}
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Full Name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="input-field"
                            placeholder="John Doe"
                        />
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
                            placeholder="Minimum 8 characters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[#1d1d1f] mb-2">Confirm Password</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            required
                            class="input-field"
                            placeholder="Confirm your password"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="btn-primary w-full mt-6"
                    >
                        <span v-if="loading">Creating account...</span>
                        <span v-else>Create Account</span>
                    </button>
                </form>
                <p class="text-center text-[#86868b] text-sm mt-6">
                    Already have an account?
                    <router-link to="/login" class="text-[#0071e3] font-medium hover:underline">
                        Sign in
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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const loading = ref(false);
const error = ref('');

const handleSubmit = async () => {
    loading.value = true;
    error.value = '';
    try {
        await authStore.register(form);
        router.push('/dashboard');
    } catch (e) {
        error.value = e.response?.data?.message || 'Registration failed';
    } finally {
        loading.value = false;
    }
};
</script>
