<template>
  <div class="marketplace">
    <div class="marketplace-header">
      <div class="header-info">
        <h1 class="page-title">Tirgojums</h1>
        <p class="page-subtitle">Pērciet un pārdodiet preces tiešsaistē</p>
      </div>
      <div class="header-action">
        <button 
          @click="showCreateForm = !showCreateForm" 
          class="btn btn-outline"
          :disabled="!authStore.isAuthenticated"
          :title="!authStore.isAuthenticated ? 'Jums jāpielogojās, lai varētu pārdot preces' : ''"
        >
          {{ showCreateForm ? 'Atcelt' : 'Pārdot preci' }}
        </button>
      </div>
    </div>

    <div v-if="showCreateForm" class="form-card glass-card">
      <div class="form-card-header">
        <h5>Uzskaitīt preci pārdošanai</h5>
      </div>
      <div class="form-card-body">
        <form @submit.prevent="createItem">
          <div class="form-grid">
            <div class="form-section">
              <h6>Pamatinformācija</h6>
              <div class="field">
                <label for="name">Preces nosaukums *</label>
                <input type="text" id="name" v-model="newItem.name" required placeholder="Ko jūs pārdodat?">
              </div>
              <div class="field">
                <label for="category">Kategorija</label>
                <select id="category" v-model="newItem.category">
                  <option value="">Izvēlieties kategoriju</option>
                  <option value="Elektronika">Elektronika</option>
                  <option value="Apģērbs">Apģērbs</option>
                  <option value="Grāmatas">Grāmatas</option>
                  <option value="Mājas">Mājas</option>
                  <option value="Virtuve">Virtuve</option>
                  <option value="Fitness">Fitness</option>
                  <option value="Piederumi">Piederumi</option>
                  <option value="Māksla">Māksla</option>
                  <option value="Skaistums">Skaistums</option>
                  <option value="Kancelejas preces">Kancelejas preces</option>
                  <option value="Pārtika">Pārtika</option>
                  <option value="Dāvanas">Dāvanas</option>
                </select>
              </div>
              <div class="field">
                <label for="condition">Stāvoklis</label>
                <select id="condition" v-model="newItem.condition">
                  <option value="">Izvēlieties stāvokli</option>
                  <option value="Jauns">Jauns</option>
                  <option value="Lietots">Lietots</option>
                  <option value="Atjaunots">Atjaunots</option>
                </select>
              </div>
              <div class="field">
                <label for="description">Apraksts</label>
                <textarea id="description" v-model="newItem.description" rows="3" placeholder="Aprakstiet savu preci..."></textarea>
              </div>
              <div class="field image-field">
                <label for="images">Preces attēli</label>
                <input id="images" type="file" accept="image/*" multiple @change="handleImageSelection">
                <small>Līdz {{ maxImages }} attēliem, JPG/PNG/GIF/WebP, 4MB katrs.</small>
                <div v-if="imagePreviews.length" class="image-preview-grid">
                  <div v-for="(preview, index) in imagePreviews" :key="preview.url" class="image-preview">
                    <img :src="preview.url" :alt="preview.name">
                    <button type="button" class="image-remove" @click="removeSelectedImage(index)">×</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6>Cena un Piegāde</h6>
              <div class="field-group">
                <div class="field half">
                  <label for="price">Cena (€) *</label>
                  <div class="input-symbol">
                    <span class="symbol">€</span>
                    <input type="number" id="price" v-model="newItem.price" required min="0" step="0.01" placeholder="0.00">
                  </div>
                </div>
                <div class="field half">
                  <label for="quantity">Daudzums *</label>
                  <input type="number" id="quantity" v-model="newItem.quantity" required min="0" placeholder="Cik daudz?">
                </div>
              </div>
              <div class="field">
                <label for="shipping_type">Piegādes veids *</label>
                <select id="shipping_type" v-model="newItem.shipping_type" required>
                  <option value="free">Bezmaksas piegāde</option>
                  <option value="flat_rate">Fiksēta cena</option>
                  <option value="calculated">Aprēķināta</option>
                </select>
              </div>
              <div v-if="newItem.shipping_type !== 'free'" class="field">
                <label for="shipping_cost">Piegādes izmaksas (€)</label>
                <div class="input-symbol">
                  <span class="symbol">€</span>
                  <input type="number" id="shipping_cost" v-model="newItem.shipping_cost" min="0" step="0.01" placeholder="0.00">
                </div>
              </div>

              <div class="summary-card glass-card">
                <h6>Cenas kopsavilkums</h6>
                <div class="summary-row"><span>Preces cena:</span><span>{{ formatCurrency(newItem.price || 0) }}</span></div>
                <div class="summary-row"><span>Piegāde:</span><span>{{ calculateShipping() }}</span></div>
                <hr class="divider">
                <div class="summary-row total"><span>Kopā:</span><span>{{ formatCurrency(calculateTotal()) }}</span></div>
              </div>
            </div>
          </div>
          <div class="form-actions">
            <button type="button" @click="showCreateForm = false" class="btn btn-outline">Atcelt</button>
            <button type="submit" class="btn btn-solid" :disabled="creating">{{ creating ? 'Uzskaitīju...' : 'Uzskaitīt' }}</button>
          </div>
        </form>
      </div>
    </div>

    <div class="marketplace-grid">
      <!-- Sidebar filters -->
      <aside class="filters-sidebar">
        <div class="filters-card glass-card">
          <div class="filters-header"><h6>Filtri</h6></div>
          <div class="filters-body">
            <div class="filter-group"><label>Meklēt</label><input type="text" v-model="filters.search" placeholder="Meklēt preces..."></div>
            <div class="filter-group"><label>Kategorija</label><div class="checkbox-group"><div v-for="cat in categories" :key="cat"><input type="checkbox" :value="cat" v-model="filters.categories"> {{ cat }}</div></div></div>
            <div class="filter-group"><label>Cena</label><div class="price-range"><input type="number" placeholder="Min" v-model="filters.minPrice"><input type="number" placeholder="Max" v-model="filters.maxPrice"></div></div>
            <div class="filter-group"><label>Stāvoklis</label><select v-model="filters.condition"><option value="">Visi</option><option>Jauns</option><option>Lietots</option><option>Atjaunots</option></select></div>
            <div class="filter-group"><label>Piegāde</label><select v-model="filters.shippingType"><option value="">Visi</option><option value="free">Bezmaksas</option><option value="flat_rate">Fiksēta</option><option value="calculated">Aprēķināta</option></select></div>
            <button @click="clearFilters" class="btn btn-outline btn-small full-width">Notīrīt filtrus</button>
          </div>
        </div>
      </aside>

      <!-- Items grid -->
      <section class="items-section">
        <div v-if="loading" class="state-empty glass-card"><div class="spinner"></div><p>Ielādē...</p></div>
        
        <!-- Enhanced empty state with conditional messaging -->
        <div v-else-if="filteredItems.length === 0" class="state-empty glass-card">
          <div class="empty-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M20 7H4C2.9 7 2 7.9 2 9V19C2 20.1 2.9 21 4 21H20C21.1 21 22 20.1 22 19V9C22 7.9 21.1 7 20 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
              <path d="M16 21V5C16 3.9 15.1 3 14 3H10C8.9 3 8 3.9 8 5V21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
              <path d="M12 7V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M12 13V17" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M9 17H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M9 21H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
          </div>
          <h3 class="empty-title">{{ emptyTitle }}</h3>
          <p class="empty-message">{{ emptyMessage }}</p>
          <div class="empty-actions">
            <button v-if="hasActiveFilters" @click="clearFilters" class="btn btn-outline">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 0.5rem;">
                <path d="M3 6H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M6 12H18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M10 18H14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              Notīrīt filtrus
            </button>
            <button @click="showCreateForm = true" class="btn btn-solid" :disabled="!authStore.isAuthenticated">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 0.5rem;">
                <path d="M12 5V19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M5 12H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              {{ hasActiveFilters ? 'Pievienot jaunu preci' : 'Pievienot preci' }}
            </button>
          </div>
        </div>
        
        <div v-else class="items-grid">
          <div v-for="item in filteredItems" :key="item.id" class="item-card glass-card" @click="openModal(item)">
            <div class="item-card-image">
              <img v-if="primaryImage(item)" :src="primaryImage(item)" :alt="item.name" loading="lazy" @error="handleImageError">
              <div v-else class="image-placeholder">Nav attēla</div>
            </div>
            <div class="item-card-content">
              <div class="item-header">
                <div class="item-badges"><span class="badge">{{ item.category || 'Cits' }}</span><span :class="'badge ' + conditionClass(item.condition)">{{ item.condition || 'N/A' }}</span></div>
                <div class="item-menu" @click.stop>
                  <button v-if="canEditItem(item)" type="button" class="menu-dots" aria-label="Post actions" @click.stop.prevent="toggleMenu(item.id)">⋯</button>
                  <div v-if="activeMenuId == item.id" class="post-actions-menu" @click.stop><button type="button" @click.stop="startEdit(item)">Rediģēt</button><button type="button" class="danger" @click.stop="deleteItem(item.id)">Dzēst</button></div>
                </div>
              </div>
              <h5 class="item-title">{{ item.name }}</h5>
              <p class="item-description">{{ truncate(item.description) }}</p>
              <p class="item-seller"><i class="bi bi-person"></i> {{ item.user?.name || 'Nezināms' }}</p>
              <div class="item-pricing"><div class="price">{{ formatCurrency(item.price) }} <span> / gab.</span></div><div class="shipping"><span v-if="item.shipping_type === 'free'">🚚 Bezmaksas</span><span v-else-if="item.shipping_type === 'flat_rate'">📦 {{ formatCurrency(item.shipping_cost) }}</span><span v-else>📦 Aprēķināts</span></div></div>
              <div class="item-footer"><span :class="'stock ' + stockClass(item.quantity)">{{ item.quantity }} gab.</span><button @click.stop="addToCart(item)" class="btn btn-outline btn-small"><i class="bi bi-cart-plus"></i> Pievienot</button></div>
            </div>
            <div v-if="editingId === item.id" class="edit-form"><form @submit.prevent="updateItem(item.id)"><input v-model="editData.name" placeholder="Nosaukums"><input v-model.number="editData.price" type="number" placeholder="Cena"><input v-model.number="editData.quantity" type="number" placeholder="Daudzums"><select v-model="editData.shipping_type"><option value="free">Bezmaksas</option><option value="flat_rate">Fiksēta</option><option value="calculated">Aprēķināta</option></select><div class="edit-actions"><button type="submit" class="btn btn-solid btn-small">Saglabāt</button><button type="button" @click="cancelEdit" class="btn btn-outline btn-small">Atcelt</button></div></form></div>
          </div>
        </div>
      </section>
    </div>

    <!-- Cart bar -->
    <div v-if="cart.length" class="cart-summary glass-card"><div class="cart-container"><div class="cart-info"><span>{{ cart.length }} preces</span><span>{{ formatCurrency(cartTotal) }}</span></div><div class="cart-actions"><button @click="clearCart" class="btn btn-outline btn-small">Notīrīt</button><button @click="checkout" class="btn btn-solid">Norēķināties</button></div></div></div>

    <!-- Toasts -->
    <div v-if="message" class="toast success">{{ message }}<button class="toast-close" @click="message = ''">×</button></div>
    <div v-if="error" class="toast error">{{ error }}<button class="toast-close" @click="error = ''">×</button></div>

    <!-- Modal -->
    <div v-if="selectedItem" class="modal-overlay" @click.self="closeModal">
      <div class="modal-card glass-card">
        <div class="modal-header"><h3>{{ selectedItem.name }}</h3><button class="modal-close" @click="closeModal">×</button></div>
        <div class="modal-content">
          <div v-if="itemImageUrls(selectedItem).length" class="modal-images">
            <img v-for="(imageUrl, index) in itemImageUrls(selectedItem)" :key="imageUrl" :src="imageUrl" :alt="`${selectedItem.name} attēls ${index + 1}`" loading="lazy" @error="handleImageError">
          </div>
          <div><strong>Kategorija:</strong> {{ selectedItem.category || '—' }}</div>
          <div><strong>Stāvoklis:</strong> <span :class="'badge ' + conditionClass(selectedItem.condition)">{{ selectedItem.condition }}</span></div>
          <div><strong>Pārdevējs:</strong> {{ selectedItem.user?.name || 'Nezināms' }}</div>
          <div><strong>Apraksts:</strong> {{ selectedItem.description || 'Nav' }}</div>
          <div><strong>Cena:</strong> {{ formatCurrency(selectedItem.price) }}</div>
          <div><strong>Piegāde:</strong> <span v-if="selectedItem.shipping_type === 'free'">Bezmaksas</span><span v-else-if="selectedItem.shipping_type === 'flat_rate'">{{ formatCurrency(selectedItem.shipping_cost) }}</span><span v-else>Aprēķināta</span></div>
          <div><strong>Krājums:</strong> {{ selectedItem.quantity }} gab.</div>
        </div>
        <div class="modal-footer"><button @click="addToCart(selectedItem)" class="btn btn-solid">Pievienot grozam</button><button @click="closeModal" class="btn btn-outline">Aizvērt</button></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
