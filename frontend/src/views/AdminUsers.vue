<template>
    <div class="admin-users-container">
        <div class="admin-header">
            <h1 class="admin-title">User Management</h1>
            <p class="admin-subtitle">Manage all users and their permissions</p>
        </div>

        <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ error }}
            <button type="button" class="btn-close" @click="error = null"></button>
        </div>

        <div class="admin-controls">
            <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Search users by name or email..."
                class="search-input"
            >
            <button @click="loadUsers" class="btn btn-primary" :disabled="loading">
                {{ loading ? 'Refreshing...' : 'Refresh' }}
            </button>
        </div>

        <div v-if="loading && !users.length" class="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div v-else-if="filteredUsers.length > 0" class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in filteredUsers" :key="user.id" class="user-row">
                        <td>{{ user.id }}</td>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <span v-if="user.is_admin" class="badge bg-danger">Admin</span>
                            <span v-else class="badge bg-secondary">User</span>
                        </td>
                        <td>{{ formatDate(user.created_at) }}</td>
                        <td class="action-cell">
                            <div class="action-buttons">
                                <button 
                                    v-if="user.is_admin"
                                    @click="demoteUser(user.id)"
                                    class="btn btn-sm btn-warning"
                                    :disabled="loading"
                                >
                                    Demote
                                </button>
                                <button 
                                    v-else
                                    @click="promoteUser(user.id)"
                                    class="btn btn-sm btn-success"
                                    :disabled="loading"
                                >
                                    Promote
                                </button>
                                <button 
                                    @click="deleteUser(user.id)"
                                    class="btn btn-sm btn-danger"
                                    :disabled="loading"
                                >
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>No users found</p>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useRouter } from 'vue-router';
import apiClient from '@/services/api';

const router = useRouter();
const authStore = useAuthStore();

const users = ref([]);
const loading = ref(false);
const error = ref(null);
const searchQuery = ref('');

const filteredUsers = computed(() => {
    if (!searchQuery.value) {
        return users.value;
    }
    const query = searchQuery.value.toLowerCase();
    return users.value.filter(user =>
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query)
    );
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const loadUsers = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await apiClient.get('/admin/users');
        users.value = response.data.users;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load users';
    } finally {
        loading.value = false;
    }
};

const promoteUser = async (userId) => {
    loading.value = true;
    error.value = null;

    try {
        await apiClient.put(`/admin/users/${userId}/promote`);
        await loadUsers();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to promote user';
    } finally {
        loading.value = false;
    }
};

const demoteUser = async (userId) => {
    loading.value = true;
    error.value = null;

    try {
        await apiClient.put(`/admin/users/${userId}/demote`);
        await loadUsers();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to demote user';
    } finally {
        loading.value = false;
    }
};

const deleteUser = async (userId) => {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        await apiClient.delete(`/admin/users/${userId}`);
        await loadUsers();
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to delete user';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (!authStore.isAdmin) {
        router.push('/');
        return;
    }
    loadUsers();
});
</script>

<style scoped>
.admin-users-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    background: radial-gradient(ellipse at top, #d4eaff, #b0cef0);
    min-height: 100vh;
    font-family: 'Segoe UI', 'Tahoma', sans-serif;
}

.admin-header {
    margin-bottom: 2rem;
}

.admin-title {
    font-size: 2rem;
    font-weight: 400;
    color: #1e3c5c;
    margin-bottom: 0.5rem;
    text-shadow: 0 1px 0 rgba(255,255,255,0.5);
}

.admin-subtitle {
    font-size: 1rem;
    color: #1e3c5c;
    text-shadow: 0 1px 0 rgba(255,255,255,0.4);
}

.admin-controls {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.search-input {
    flex: 1;
    min-width: 200px;
    padding: 0.8rem 1rem;
    border: 1px solid rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.7);
    color: #1e1e1e;
}

.search-input:focus {
    outline: none;
    border-color: #4da6ff;
    background: rgba(255, 255, 255, 0.85);
    box-shadow: 0 0 0 3px rgba(77, 166, 255, 0.2);
}

.loading-spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
}

.admin-table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    background: rgba(245, 250, 255, 0.5);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.7);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 4px 12px rgba(0,0,0,0.1);
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    background: transparent;
}

.admin-table thead {
    background: rgba(240, 248, 255, 0.6);
    border-bottom: 1px solid rgba(255, 255, 255, 0.6);
}

.admin-table thead th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #1e3c5c;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.admin-table tbody tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
    transition: background-color 0.2s;
}

.admin-table tbody tr:hover {
    background: rgba(255, 255, 255, 0.3);
}

.admin-table tbody td {
    padding: 1rem;
    color: #1e1e1e;
}

.action-cell {
    padding: 0.8rem 1rem;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-buttons .btn {
    font-size: 0.85rem;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    background: linear-gradient(180deg, rgba(255,255,255,0.85) 0%, rgba(210,230,250,0.75) 100%);
    border: 1px solid rgba(255,255,255,0.7);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.7), 0 1px 2px rgba(0,0,0,0.05);
    color: #1e3c5c;
}

.btn-sm {
    padding: 0.25rem 0.8rem;
}

.btn-warning {
    background: linear-gradient(180deg, rgba(255,193,7,0.85), rgba(255,160,0,0.75));
}
.btn-success {
    background: linear-gradient(180deg, rgba(40,167,69,0.85), rgba(30,130,50,0.75));
}
.btn-danger {
    background: linear-gradient(180deg, rgba(220,53,69,0.85), rgba(180,40,50,0.75));
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: rgba(245, 250, 255, 0.45);
    backdrop-filter: blur(8px);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.7);
    color: #1e3c5c;
}

.badge {
    font-size: 0.8rem;
    padding: 0.3rem 0.7rem;
    border-radius: 4px;
    background: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.5);
    color: #1e3c5c;
}

@media (max-width: 768px) {
    .admin-users-container {
        padding: 1rem;
    }
    .action-buttons {
        flex-direction: column;
    }
}
</style>