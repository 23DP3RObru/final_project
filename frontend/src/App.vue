<template>
  <div id="app">
    <header class="app-header glass-header">
      <div class="header-container">
        <router-link to="/" class="brand">
          <i class="bi bi-window-dock"></i>
          <span>ProbablyLegit</span>
        </router-link>

        <button 
          class="menu-toggle" 
          @click="mobileMenuOpen = !mobileMenuOpen"
          :aria-expanded="mobileMenuOpen"
          aria-label="Menu"
        >
          <span class="toggle-bar"></span>
          <span class="toggle-bar"></span>
          <span class="toggle-bar"></span>
        </button>

        <nav 
          class="primary-nav" 
          :class="{ 'is-open': mobileMenuOpen }"
        >
          <router-link to="/" class="nav-link" active-class="active" exact @click="mobileMenuOpen = false">
            <i class="bi bi-house-door"></i> Home
          </router-link>
          <router-link to="/items" class="nav-link" active-class="active" @click="mobileMenuOpen = false">
            <i class="bi bi-grid-3x3-gap"></i> Pārlūkot preces
          </router-link>

          <router-link v-if="authStore.isAdmin" to="/admin/users" class="nav-link" active-class="active" @click="mobileMenuOpen = false">
            <i class="bi bi-people"></i> Lietotāju pārvaldība
          </router-link>

          <div v-if="authStore.isAuthenticated" class="auth-section">
            <button @click="dropdownOpen = !dropdownOpen" class="user-btn glass-btn">
              <i class="bi bi-person-circle"></i>
              <span>{{ authStore.user?.name }}</span>
            </button>
            <div v-if="dropdownOpen" class="user-menu glass-dropdown">
              <button @click="handleLogout" class="menu-item">
                <i class="bi bi-box-arrow-right"></i>
                <span>Izrakstīties</span>
              </button>
            </div>
          </div>

          <router-link v-else to="/login" class="nav-link" active-class="active" @click="mobileMenuOpen = false">
            <i class="bi bi-key"></i> Ienākt / Reģistrēties
          </router-link>
        </nav>
      </div>
    </header>

    <main class="app-main">
      <div class="main-container">
        <router-view />
      </div>
    </main>

    <footer class="app-footer glass-footer">
      <div class="footer-container">
        <span class="footer-text">ProbablyLegit © 2026</span>
        <span class="footer-text">Būvēts ar Vue.js + Laravel</span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();
const mobileMenuOpen = ref(false);
const dropdownOpen = ref(false);

const handleLogout = () => {
  authStore.logout();
  dropdownOpen.value = false;
  router.push('/login');
};

onMounted(() => {
  document.addEventListener('click', (e) => {
    const authSection = document.querySelector('.auth-section');
    if (authSection && !authSection.contains(e.target)) {
      dropdownOpen.value = false;
    }
  });
});
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: radial-gradient(ellipse at top, #d4eaff, #b0cef0);
  background-attachment: fixed;
  font-family: 'Segoe UI', 'Tahoma', 'Lucida Grande', sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: #1e3c5c;
}


.glass-header, .glass-footer {
  background: rgba(245, 250, 255, 0.5); 
  backdrop-filter: blur(10px);          
  border-bottom: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8), 0 2px 10px rgba(0, 0, 0, 0.1);
}

.app-header {
  padding: 0.4rem 0;
  width: 100%;
}

.header-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0.6rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  font-size: 1.5rem;
  font-weight: 500;
  letter-spacing: -0.02em;
  color: #1e3c5c;
  transition: all 0.2s ease;
  text-shadow: 0 1px 1px rgba(255, 255, 255, 0.6);
}

.brand i {
  font-size: 1.7rem;
  color: #1e3c5c;
  filter: drop-shadow(0 1px 1px rgba(255, 255, 255, 0.7));
}

.brand:hover {
  transform: scale(1.02);
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
}

.menu-toggle {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 26px;
  height: 20px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
}

.toggle-bar {
  width: 100%;
  height: 2px;
  background-color: #1e3c5c;
  border-radius: 2px;
  transition: 0.2s;
}

.primary-nav {
  display: flex;
  align-items: center;
  gap: 2rem;
  overflow: visible;
}

.nav-link {
  text-decoration: none;
  color: #1e3c5c;
  font-weight: 500;
  font-size: 1rem;
  padding: 0.4rem 0.8rem;
  letter-spacing: -0.005em;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  border-radius: 8px;
  transition: all 0.2s ease;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.nav-link i {
  font-size: 1.1rem;
  color: #1e3c5c;
}

.nav-link.active {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.9);
  color: #0a2a44;
  font-weight: 600;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8), 0 2px 6px rgba(0, 0, 0, 0.05);
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.4);
  transform: translateY(-1px);
}

.auth-section {
  position: relative;
  display: flex;
  align-items: center;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.4rem 1rem;
  background: rgba(255, 255, 255, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.6);
  border-radius: 20px;
  color: #1e3c5c;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 500;
  font-family: inherit;
  transition: all 0.2s ease;
  backdrop-filter: blur(4px);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.5), 0 1px 2px rgba(0, 0, 0, 0.05);
}

.user-btn:hover {
  background: rgba(255, 255, 255, 0.55);
  border-color: rgba(255, 255, 255, 0.8);
  transform: translateY(-1px);
}

.user-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: rgba(245, 250, 255, 0.85);
  backdrop-filter: blur(12px);
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.7);
  min-width: 160px;
  z-index: 1000;
  padding: 6px 0;
}

.menu-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.6rem 1rem;
  background: none;
  border: none;
  color: #1e3c5c;
  cursor: pointer;
  font-size: 0.9rem;
  font-family: inherit;
  text-align: left;
  transition: background 0.15s ease;
}

.menu-item:hover {
  background: rgba(77, 166, 255, 0.2);
}


.app-main {
  flex: 1;
  display: flex;
  width: 100%;
}

.main-container {
  max-width: 1280px;
  width: 100%;
  margin: 0 auto;
  padding: 2rem 2rem;
  background: transparent;
}


.app-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.6);
  padding: 1.2rem 0;
  margin-top: auto;
}

.footer-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.8rem;
}

.footer-text {
  font-size: 0.85rem;
  color: #1e3c5c;
  font-weight: 500;
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
}


@media (max-width: 768px) {
  .header-container {
    padding: 0.6rem 1.5rem;
    flex-wrap: wrap;
  }

  .menu-toggle {
    display: flex;
  }

  .primary-nav {
    display: none;
    flex-direction: column;
    width: 100%;
    padding: 1.2rem 0 0.5rem 0;
    gap: 1rem;
  }

  .primary-nav.is-open {
    display: flex;
  }

  .nav-link {
    width: 100%;
    padding: 0.6rem 0.8rem;
    border-left: 3px solid transparent;
    background: rgba(255, 255, 255, 0.2);
  }

  .nav-link.active {
    border-left-color: #1e3c5c;
    background: rgba(255, 255, 255, 0.4);
  }

  .auth-section {
    width: 100%;
    position: static;
  }

  .user-btn {
    width: 100%;
    justify-content: flex-start;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
  }

  .user-menu {
    position: static;
    background: transparent;
    backdrop-filter: none;
    box-shadow: none;
    border: none;
    padding-left: 1rem;
    margin-top: 0;
  }

  .menu-item {
    color: #1e3c5c;
    padding: 0.5rem 0.8rem;
  }

  .main-container {
    padding: 1.5rem 1.2rem;
  }

  .footer-container {
    flex-direction: column;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .header-container {
    padding: 0.5rem 1rem;
  }
  .main-container {
    padding: 1rem;
  }
}
</style>