const FALLBACK_ASSET_BASE_URL = API_BASE_URL.replace(/\/api\/?$/, '');
const maxImages = 6;
const items = ref([]);
const loading = ref(false);
const creating = ref(false);
const showCreateForm = ref(false);
const editingId = ref(null);
const message = ref('');
const error = ref('');
const cart = ref([]);
const activeMenuId = ref(null);
const selectedItem = ref(null);
const selectedImages = ref([]);
const imagePreviews = ref([]);

const blankItem = () => ({ name: '', description: '', price: 0, quantity: 1, shipping_type: 'flat_rate', shipping_cost: 0, condition: '', category: '' });
const newItem = ref(blankItem());
const editData = ref({ name: '', price: 0, quantity: 0, shipping_type: 'flat_rate' });
const filters = ref({ search: '', categories: [], minPrice: '', maxPrice: '', condition: '', shippingType: '' });
const categories = ['Elektronika', 'Apģērbs', 'Grāmatas', 'Mājas', 'Virtuve', 'Fitness', 'Piederumi', 'Māksla', 'Skaistums', 'Kancelejas preces', 'Pārtika', 'Dāvanas'];


const hasActiveFilters = computed(() => {
  return filters.value.search !== '' ||
         filters.value.categories.length > 0 ||
         filters.value.minPrice !== '' ||
         filters.value.maxPrice !== '' ||
         filters.value.condition !== '' ||
         filters.value.shippingType !== '';
});

