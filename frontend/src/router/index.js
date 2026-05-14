import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ItemsView from '../views/ItemsView.vue'
import Register from '../views/Register.vue'
import Login from '@/views/Login.vue'
import AdminUsers from '@/views/AdminUsers.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/items',
      name: 'items',
      component: ItemsView
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      meta: { requiresGuest: true }
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: { requiresGuest: true }
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: AdminUsers,
      meta: { requiresAdmin: true }
    },
  ]
})

// Route guards
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  // Debug logging
  console.log('Route Navigation:', {
    to: to.path,
    requiresGuest: to.meta.requiresGuest,
    requiresAdmin: to.meta.requiresAdmin,
    isAuthenticated: authStore.isAuthenticated,
    isAdmin: authStore.isAdmin,
    user: authStore.user
  })
  
  // Redirect authenticated users away from login/register
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/')
    return
  }
  
  // Redirect unauthenticated users to login
  if (to.meta.requiresAdmin && !authStore.isAuthenticated) {
    console.warn('Admin page: User not authenticated')
    next('/login')
    return
  }
  
  // Redirect non-admin users away from admin pages
  if (to.meta.requiresAdmin && !authStore.isAdmin) {
    console.warn('Admin page: User is not admin', { isAdmin: authStore.isAdmin, user: authStore.user })
    next('/')
    return
  }
  
  next()
})

export default router