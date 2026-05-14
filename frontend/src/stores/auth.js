import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import apiClient from '@/services/api';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('auth_token') || null);
  const loading = ref(false);
  const error = ref(null);

  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const isAdmin = computed(() => {
    const adminStatus = user.value?.is_admin;
    return adminStatus === true || adminStatus === 1 || adminStatus === '1';
  });

  // Register user
  const register = async (name, email, password, passwordConfirmation) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await apiClient.post('/auth/register', {
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      
      user.value = response.data.user;
      token.value = response.data.token;
      localStorage.setItem('auth_token', token.value);
      
      // Update axios headers with token
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed';
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  // Login user
  const login = async (email, password) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await apiClient.post('/auth/login', {
        email,
        password,
      });

      user.value = response.data.user;
      token.value = response.data.token;
      localStorage.setItem('auth_token', token.value);
      
      // Update axios headers with token
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      
      console.log('Login successful:', {
        user: response.data.user,
        isAdmin: response.data.user?.is_admin,
        token: token.value
      });
      
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Login failed';
      console.error('Login error:', err);
      throw error.value;
    } finally {
      loading.value = false;
    }
  };

  // Logout user
  const logout = () => {
    user.value = null;
    token.value = null;
    localStorage.removeItem('auth_token');
    delete apiClient.defaults.headers.common['Authorization'];
  };

  // Get current user
  const fetchCurrentUser = async () => {
    if (!token.value) {
      return;
    }

    loading.value = true;
    try {
      const response = await apiClient.get('/auth/me');
      user.value = response.data.user;
      console.log('Fetched current user:', {
        user: response.data.user,
        isAdmin: response.data.user?.is_admin
      });
      return response.data.user;
    } catch (err) {
      console.error('Failed to fetch current user:', err);
      logout();
    } finally {
      loading.value = false;
    }
  };

  // Initialize auth from localStorage
  const initializeAuth = async () => {
    if (token.value) {
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      try {
        await fetchCurrentUser();
        console.log('Auth initialized successfully:', { user: user.value, isAdmin: isAdmin.value });
      } catch (err) {
        console.error('Auth initialization failed:', err);
      }
    }
  };

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    register,
    login,
    logout,
    fetchCurrentUser,
    initializeAuth,
  };
});