const emptyTitle = computed(() => {
  return hasActiveFilters.value ? 'Nekas nav atrasts' : 'Tukšs tirgus';
});

const emptyMessage = computed(() => {
  if (hasActiveFilters.value) {
    return 'Neatradām nevienu preci, kas atbilstu izvēlētajiem filtriem. Mēģiniet pielāgot filtrus vai pievienojiet jaunu preci.';
  }
  return 'Šobrīd neviena prece nav pieejama. Esiet pirmais, kas pievieno preci pārdošanai!';
});

onMounted(() => {
  fetchItems();
  window.addEventListener('click', handleWindowClick);
});

onBeforeUnmount(() => {
  window.removeEventListener('click', handleWindowClick);
  resetImageSelection();
});

const filteredItems = computed(() => items.value.filter(item => {
  if (filters.value.search && !item.name.toLowerCase().includes(filters.value.search.toLowerCase()) && !item.description?.toLowerCase().includes(filters.value.search.toLowerCase())) return false;
  if (filters.value.categories.length && !filters.value.categories.includes(item.category)) return false;
  if (filters.value.minPrice && Number(item.price) < parseFloat(filters.value.minPrice)) return false;
  if (filters.value.maxPrice && Number(item.price) > parseFloat(filters.value.maxPrice)) return false;
  if (filters.value.condition && item.condition !== filters.value.condition) return false;
  if (filters.value.shippingType && item.shipping_type !== filters.value.shippingType) return false;
  return true;
}));

