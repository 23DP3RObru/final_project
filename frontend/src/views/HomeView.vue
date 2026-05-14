<template>
  <div class="minimal-marketplace">
    <div class="hero-section glass-card">
      <h1 class="hero-title">ProbablyLegit</h1>
      <p class="hero-subtitle">Pērciet un pārdodiet priekšmetus viegli un droši.</p>
      <div class="hero-actions">
        <router-link to="/items" class="btn btn-outline"><i class="bi bi-shop"></i> Pārlūkot preces</router-link>
        <router-link to="/items" class="btn btn-solid"><i class="bi bi-plus-circle"></i> Pārdot preci</router-link>
      </div>
    </div>

    <div class="features-grid">
      <div class="feature-card glass-card">
        <div class="feature-icon"><i class="bi bi-cart-plus"></i></div>
        <h3>Vienkārša pārdošana</h3>
        <p>Ievieto savus priekšmetus pārdošanā dažu minūšu laikā.</p>
      </div>
      <div class="feature-card glass-card">
        <div class="feature-icon"><i class="bi bi-search"></i></div>
        <h3>Viedie filtri</h3>
        <p>Atrodi tieši to, ko meklē, ar uzlabotajiem filtriem.</p>
      </div>
      <div class="feature-card glass-card">
        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
        <h3>Droša platforma</h3>
        <p>Veidota ar Laravel un Vue.js, lai nodrošinātu drošu pieredzi.</p>
      </div>
    </div>

    <div class="stats-card glass-card">
      <div class="stats-header"><h4>Tirgus statistika</h4></div>
      <div class="stats-grid">
        <div class="stat-item"><span class="stat-value">{{ itemsCount }}</span><span class="stat-label">Piedāvātie priekšmeti</span></div>
        <div class="stat-item"><span class="stat-value">{{ formatCurrency(totalValue) }}</span><span class="stat-label">Kopējā vērtība</span></div>
        <div class="stat-item"><span class="stat-value">{{ categoriesCount }}</span><span class="stat-label">Kategorijas</span></div>
        <div class="stat-item"><span class="stat-value">24/7</span><span class="stat-label">Pieejamība</span></div>
      </div>
    </div>

    <div class="steps-section">
      <h2 class="steps-title">Kā tas darbojas</h2>
      <div class="steps-grid">
        <div class="step-item glass-card">
          <div class="step-number">1</div>
          <div class="step-content"><h5>Ievieto priekšmetus</h5><p>Aizpildi formu ar detaļām, cenu un piegādi.</p></div>
        </div>
        <div class="step-item glass-card">
          <div class="step-number">2</div>
          <div class="step-content"><h5>Pircēji pārlūko</h5><p>Meklē, filtrē un atrod vajadzīgo.</p></div>
        </div>
        <div class="step-item glass-card">
          <div class="step-number">3</div>
          <div class="step-content"><h5>Pabeidz darījumus</h5><p>Pievieno grozam un pabeidz pirkumu (demo).</p></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const itemsCount = ref(0);
const totalValue = ref(0);
const categoriesCount = ref(6);

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0 }).format(amount);
};

onMounted(async () => {
  try {
    const res = await fetch('http://localhost:8000/api/items');
    if (res.ok) {
      const items = await res.json();
      itemsCount.value = items.length;
      totalValue.value = items.reduce((sum, i) => sum + (i.price * i.quantity), 0);
    }
  } catch {
    itemsCount.value = 12;
    totalValue.value = 1890;
  }
});
</script>

<style scoped>
.minimal-marketplace {
  max-width: 1280px;
  margin: 0 auto;
  padding: 3rem 2rem;
  font-family: 'Segoe UI', 'Tahoma', sans-serif;
  background: radial-gradient(ellipse at top, #d4eaff, #b0cef0);
  min-height: 100vh;
}
.glass-card {
  background: rgba(245, 250, 255, 0.5);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.7);
  border-radius: 16px;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 8px 20px rgba(0,0,0,0.1);
}
.hero-section {
  text-align: center;
  padding: 3rem 2rem;
  margin-bottom: 3rem;
}
.hero-title {
  font-size: 48px;
  font-weight: 300;
  color: #1e3c5c;
  text-shadow: 0 1px 0 rgba(255,255,255,0.5);
  margin-bottom: 0.5rem;
}
.hero-subtitle {
  font-size: 18px;
  color: #1e3c5c;
  margin-bottom: 2rem;
}
.hero-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.5rem;
  border-radius: 10px;
  font-weight: 500;
  text-decoration: none;
  transition: 0.2s;
  background: rgba(255,255,255,0.7);
  border: 1px solid rgba(255,255,255,0.8);
  color: #1e3c5c;
  cursor: pointer;
}
.btn-solid {
  background: linear-gradient(180deg, rgba(77,166,255,0.85), rgba(51,133,255,0.75));
  color: white;
}
.btn:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(0,0,0,0.1); }
.features-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
  margin: 4rem 0;
}
.feature-card {
  padding: 2rem 1.5rem;
  text-align: center;
}
.feature-icon i { font-size: 2.8rem; color: #1e3c5c; margin-bottom: 1rem; }
.feature-card h3 { font-size: 1.4rem; font-weight: 500; margin-bottom: 0.75rem; color: #1e3c5c; }
.feature-card p { color: #2c4c6e; line-height: 1.5; }
.stats-card { margin: 4rem 0; overflow: hidden; }
.stats-header { padding: 1rem 2rem; background: rgba(240,248,255,0.3); border-bottom: 1px solid rgba(255,255,255,0.5); }
.stats-header h4 { font-size: 1.1rem; font-weight: 600; color: #1e3c5c; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); padding: 2rem; gap: 1rem; text-align: center; }
.stat-value { font-size: 28px; font-weight: 300; color: #1e3c5c; display: block; }
.stat-label { font-size: 11px; text-transform: uppercase; color: #2c4c6e; font-weight: 500; }
.steps-section { margin-top: 4rem; }
.steps-title { font-size: 28px; font-weight: 300; text-align: center; margin-bottom: 2rem; color: #1e3c5c; }
.steps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
.step-item { display: flex; gap: 1rem; padding: 1.5rem; align-items: flex-start; }
.step-number { width: 48px; height: 48px; background: rgba(255,255,255,0.7); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold; color: #1e3c5c; border: 1px solid rgba(255,255,255,0.9); }
.step-content h5 { font-size: 1.1rem; font-weight: 600; color: #1e3c5c; margin-bottom: 0.2rem; }
.step-content p { color: #2c4c6e; font-size: 0.9rem; }
@media (max-width: 992px) {
  .features-grid, .steps-grid { grid-template-columns: repeat(2, 1fr); }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .features-grid, .steps-grid, .stats-grid { grid-template-columns: 1fr; }
  .hero-title { font-size: 2.5rem; }
}
</style>