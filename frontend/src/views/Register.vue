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
                    <h1 class="login-title">Create an account</h1>
                    
                    <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ error }}
                        <button type="button" class="btn-close" @click="error = null"></button>
                    </div>

                    <form class="login-form" @submit.prevent="handleSubmit">
                        <div class="form-group">
                            <label for="name" class="form-label">Full name</label>
                            <input 
                                type="text" 
                                id="name" 
                                v-model="formData.name"
                                class="form-input" 
                                placeholder="John Doe" 
                                required
                                :disabled="loading"
                            >
                        </div>
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
                        <div class="form-group">
                            <label for="confirm-password" class="form-label">Confirm password</label>
                            <input 
                                type="password" 
                                id="confirm-password" 
                                v-model="formData.confirmPassword"
                                class="form-input" 
                                placeholder="••••••••" 
                                required
                                :disabled="loading"
                            >
                        </div>
                        <div class="checkbox-group">
                            <div class="checkbox-container">
                                <input 
                                    id="terms" 
                                    type="checkbox" 
                                    v-model="formData.acceptTerms"
                                    class="checkbox-input"
                                    required
                                    :disabled="loading"
                                >
                                <label for="terms" class="checkbox-label">
                                    I accept the <a href="#" class="checkbox-link">Terms and Conditions</a>
                                </label>
                            </div>
                        </div>
                        <button 
                            type="submit" 
                            class="submit-btn"
                            :disabled="loading"
                        >
                            {{ loading ? 'Creating account...' : 'Create an account' }}
                        </button>
                        <p class="login-footer-text">
                            Already have an account? <router-link to="/login" class="login-link">Login here</router-link>
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
    name: '',
    email: '',
    password: '',
    confirmPassword: '',
    acceptTerms: false
});

const loading = ref(false);
const error = ref(null);

const handleSubmit = async () => {
    if (formData.value.password !== formData.value.confirmPassword) {
        error.value = 'Passwords do not match';
        return;
    }

    if (!formData.value.acceptTerms) {
        error.value = 'You must accept the terms and conditions';
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        await authStore.register(
            formData.value.name,
            formData.value.email,
            formData.value.password,
            formData.value.confirmPassword
        );
        router.push('/');
    } catch (err) {
        error.value = err;
    } finally {
        loading.value = false;
    }
};
</script>

<style src="./Register.css"></style>