const cartTotal = computed(() => cart.value.reduce((s, i) => s + Number(i.price || 0), 0));
const formatCurrency = (v) => new Intl.NumberFormat('lv-LV', { style: 'currency', currency: 'EUR' }).format(Number(v || 0));
const truncate = (t, l = 60) => t ? (t.length > l ? t.slice(0, l) + '…' : t) : 'Nav apraksta';
const calculateShipping = () => newItem.value.shipping_type === 'free' ? 'Bezmaksas' : (newItem.value.shipping_type === 'flat_rate' && newItem.value.shipping_cost ? formatCurrency(newItem.value.shipping_cost) : 'Aprēķināts');
const calculateTotal = () => Number(newItem.value.price || 0) + (newItem.value.shipping_type === 'flat_rate' ? Number(newItem.value.shipping_cost || 0) : 0);
const conditionClass = (c) => ({ Jauns: 'badge-new', Lietots: 'badge-used', Atjaunots: 'badge-refurbished' }[c] || 'badge-default');
const stockClass = (q) => Number(q) === 0 ? 'stock-out' : Number(q) < 5 ? 'stock-low' : 'stock-ok';
const canEditItem = (item) => {
 const currentUserId = Number(authStore.user?.id);
 const itemOwnerId = Number(item.user_id);
 const isAdmin = Boolean(authStore.user?.is_admin || authStore.isAdmin);
 return authStore.isAuthenticated && (currentUserId === itemOwnerId || isAdmin);
};
const authHeaders = () => authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {};
const jsonHeaders = () => ({ 'Content-Type': 'application/json', ...authHeaders() });

