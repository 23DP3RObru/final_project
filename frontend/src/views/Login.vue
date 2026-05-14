<template>
    <div class="login-container">
        <div class="login-wrapper">
            <div class="login-header">
                <router-link to="/" class="login-brand">
                    <i class="bi bi-shop"></i>
                    <span>ProbablyLegit</span>
                </router-link>
            </div>
            <div class="login-card">
                <div class="login-card-inner">
                    <h1 class="login-title">Welcome back</h1>
                    
                    <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ error }}
                        <button type="button" class="btn-close" @click="error = null"></button>
                    </div>

                    <form class="login-form" @submit.prevent="handleSubmit">
                        <div class="form-group">
                            <label for="email" class="form-label">Your email</label>
                            <input 
                                type="email" 
                                id="email" 
                                v-model="formData.email"
                                class="form-input" 
                                placeholder="name@company.com" 
                                required
                                :disabled="loading"
                            >
                        </div>
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <input 
                                type="password" 
                                id="password" 
                                v-model="formData.password"
                                class="form-input" 
                                placeholder="••••••••" 
                                required
                                :disabled="loading"
                            >
                        </div>
                        <div class="forgot-password-group">
                            <a href="#" class="forgot-password-link">Forgot password?</a>
                        </div>
                        <button 
                            type="submit" 
                            class="submit-btn"
                            :disabled="loading"
                        >
                            {{ loading ? 'Logging in...' : 'Login' }}
                        </button>
                        <p class="login-footer-text">
                            Don't have an account? <router-link to="/register" class="login-link">Create one here</router-link>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const formData = ref({
    email: '',
    password: ''
});

const loading = ref(false);
const error = ref(null);

const handleSubmit = async () => {
    loading.value = true;
    error.value = null;

    try {
        await authStore.login(formData.value.email, formData.value.password);
        router.push('/');
    } catch (err) {
        error.value = err;
    } finally {
        loading.value = false;
    }
};
</script>

<style src="./Login.css"></style>