function buildItemFormData(item) {
  const formData = new FormData();
  Object.entries(item).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      formData.append(key, value);
    }
  });
  selectedImages.value.forEach(file => formData.append('images[]', file));
  return formData;
}

function handleImageSelection(event) {
  const files = Array.from(event.target.files || []);
  const imageFiles = files.filter(file => file.type.startsWith('image/'));

  if (imageFiles.length !== files.length) {
    error.value = 'Var pievienot tikai attēlu failus.';
  }

  resetImageSelection(false);
  selectedImages.value = imageFiles.slice(0, maxImages);
  imagePreviews.value = selectedImages.value.map(file => ({
    name: file.name,
    url: URL.createObjectURL(file),
  }));

  if (imageFiles.length > maxImages) {
    error.value = `Var pievienot ne vairāk kā ${maxImages} attēlus.`;
  }
}

function removeSelectedImage(index) {
  URL.revokeObjectURL(imagePreviews.value[index]?.url);
  selectedImages.value.splice(index, 1);
  imagePreviews.value.splice(index, 1);
}

function resetImageSelection(clearInput = true) {
  imagePreviews.value.forEach(preview => URL.revokeObjectURL(preview.url));
  selectedImages.value = [];
  imagePreviews.value = [];

  if (clearInput) {
    const input = document.getElementById('images');
    if (input) input.value = '';
  }
}

function itemImageUrls(item) {
  if (!item) return [];
  if (Array.isArray(item.image_urls)) return item.image_urls.filter(Boolean);
  if (!Array.isArray(item.images)) return [];

  return item.images
    .filter(Boolean)
    .map(path => path.startsWith('http') ? path : `${FALLBACK_ASSET_BASE_URL}/storage/${path}`);
}

function primaryImage(item) {
  return itemImageUrls(item)[0] || '';
}

function handleImageError(event) {
  event.target.style.display = 'none';
}

async function readErrorMessage(response, fallback) {
  const data = await response.json().catch(() => null);
  if (data?.errors) return Object.values(data.errors).flat().join(' ');
  return data?.message || data?.error || fallback;
}

async function fetchItems() {
  loading.value = true;
  try {
    const res = await fetch(`${API_BASE_URL}/items`);
    if (!res.ok) throw new Error();
    items.value = await res.json();
  } catch {
    error.value = 'Neizdevās ielādēt preces';
  } finally {
    loading.value = false;
  }
}

async function createItem() {
  if (!newItem.value.name.trim()) return error.value = 'Nosaukums obligāts';
  if (!authStore.isAuthenticated) return error.value = 'Jāpiesakās';
  creating.value = true;
  error.value = '';
  try {
    const res = await fetch(`${API_BASE_URL}/items`, {
      method: 'POST',
      headers: authHeaders(),
      body: buildItemFormData(newItem.value),
    });

    if (!res.ok) throw new Error(await readErrorMessage(res, 'Kļūda veidojot preci'));

    const created = await res.json();
    items.value = [created, ...items.value];
    message.value = `"${created.name}" pievienots!`;
    newItem.value = blankItem();
    resetImageSelection();
    showCreateForm.value = false;
    setTimeout(() => message.value = '', 3000);
  } catch (e) {
    error.value = e.message || 'Kļūda veidojot preci';
  } finally {
    creating.value = false;
  }
}

function startEdit(item) {
  editingId.value = item.id;
  editData.value = { name: item.name, price: item.price, quantity: item.quantity, shipping_type: item.shipping_type };
  activeMenuId.value = null;
}
function cancelEdit() { editingId.value = null; }
async function updateItem(id) {
  try {
    const res = await fetch(`${API_BASE_URL}/items/${id}`, { method: 'PUT', headers: jsonHeaders(), body: JSON.stringify(editData.value) });
    if (!res.ok) throw new Error(await readErrorMessage(res, 'Atjaunināšana neizdevās'));
    const updated = await res.json();
    const idx = items.value.findIndex(i => i.id === id);
    if (idx !== -1) items.value[idx] = updated;
    message.value = 'Prece atjaunināta';
    editingId.value = null;
    setTimeout(() => message.value = '', 3000);
  } catch (e) {
    error.value = e.message || 'Atjaunināšana neizdevās';
  }
}
async function deleteItem(id) {
  if (!confirm('Dzēst preci?')) return;
  try {
    const res = await fetch(`${API_BASE_URL}/items/${id}`, { method: 'DELETE', headers: authHeaders() });
    if (!res.ok) throw new Error(await readErrorMessage(res, 'Dzēšana neizdevās'));
    items.value = items.value.filter(i => i.id !== id);
    message.value = 'Prece dzēsta';
    setTimeout(() => message.value = '', 3000);
  } catch (e) {
    error.value = e.message || 'Dzēšana neizdevās';
  }
}
function addToCart(item) {
  if (Number(item.quantity) === 0) return error.value = 'Prece beigusies!';
  cart.value.push({ ...item });
  const idx = items.value.findIndex(i => i.id === item.id);
  if (idx !== -1) items.value[idx].quantity--;
  message.value = `"${item.name}" grozā`;
  setTimeout(() => message.value = '', 2000);
}
function clearCart() { cart.value = []; message.value = 'Grozs notīrīts'; setTimeout(() => message.value = '', 2000); }
function checkout() { alert(`Norēķins: ${formatCurrency(cartTotal.value)} (demo)`); cart.value = []; }
function clearFilters() { filters.value = { search: '', categories: [], minPrice: '', maxPrice: '', condition: '', shippingType: '' }; }
function toggleMenu(id) {
 activeMenuId.value = activeMenuId.value == id ? null : id;
}
function openModal(item) { selectedItem.value = item; }
function closeModal() { selectedItem.value = null; }
function handleWindowClick(e) { if (!e.target.closest('.item-menu')) activeMenuId.value = null; }
</script>

<style scoped>
.marketplace {
  max-width: 1280px;
  margin: 0 auto;
  padding: 2rem;
  font-family: 'Segoe UI', 'Tahoma', sans-serif;
  background: radial-gradient(ellipse at top, #d4eaff, #b0cef0);
  min-height: 100vh;
}
.glass-card {
  background: rgba(245, 250, 255, 0.5);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.7);
  border-radius: 12px;
  box-shadow: inset 0 1px 0 rgba(255,255,255,0.8), 0 8px 20px rgba(0,0,0,0.1);
}
.page-title {
  font-size: 28px;
  font-weight: 300;
  color: #1e3c5c;
  text-shadow: 0 1px 0 rgba(255,255,255,0.5);
}
.marketplace-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1.2rem;
  border-radius: 8px;
  font-weight: 500;
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
.btn-outline:hover, .btn-solid:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.btn-small { padding: 0.3rem 0.8rem; font-size: 0.85rem; }
.form-card { margin-bottom: 2rem; overflow: hidden; }
.form-card-header { padding: 1rem 1.5rem; background: rgba(240,248,255,0.3); border-bottom: 1px solid rgba(255,255,255,0.5); }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
.field { display: flex; flex-direction: column; margin-bottom: 1rem; }
.field label { font-size: 12px; text-transform: uppercase; color: #1e3c5c; margin-bottom: 0.3rem; }
.field input, .field select, .field textarea { padding: 0.6rem; border-radius: 8px; border: 1px solid rgba(255,255,255,0.7); background: rgba(255,255,255,0.7); }
.image-field small { color: #3a6a8e; font-size: 0.78rem; margin-top: 0.25rem; }
.image-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(86px, 1fr)); gap: 0.75rem; margin-top: 0.75rem; }
.image-preview { position: relative; height: 86px; border-radius: 10px; overflow: hidden; border: 1px solid rgba(255,255,255,0.85); background: rgba(255,255,255,0.5); }
.image-preview img { width: 100%; height: 100%; object-fit: cover; display: block; }
.image-remove { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; border-radius: 50%; border: 0; background: rgba(0,0,0,0.55); color: #fff; cursor: pointer; line-height: 1; }
.summary-card { padding: 1rem; margin-top: 1rem; }
.summary-row { display: flex; justify-content: space-between; font-size: 13px; padding: 0.3rem 0; }
.total { font-weight: bold; }
.divider { margin: 0.5rem 0; border-top: 1px solid rgba(0,0,0,0.05); }
.form-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem; }
.marketplace-grid { display: grid; grid-template-columns: 260px 1fr; gap: 2rem; }
.filters-card { overflow: hidden; }
.filters-header { padding: 1rem; background: rgba(240,248,255,0.3); border-bottom: 1px solid rgba(255,255,255,0.5); }
.filters-body { padding: 1rem; }
.filter-group { margin-bottom: 1rem; }
.filter-group label { font-size: 12px; font-weight: 600; color: #1e3c5c; }
.filter-group input, .filter-group select { width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.7); background: rgba(255,255,255,0.7); }
.price-range { display: flex; gap: 0.5rem; }
.checkbox-group div { margin: 0.3rem 0; }
.items-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
.item-card { cursor: pointer; transition: all 0.2s; overflow: visible; position: relative; }
.item-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
.item-card-image { height: 180px; background: rgba(255,255,255,0.35); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.item-card-image img { width: 100%; height: 100%; object-fit: cover; display: block; }
.image-placeholder { color: #3a6a8e; font-size: 0.9rem; opacity: 0.75; }
.item-card-content { padding: 1.2rem; }
.item-header { display: flex; justify-content: space-between; margin-bottom: 0.8rem; }
.item-badges { display: flex; gap: 0.4rem; }
.badge { font-size: 10px; padding: 0.2rem 0.6rem; border-radius: 20px; background: rgba(255,255,255,0.7); color: #1e3c5c; }
.badge-new { background: rgba(76,175,80,0.7); color: #1b5e20; }
.badge-used { background: rgba(255,152,0,0.7); color: #e65100; }
.badge-refurbished { background: rgba(156,39,176,0.7); color: #4a148c; }


.item-menu {
 position: relative;
 z-index: 50;
 flex-shrink: 0;
}

.menu-dots {
 width: 2rem;
 height: 2rem;
 display: inline-flex;
 align-items: center;
 justify-content: center;
 border: 1px solid rgba(255,255,255,0.8);
 border-radius: 999px;
 background: rgba(255,255,255,0.75);
 color: #1e3c5c;
 font-size: 1.4rem;
 font-weight: 700;
 line-height: 1;
 cursor: pointer;
}

.menu-dots:hover,
.menu-dots:focus-visible {
 background: rgba(255,255,255,0.95);
 box-shadow: 0 4px 12px rgba(0,0,0,0.12);
 outline: none;
}

.post-actions-menu {
 position: absolute;
 top: calc(100% + 0.35rem);
 right: 0;
 min-width: 140px;
 display: flex !important;
 flex-direction: column;
 padding: 0.35rem;
 border: 1px solid rgba(255,255,255,0.85);
 border-radius: 10px;
 background: rgba(255,255,255,0.97);
 box-shadow: 0 12px 28px rgba(0,0,0,0.18);
 z-index: 9999;
}

.post-actions-menu button {
 width: 100%;
 padding: 0.55rem 0.75rem;
 border: 0;
 border-radius: 8px;
 background: transparent;
 color: #1e3c5c;
 text-align: left;
 cursor: pointer;
}

.post-actions-menu button:hover {
 background: rgba(77,166,255,0.15);
}

.post-actions-menu button.danger {
 color: #b42318;
}

.post-actions-menu button.danger:hover {
 background: rgba(180,35,24,0.12);
}

.item-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.4rem; }
.item-description { font-size: 0.85rem; color: #2c4c6e; margin-bottom: 0.8rem; }
.item-pricing { margin: 0.8rem 0; }
.price { font-size: 1.3rem; font-weight: 300; }
.shipping { font-size: 0.8rem; color: #3a6a8e; }
.item-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 0.8rem; }
.stock { font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 20px; background: rgba(255,255,255,0.6); }
.stock-ok { color: #2e7d32; }
.stock-low { color: #ed6c02; }
.stock-out { color: #d32f2f; }
.edit-form { padding: 1rem; border-top: 1px solid rgba(255,255,255,0.5); background: rgba(240,248,255,0.3); }
.edit-form input, .edit-form select { width: 100%; margin-bottom: 0.5rem; padding: 0.4rem; border-radius: 6px; border: 1px solid rgba(255,255,255,0.7); }
.edit-actions { display: flex; gap: 0.5rem; }
.cart-summary { position: fixed; bottom: 0; left: 0; width: 100%; padding: 0.8rem 2rem; z-index: 1000; border-radius: 0; border-bottom: none; }
.cart-container { max-width: 1280px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; }
.cart-info { display: flex; gap: 1.5rem; font-weight: 600; }
.toast { position: fixed; bottom: 5rem; right: 1rem; padding: 0.6rem 1rem; border-radius: 8px; background: rgba(255,255,255,0.9); backdrop-filter: blur(8px); border-left: 4px solid; z-index: 1100; }
.toast.success { border-color: #2e7d32; }
.toast.error { border-color: #d32f2f; }
.toast-close { margin-left: 1rem; background: none; border: none; cursor: pointer; }
.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-card { max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; padding: 1.5rem; }
.modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
.modal-content > div { margin-bottom: 0.8rem; }
.modal-images { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1rem; }
.modal-images img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 10px; background: rgba(255,255,255,0.5); }
.modal-footer { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem; }


.state-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 4rem 2rem;
  min-height: 400px;
}

.empty-icon {
  margin-bottom: 1.5rem;
  color: #3a7ca5;
  opacity: 0.8;
}

.empty-icon svg {
  width: 80px;
  height: 80px;
  stroke: currentColor;
}

.empty-title {
  font-size: 1.8rem;
  font-weight: 300;
  color: #1e3c5c;
  margin: 0 0 0.75rem 0;
  letter-spacing: -0.5px;
}

.empty-message {
  color: #2c5a7a;
  font-size: 1rem;
  max-width: 400px;
  margin: 0 0 1.5rem 0;
  line-height: 1.5;
  opacity: 0.9;
}

.empty-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
}

.empty-actions .btn {
  padding: 0.6rem 1.2rem;
  font-size: 0.9rem;
}


.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(30, 60, 92, 0.1);
  border-top-color: #1e3c5c;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}


@media (max-width: 768px) {
  .marketplace-grid { grid-template-columns: 1fr; }
  .form-grid { grid-template-columns: 1fr; }
  .state-empty {
    padding: 3rem 1.5rem;
    min-height: 300px;
  }
  .empty-title {
    font-size: 1.4rem;
  }
  .empty-message {
    font-size: 0.9rem;
    padding: 0 1rem;
  }
  .empty-actions {
    flex-direction: column;
    width: 100%;
  }
  .empty-actions .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>