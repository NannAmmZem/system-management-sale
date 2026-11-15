<template>
  <Head title="POS System - SMS Store" />
  
  <AdminLayout>
    <!-- POS Header with Stats -->
    <div class="pos-header-stats mb-4">
      <v-row>
        <v-col cols="12" sm="6" md="3" xl="3">
          <v-card class="stat-card" color="success" variant="tonal">
            <v-card-text class="d-flex align-center pa-3 pa-sm-4">
              <v-icon :size="$vuetify.display.xs ? 32 : 40" class="mr-2 mr-sm-3">mdi-currency-usd</v-icon>
              <div>
                <div class="text-h6 text-sm-h5 font-weight-bold">${{ todaySales.toFixed(2) }}</div>
                <div class="text-caption">Today's Sales</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" sm="6" md="3" xl="3">
          <v-card class="stat-card" color="info" variant="tonal">
            <v-card-text class="d-flex align-center pa-3 pa-sm-4">
              <v-icon :size="$vuetify.display.xs ? 32 : 40" class="mr-2 mr-sm-3">mdi-receipt</v-icon>
              <div>
                <div class="text-h6 text-sm-h5 font-weight-bold">{{ transactionCount }}</div>
                <div class="text-caption">Transactions</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" sm="6" md="3" xl="3">
          <v-card class="stat-card" color="warning" variant="tonal">
            <v-card-text class="d-flex align-center pa-3 pa-sm-4">
              <v-icon :size="$vuetify.display.xs ? 32 : 40" class="mr-2 mr-sm-3">mdi-clock</v-icon>
              <div>
                <div class="text-h6 font-weight-bold">{{ currentTime }}</div>
                <div class="text-caption">Current Time</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
        
        <v-col cols="12" sm="6" md="3" xl="3">
          <v-card class="stat-card" color="primary" variant="tonal">
            <v-card-text class="d-flex align-center pa-3 pa-sm-4">
              <v-icon :size="$vuetify.display.xs ? 32 : 40" class="mr-2 mr-sm-3">mdi-package-variant</v-icon>
              <div>
                <div class="text-h6 text-sm-h5 font-weight-bold">{{ filteredProducts.length }}</div>
                <div class="text-caption">Available Products</div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </div>

    <!-- Main POS Interface -->
    <v-row class="pos-main-row">
      <!-- Left Column - Products -->
      <v-col cols="12" lg="8" xl="9">
        <v-card class="products-card" :height="$vuetify.display.xs ? 'calc(100vh - 400px)' : 'calc(100vh - 280px)'">
          <v-card-title class="d-flex align-center bg-primary pa-3 pa-sm-4">
            <v-icon class="mr-2">mdi-package-variant</v-icon>
            <span class="text-h6 text-sm-h5">Products</span>
            <v-spacer></v-spacer>
            <v-chip color="white" text-color="primary" size="small">
              {{ filteredProducts.length }} items
            </v-chip>
          </v-card-title>

          <!-- Search and Filters -->
          <div class="pa-3 pa-sm-4 bg-grey-lighten-5">
            <v-row>
              <v-col cols="12" sm="8" md="9" lg="8" xl="9">
                <v-text-field
                  v-model="searchQuery"
                  placeholder="Search products or scan barcode..."
                  prepend-inner-icon="mdi-magnify"
                  variant="outlined"
                  density="compact"
                  hide-details
                  @keyup.enter="handleBarcodeSearch"
                  ref="searchInput"
                  class="search-field"
                >
                  <template #append-inner>
                    <v-btn
                      icon="mdi-barcode-scan"
                      variant="text"
                      size="small"
                      @click="focusSearch"
                    />
                  </template>
                </v-text-field>
              </v-col>
              <v-col cols="12" sm="4" md="3" lg="4" xl="3">
                <v-select
                  v-model="selectedCategory"
                  :items="categoryOptions"
                  label="Category"
                  variant="outlined"
                  density="compact"
                  hide-details
                />
              </v-col>
            </v-row>
          </div>

          <!-- Products Grid -->
          <v-card-text class="products-grid-container pa-2 pa-sm-3 pa-md-4">
            <div class="products-grid">
              <div
                v-for="product in filteredProducts"
                :key="product.id"
                class="product-card"
                @click="addToCart(product)"
              >
                <div class="product-image">
                  <v-img
                    :src="product.image_url || '/images/no-image.svg'"
                    :alt="product.name"
                    :height="$vuetify.display.xs ? 80 : $vuetify.display.sm ? 90 : 100"
                    cover
                  >
                    <template #error>
                      <div class="d-flex align-center justify-center fill-height bg-grey-lighten-2">
                        <v-icon color="grey">mdi-image-off</v-icon>
                      </div>
                    </template>
                  </v-img>
                  
                  <div class="product-overlay">
                    <v-btn
                      icon
                      color="white"
                      :size="$vuetify.display.xs ? 'default' : 'large'"
                      class="add-btn"
                    >
                      <v-icon :size="$vuetify.display.xs ? 24 : 32">mdi-plus</v-icon>
                    </v-btn>
                  </div>
                </div>
                
                <div class="product-info pa-2 pa-sm-3">
                  <h4 class="product-name text-body-2 text-sm-body-1">{{ product.name }}</h4>
                  <div class="product-details">
                    <span class="product-price text-subtitle-2 text-sm-subtitle-1">${{ product.price.toFixed(2) }}</span>
                    <v-chip
                      :color="product.qty > 10 ? 'success' : product.qty > 0 ? 'warning' : 'error'"
                      size="x-small"
                      variant="flat"
                    >
                      {{ product.qty }} left
                    </v-chip>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="filteredProducts.length === 0" class="empty-products">
                <v-icon size="80" color="grey-lighten-1">mdi-package-variant-closed</v-icon>
                <h3 class="mt-4 text-grey">No products found</h3>
                <p class="text-grey-darken-1">Try adjusting your search or category filter</p>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Right Column - Cart -->
      <v-col cols="12" lg="4" xl="3">
        <v-card class="cart-card" :height="$vuetify.display.xs ? 'auto' : 'calc(100vh - 280px)'">
          <v-card-title class="d-flex align-center bg-success pa-3 pa-sm-4">
            <v-icon class="mr-2">mdi-cart</v-icon>
            <span class="text-h6 text-sm-h5">Current Order</span>
            <v-spacer></v-spacer>
            <v-chip
              v-if="cart.length > 0"
              color="white"
              text-color="success"
              size="small"
            >
              {{ cart.length }} items
            </v-chip>
          </v-card-title>

          <div class="cart-content">
            <!-- Cart Items -->
            <div class="cart-items-container" :style="{ maxHeight: $vuetify.display.xs ? '300px' : '400px' }">
              <div v-if="cart.length > 0" class="cart-items-list pa-2 pa-sm-3">
                <div
                  v-for="(item, index) in cart"
                  :key="item.id"
                  class="cart-item-row mb-2 mb-sm-3"
                >
                  <div class="item-image-container">
                    <img
                      :src="item.image_url || '/images/no-image.svg'"
                      :alt="item.name"
                      class="item-image"
                      :style="{ width: $vuetify.display.xs ? '40px' : '50px', height: $vuetify.display.xs ? '40px' : '50px' }"
                      @error="$event.target.src = '/images/no-image.svg'"
                    />
                  </div>
                  
                  <div class="item-details-container flex-grow-1 mx-2 mx-sm-3">
                    <div class="item-name text-body-2 text-sm-body-1 font-weight-medium">{{ item.name }}</div>
                    <div class="item-price text-caption text-sm-body-2 text-grey-darken-1">${{ item.price.toFixed(2) }} each</div>
                  </div>
                  
                  <div class="item-controls-container">
                    <div class="quantity-section d-flex align-center mb-1 mb-sm-2">
                      <button 
                        class="qty-btn minus"
                        :style="{ width: $vuetify.display.xs ? '24px' : '28px', height: $vuetify.display.xs ? '24px' : '28px' }"
                        @click="decrementQuantity(item.id)"
                      >
                        -
                      </button>
                      <span class="qty-display mx-1 mx-sm-2 text-body-2 text-sm-body-1">{{ item.quantity }}</span>
                      <button 
                        class="qty-btn plus"
                        :style="{ width: $vuetify.display.xs ? '24px' : '28px', height: $vuetify.display.xs ? '24px' : '28px' }"
                        @click="incrementQuantity(item.id)"
                      >
                        +
                      </button>
                    </div>
                    
                    <div class="d-flex align-center justify-space-between">
                      <div class="item-total-price text-body-2 text-sm-body-1 font-weight-bold text-success">
                        ${{ (item.price * item.quantity).toFixed(2) }}
                      </div>
                      
                      <button 
                        class="remove-btn ml-2"
                        :style="{ width: $vuetify.display.xs ? '20px' : '24px', height: $vuetify.display.xs ? '20px' : '24px' }"
                        @click="removeFromCart(item.id)"
                      >
                        ×
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty Cart -->
              <div v-else class="empty-cart text-center pa-4 pa-sm-6">
                <v-icon :size="$vuetify.display.xs ? 60 : 80" color="grey-lighten-1">mdi-cart-outline</v-icon>
                <h3 class="mt-3 mt-sm-4 text-grey text-h6 text-sm-h5">Cart is empty</h3>
                <p class="text-grey-darken-1 text-body-2 text-sm-body-1">Add products to start a new sale</p>
              </div>
            </div>

            <!-- Always show payment section when cart has items -->
            <div v-show="cart.length > 0" class="cart-footer-section pa-3 pa-sm-4">
              <!-- Summary -->
              <div class="cart-summary-section mb-3 mb-sm-4">
                <h4 class="summary-title text-subtitle-1 text-sm-h6 font-weight-bold mb-2 mb-sm-3">Order Summary</h4>
                
                <div class="summary-row d-flex justify-space-between align-center mb-1 mb-sm-2">
                  <span class="text-body-2 text-sm-body-1">Subtotal:</span>
                  <span class="summary-value text-body-2 text-sm-body-1 font-weight-medium">${{ subtotal.toFixed(2) }}</span>
                </div>
                
                <div class="summary-row d-flex justify-space-between align-center mb-1 mb-sm-2">
                  <span class="text-body-2 text-sm-body-1">Discount:</span>
                  <input
                    v-model="discountAmount"
                    type="number"
                    min="0"
                    :max="subtotal"
                    class="discount-input text-body-2"
                    :style="{ width: $vuetify.display.xs ? '60px' : '70px', fontSize: $vuetify.display.xs ? '12px' : '14px' }"
                    placeholder="0.00"
                  />
                </div>
                
                <div class="summary-row d-flex justify-space-between align-center mb-2 mb-sm-3">
                  <span class="text-body-2 text-sm-body-1">Tax ({{ taxRate }}%):</span>
                  <span class="summary-value text-body-2 text-sm-body-1 font-weight-medium">${{ tax.toFixed(2) }}</span>
                </div>
                
                <div class="summary-divider my-2 my-sm-3"></div>
                
                <div class="summary-row total-row d-flex justify-space-between align-center">
                  <span class="total-label text-subtitle-1 text-sm-h6 font-weight-bold">Total:</span>
                  <span class="total-value text-subtitle-1 text-sm-h6 font-weight-bold text-success">${{ total.toFixed(2) }}</span>
                </div>
              </div>

              <!-- Customer & Payment -->
              <div class="payment-section">
                <h4 class="section-title text-subtitle-1 text-sm-h6 font-weight-bold mb-2 mb-sm-3">Customer Information</h4>
                
                <div class="customer-inputs mb-3 mb-sm-4">
                  <input
                    v-model="customerName"
                    type="text"
                    placeholder="Customer Name (Optional)"
                    class="customer-input mb-2"
                    :style="{ fontSize: $vuetify.display.xs ? '12px' : '14px', padding: $vuetify.display.xs ? '6px 8px' : '8px 12px' }"
                  />
                  
                  <input
                    v-model="customerEmail"
                    type="email"
                    placeholder="Customer Email (Optional)"
                    class="customer-input"
                  />
                  
                  <input
                    v-model="customerPhone"
                    type="tel"
                    placeholder="Customer Phone (Optional)"
                    class="customer-input"
                  />
                </div>

                <h4 class="section-title">Payment Method</h4>
                <div class="payment-methods">
                  <div
                    v-for="(method, index) in paymentMethods"
                    :key="method.value"
                    :class="['payment-method-btn', { active: paymentMethodIndex === index }]"
                    @click="paymentMethodIndex = index"
                  >
                    <span class="method-icon">{{ getPaymentIcon(method.value) }}</span>
                    <span class="method-label">{{ method.label }}</span>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="checkout-actions">
                  <button
                    class="clear-btn"
                    @click="clearCart"
                    :disabled="processing"
                  >
                    🗑️ Clear Cart
                  </button>
                  
                  <button
                    class="checkout-btn"
                    @click="processPayment"
                    :disabled="processing"
                  >
                    {{ processing ? '⏳ Processing...' : `💳 Process Payment - $${total.toFixed(2)}` }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Success Modal -->
    <v-dialog v-model="showSuccessModal" max-width="600" persistent>
      <v-card class="success-modal">
        <div class="success-header">
          <div class="success-animation">
            <v-icon size="80" color="white">mdi-check-circle</v-icon>
          </div>
          <h2>Payment Successful!</h2>
          <p>Transaction completed successfully</p>
        </div>

        <div class="success-details" v-if="lastSale">
          <div class="detail-row">
            <span>Order Number:</span>
            <span class="detail-value">{{ lastSale.order_number }}</span>
          </div>
          <div class="detail-row">
            <span>Customer:</span>
            <span class="detail-value">{{ lastSale.customer_name }}</span>
          </div>
          <div class="detail-row">
            <span>Payment Method:</span>
            <span class="detail-value">{{ lastSale.payment_method }}</span>
          </div>
          <div class="detail-row total">
            <span>Total Amount:</span>
            <span class="detail-value">${{ parseFloat(lastSale.total || 0).toFixed(2) }}</span>
          </div>
        </div>

        <div class="success-actions">
          <v-btn
            color="primary"
            variant="outlined"
            @click="printReceipt"
          >
            <v-icon start>mdi-printer</v-icon>
            Print Receipt
          </v-btn>
          
          <v-btn
            color="success"
            @click="closeSuccessModal"
          >
            <v-icon start>mdi-plus</v-icon>
            New Sale
          </v-btn>
        </div>
      </v-card>
    </v-dialog>

    <!-- Notification -->
    <v-snackbar
      v-model="showNotification"
      :color="notificationColor"
      timeout="3000"
      location="top right"
    >
      <div class="notification-content">
        <v-icon class="mr-2">{{ notificationIcon }}</v-icon>
        {{ notificationMessage }}
      </div>
    </v-snackbar>
  </AdminLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  products: Array,
  categories: Array,
  customers: Array,
  recent_sales: Array
});

// State
const searchQuery = ref('');
const selectedCategory = ref('all');
const cart = ref([]);
const customerName = ref('');
const customerEmail = ref('');
const customerPhone = ref('');
const paymentMethodIndex = ref(0);
const discountAmount = ref(0);
const taxRate = ref(10);
const processing = ref(false);
const showSuccessModal = ref(false);
const lastSale = ref(null);
const currentTime = ref('');
const todaySales = ref(0);
const transactionCount = ref(0);
const searchInput = ref(null);

// Notification
const showNotification = ref(false);
const notificationMessage = ref('');
const notificationColor = ref('success');
const notificationIcon = ref('mdi-check');

// Payment methods
const paymentMethods = [
  { value: 'cash', label: 'Cash', icon: 'mdi-cash', color: 'success' },
  { value: 'card', label: 'Card', icon: 'mdi-credit-card', color: 'primary' },
  { value: 'mobile_payment', label: 'Mobile', icon: 'mdi-cellphone', color: 'info' },
  { value: 'bank_transfer', label: 'Transfer', icon: 'mdi-bank', color: 'warning' }
];

// Computed
const categoryOptions = computed(() => {
  return [
    { title: 'All Categories', value: 'all' },
    ...props.categories.map(cat => ({ title: cat.name, value: cat.id }))
  ];
});

const paymentMethod = computed(() => {
  return paymentMethods[paymentMethodIndex.value]?.value || 'cash';
});

const filteredProducts = computed(() => {
  let filtered = props.products;

  // Filter by search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(query) ||
      product.barcode.includes(query)
    );
  }

  // Filter by category
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(product =>
      product.category_id === selectedCategory.value
    );
  }

  return filtered;
});

const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const discount = computed(() => {
  return Math.min(parseFloat(discountAmount.value) || 0, subtotal.value);
});

const tax = computed(() => {
  return (subtotal.value - discount.value) * (taxRate.value / 100);
});

const total = computed(() => {
  return subtotal.value - discount.value + tax.value;
});

// Methods
const addToCart = (product) => {
  const existingItem = cart.value.find(item => item.id === product.id);
  
  if (existingItem) {
    if (existingItem.quantity < product.qty) {
      existingItem.quantity++;
      showNotificationMessage('Item quantity updated', 'success', 'mdi-check');
    } else {
      showNotificationMessage('Not enough stock available', 'warning', 'mdi-alert');
    }
  } else {
    const cartItem = {
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1,
      image_url: product.image_url,
      qty: product.qty
    };
    
    cart.value.push(cartItem);
    showNotificationMessage(`${product.name} added to cart`, 'success', 'mdi-cart-plus');
  }
  
  // Play sound effect
  playSound('beep');
};

const removeFromCart = (productId) => {
  const index = cart.value.findIndex(item => item.id === productId);
  if (index > -1) {
    cart.value.splice(index, 1);
    showNotificationMessage('Item removed from cart', 'info', 'mdi-cart-minus');
  }
};

const incrementQuantity = (productId) => {
  const item = cart.value.find(item => item.id === productId);
  const product = props.products.find(p => p.id === productId);
  
  if (item && product && item.quantity < product.qty) {
    item.quantity++;
  } else {
    showNotificationMessage('Not enough stock available', 'warning', 'mdi-alert');
  }
};

const decrementQuantity = (productId) => {
  const item = cart.value.find(item => item.id === productId);
  if (item) {
    if (item.quantity > 1) {
      item.quantity--;
    } else {
      removeFromCart(productId);
    }
  }
};

const clearCart = () => {
  cart.value = [];
  customerName.value = '';
  customerEmail.value = '';
  customerPhone.value = '';
  discountAmount.value = 0;
  showNotificationMessage('Cart cleared', 'info', 'mdi-cart-off');
};

const processPayment = async () => {
  if (cart.value.length === 0) {
    showNotificationMessage('Cart is empty', 'error', 'mdi-alert');
    return;
  }

  processing.value = true;

  try {
    const requestData = {
      cart: cart.value,
      customer_name: customerName.value || 'Walk-in Customer',
      customer_email: customerEmail.value || null,
      customer_phone: customerPhone.value || null,
      payment_method: paymentMethod.value,
      discount_amount: discount.value,
      tax_rate: taxRate.value,
      notes: null
    };

    console.log('Sending POS request:', requestData);

    const response = await fetch('/pos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: JSON.stringify(requestData)
    });

    console.log('Response status:', response.status);
    
    // Check for CSRF token error (419)
    if (response.status === 419) {
      showNotificationMessage('Session expired. Refreshing page...', 'warning', 'mdi-refresh');
      setTimeout(() => {
        window.location.reload();
      }, 1500);
      return;
    }
    
    if (!response.ok) {
      const errorText = await response.text();
      console.error('Response error:', errorText);
      
      // Check if error text contains 419 or "Page Expired"
      if (errorText.includes('419') || errorText.includes('Page Expired')) {
        showNotificationMessage('Session expired. Refreshing page...', 'warning', 'mdi-refresh');
        setTimeout(() => {
          window.location.reload();
        }, 1500);
        return;
      }
      
      throw new Error(`HTTP ${response.status}: ${errorText}`);
    }

    const result = await response.json();
    console.log('Response data:', result);

    if (result.success) {
      // Ensure numeric values are properly converted
      lastSale.value = {
        ...result.sale,
        total: parseFloat(result.sale.total || 0)
      };
      showSuccessModal.value = true;
      clearCart();
      playSound('success');
      
      // Update stats
      todaySales.value += parseFloat(result.sale.total || 0);
      transactionCount.value++;
      
      showNotificationMessage('Payment processed successfully!', 'success', 'mdi-check-circle');
    } else {
      showNotificationMessage(result.message || 'Payment failed', 'error', 'mdi-alert');
    }
  } catch (error) {
    console.error('Payment error:', error);
    
    // Check if it's a CSRF error
    if (error.message && (error.message.includes('419') || error.message.includes('Page Expired'))) {
      showNotificationMessage('Session expired. Refreshing page...', 'warning', 'mdi-refresh');
      setTimeout(() => {
        window.location.reload();
      }, 1500);
      return;
    }
    
    showNotificationMessage('Payment processing failed: ' + error.message, 'error', 'mdi-alert');
  } finally {
    processing.value = false;
  }
};

const handleBarcodeSearch = () => {
  if (searchQuery.value) {
    const product = props.products.find(p => p.barcode === searchQuery.value);
    if (product) {
      addToCart(product);
      searchQuery.value = '';
    }
  }
};

const focusSearch = () => {
  nextTick(() => {
    if (searchInput.value) {
      searchInput.value.focus();
    }
  });
};

const closeSuccessModal = () => {
  showSuccessModal.value = false;
  lastSale.value = null;
  focusSearch();
};

const printReceipt = () => {
  if (lastSale.value) {
    window.open(`/orders/${lastSale.value.id}/receipt`, '_blank');
  }
};

const playSound = (type) => {
  try {
    const audio = new Audio(`/sounds/${type}.mp3`);
    audio.volume = 0.3;
    audio.play().catch(() => {
      // Ignore audio play errors
    });
  } catch (error) {
    // Ignore audio errors
  }
};

const getPaymentIcon = (method) => {
  const icons = {
    cash: '💵',
    card: '💳',
    mobile_payment: '📱',
    bank_transfer: '🏦'
  };
  return icons[method] || '💳';
};

const showNotificationMessage = (message, color = 'success', icon = 'mdi-check') => {
  notificationMessage.value = message;
  notificationColor.value = color;
  notificationIcon.value = icon;
  showNotification.value = true;
};

const updateTime = () => {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString();
};

// Initialize
onMounted(() => {
  updateTime();
  setInterval(updateTime, 1000);
  
  // Calculate today's sales
  todaySales.value = props.recent_sales.reduce((sum, sale) => sum + sale.total, 0);
  transactionCount.value = props.recent_sales.length;
  
  // Focus search input
  focusSearch();
});
</script>

<style scoped>
.pos-header-stats {
  margin-bottom: 1rem;
}

.stat-card {
  height: 100px;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.pos-main-row {
  height: calc(100vh - 280px);
}

.products-card,
.cart-card {
  display: flex;
  flex-direction: column;
}

.search-field {
  background: white;
}

.products-grid-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
}

.product-card {
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  border-color: #1976D2;
}

.product-image {
  position: relative;
  height: 100px;
  overflow: hidden;
}

.product-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(25, 118, 210, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
  opacity: 1;
}

.add-btn {
  background: rgba(255, 255, 255, 0.2) !important;
  backdrop-filter: blur(10px);
}

.product-info {
  padding: 0.75rem;
}

.product-name {
  font-size: 0.85rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
  line-height: 1.2;
  color: #333;
}

.product-details {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-price {
  color: #4CAF50;
  font-weight: 700;
  font-size: 0.9rem;
}

.empty-products {
  grid-column: 1 / -1;
  text-align: center;
  padding: 3rem 1rem;
}

.cart-card {
  display: flex;
  flex-direction: column;
}

.cart-content {
  display: flex;
  flex-direction: column;
  height: 100%;
  overflow: hidden;
}

.cart-items-container {
  flex: 1;
  overflow-y: auto;
  max-height: 300px;
  min-height: 150px;
  background: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin: 1rem;
}

.cart-items-list {
  padding: 0.5rem;
}

.cart-item-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid #f0f0f0;
  background: white;
  border-radius: 8px;
  margin-bottom: 0.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.cart-item-row:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.item-image-container {
  flex-shrink: 0;
}

.item-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.item-details-container {
  flex: 1;
  min-width: 0;
}

.item-name {
  font-weight: 600;
  font-size: 0.9rem;
  color: #333;
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-price {
  font-size: 0.8rem;
  color: #666;
}

.item-controls-container {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
  flex-shrink: 0;
}

.quantity-section {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #f5f5f5;
  border-radius: 6px;
  padding: 0.25rem;
}

.qty-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: #1976D2;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: bold;
}

.qty-btn:hover {
  background: #1565C0;
}

.qty-btn.minus {
  background: #f44336;
}

.qty-btn.minus:hover {
  background: #d32f2f;
}

.qty-display {
  font-weight: 600;
  min-width: 24px;
  text-align: center;
  font-size: 0.9rem;
}

.item-total-price {
  color: #4CAF50;
  font-weight: 700;
  font-size: 0.9rem;
}

.remove-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: #f44336;
  color: white;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: bold;
}

.remove-btn:hover {
  background: #d32f2f;
}

/* Cart Footer Section */
.cart-footer-section {
  flex-shrink: 0;
  border-top: 1px solid #e0e0e0;
  background: #fafafa;
  padding: 1rem;
  margin: 0 1rem 1rem 1rem;
  border-radius: 8px;
  max-height: 400px;
  overflow-y: auto;
}

.cart-summary-section {
  margin-bottom: 1.5rem;
  background: white;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.summary-title {
  font-size: 1rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 1rem 0;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 0.5rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  font-size: 0.9rem;
}

.summary-value {
  font-weight: 600;
  color: #333;
}

.discount-input {
  width: 80px;
  padding: 0.25rem 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 0.9rem;
  text-align: right;
}

.summary-divider {
  height: 1px;
  background: #e0e0e0;
  margin: 0.5rem 0;
}

.total-row {
  border-top: 1px solid #e0e0e0;
  padding-top: 0.75rem;
  margin-top: 0.5rem;
}

.total-label {
  font-size: 1.1rem;
  font-weight: 700;
  color: #333;
}

.total-value {
  font-size: 1.2rem;
  font-weight: 700;
  color: #4CAF50;
}

.section-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: #333;
  margin: 0 0 0.75rem 0;
}

.customer-inputs {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.customer-input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.9rem;
  background: white;
}

.customer-input:focus {
  outline: none;
  border-color: #1976D2;
  box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
}

.payment-methods {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.payment-method-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0.75rem;
  background: white;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.payment-method-btn:hover {
  border-color: #1976D2;
  background: #f5f5f5;
}

.payment-method-btn.active {
  border-color: #1976D2;
  background: #e3f2fd;
}

.method-icon {
  font-size: 1.5rem;
}

.method-label {
  font-size: 0.8rem;
  font-weight: 500;
  color: #333;
}

.checkout-actions {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.clear-btn {
  width: 100%;
  padding: 0.75rem;
  background: #f44336;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
}

.clear-btn:hover {
  background: #d32f2f;
}

.clear-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.checkout-btn {
  width: 100%;
  padding: 1rem;
  background: #4CAF50;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease;
  box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
}

.checkout-btn:hover {
  background: #45a049;
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(76, 175, 80, 0.4);
}

.checkout-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.empty-cart {
  text-align: center;
  padding: 3rem 1rem;
  color: #666;
}

.cart-footer {
  margin-top: auto;
  border-top: 1px solid #e0e0e0;
  padding: 1rem;
  background: #fafafa;
}

.cart-summary {
  margin-bottom: 1rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  font-size: 0.9rem;
}

.total-row {
  border-top: 1px solid #e0e0e0;
  padding-top: 0.75rem;
  margin-top: 0.5rem;
}

.payment-section {
  margin-top: 1rem;
}

.customer-inputs {
  margin-bottom: 1rem;
}

.payment-methods {
  margin-bottom: 1rem;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.checkout-btn {
  font-weight: 700 !important;
  font-size: 1rem !important;
}

/* Success Modal */
.success-modal {
  background: white !important;
  border-radius: 16px !important;
}

.success-header {
  text-align: center;
  padding: 2rem;
  background: linear-gradient(135deg, #4CAF50, #2E7D32);
  color: white;
  border-radius: 16px 16px 0 0;
}

.success-animation {
  margin-bottom: 1rem;
  animation: bounce 1s ease-in-out;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-10px); }
  60% { transform: translateY(-5px); }
}

.success-header h2 {
  font-size: 1.8rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.success-details {
  padding: 2rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.detail-row.total {
  font-size: 1.2rem;
  font-weight: 700;
  color: #4CAF50;
  border-bottom: none;
  padding-top: 1rem;
}

.detail-value {
  font-weight: 600;
}

.success-actions {
  display: flex;
  gap: 1rem;
  padding: 0 2rem 2rem;
}

.success-actions .v-btn {
  flex: 1;
  height: 50px !important;
  border-radius: 12px !important;
  font-weight: 600 !important;
}

.notification-content {
  display: flex;
  align-items: center;
}

/* Responsive Design for All Devices */

/* Mobile Phones (320px - 480px) */
@media (max-width: 480px) {
  .pos-header-stats .v-col {
    margin-bottom: 0.5rem;
  }
  
  .stat-card .v-card-text {
    padding: 0.75rem !important;
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
  }
  
  .product-card {
    min-height: 140px;
  }
  
  .product-name {
    font-size: 0.75rem;
    line-height: 1.1;
  }
  
  .product-price {
    font-size: 0.8rem;
  }
  
  .cart-item-row {
    padding: 0.5rem;
    gap: 0.5rem;
  }
  
  .item-image {
    width: 35px;
    height: 35px;
  }
  
  .item-name {
    font-size: 0.8rem;
  }
  
  .item-price {
    font-size: 0.7rem;
  }
  
  .qty-btn {
    width: 20px;
    height: 20px;
    font-size: 12px;
  }
  
  .remove-btn {
    width: 20px;
    height: 20px;
    font-size: 14px;
  }
  
  .cart-footer-section {
    padding: 0.75rem;
    margin: 0 0.5rem 0.5rem 0.5rem;
  }
  
  .cart-summary-section {
    padding: 0.75rem;
  }
  
  .summary-title {
    font-size: 0.9rem;
  }
  
  .summary-row {
    font-size: 0.8rem;
    padding: 0.25rem 0;
  }
  
  .total-label {
    font-size: 1rem;
  }
  
  .total-value {
    font-size: 1.1rem;
  }
  
  .customer-input {
    padding: 0.5rem;
    font-size: 0.8rem;
  }
  
  .payment-methods {
    grid-template-columns: 1fr;
    gap: 0.25rem;
  }
  
  .payment-method-btn {
    padding: 0.5rem;
  }
  
  .method-icon {
    font-size: 1.2rem;
  }
  
  .method-label {
    font-size: 0.7rem;
  }
  
  .checkout-btn {
    padding: 0.75rem;
    font-size: 0.9rem;
  }
}

/* Large Mobile Phones (481px - 768px) */
@media (min-width: 481px) and (max-width: 768px) {
  .pos-main-row {
    height: auto;
  }
  
  .products-card,
  .cart-card {
    height: auto !important;
    margin-bottom: 1rem;
  }
  
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
  }
  
  .product-card {
    min-height: 160px;
  }
  
  .cart-items-container {
    max-height: 250px;
  }
  
  .success-actions {
    flex-direction: column;
    gap: 0.75rem;
  }
}

/* Tablets (769px - 1024px) */
@media (min-width: 769px) and (max-width: 1024px) {
  .products-grid {
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
  }
  
  .product-card {
    min-height: 180px;
  }
  
  .cart-items-container {
    max-height: 350px;
  }
  
  .payment-methods {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .success-actions {
    flex-direction: row;
  }
}

/* Small Laptops (1025px - 1366px) */
@media (min-width: 1025px) and (max-width: 1366px) {
  .products-grid {
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
  }
  
  .product-card {
    min-height: 200px;
  }
  
  .cart-items-container {
    max-height: 400px;
  }
}

/* Large Laptops & Desktops (1367px - 1920px) */
@media (min-width: 1367px) and (max-width: 1920px) {
  .products-grid {
    grid-template-columns: repeat(6, 1fr);
    gap: 1.25rem;
  }
  
  .product-card {
    min-height: 220px;
  }
  
  .product-name {
    font-size: 0.9rem;
  }
  
  .product-price {
    font-size: 1rem;
  }
  
  .cart-items-container {
    max-height: 450px;
  }
  
  .cart-summary-section {
    padding: 1.25rem;
  }
  
  .summary-title {
    font-size: 1.1rem;
  }
  
  .total-label {
    font-size: 1.2rem;
  }
  
  .total-value {
    font-size: 1.3rem;
  }
}

/* Ultra-wide Screens (1921px+) */
@media (min-width: 1921px) {
  .products-grid {
    grid-template-columns: repeat(8, 1fr);
    gap: 1.5rem;
  }
  
  .product-card {
    min-height: 240px;
  }
  
  .product-name {
    font-size: 1rem;
  }
  
  .product-price {
    font-size: 1.1rem;
  }
  
  .cart-items-container {
    max-height: 500px;
  }
  
  .cart-summary-section {
    padding: 1.5rem;
  }
  
  .summary-title {
    font-size: 1.2rem;
  }
  
  .summary-row {
    font-size: 1rem;
    padding: 0.75rem 0;
  }
  
  .total-label {
    font-size: 1.3rem;
  }
  
  .total-value {
    font-size: 1.4rem;
  }
  
  .customer-input {
    padding: 1rem;
    font-size: 1rem;
  }
  
  .checkout-btn {
    padding: 1.25rem;
    font-size: 1.1rem;
  }
}

/* Landscape Orientation for Tablets */
@media (min-width: 768px) and (max-width: 1024px) and (orientation: landscape) {
  .pos-main-row {
    height: calc(100vh - 200px);
  }
  
  .products-card,
  .cart-card {
    height: calc(100vh - 200px) !important;
  }
  
  .products-grid {
    grid-template-columns: repeat(5, 1fr);
  }
  
  .cart-items-container {
    max-height: 300px;
  }
}

/* High DPI Displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
  .product-image img {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
  }
  
  .item-image {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
  }
}

/* Print Styles */
@media print {
  .pos-header-stats,
  .products-card,
  .cart-footer-section {
    display: none !important;
  }
  
  .cart-items-list {
    page-break-inside: avoid;
  }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  .product-card,
  .qty-btn,
  .checkout-btn,
  .success-animation {
    animation: none !important;
    transition: none !important;
  }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
  .cart-summary-section,
  .customer-input,
  .payment-method-btn {
    background: #2d2d2d;
    color: white;
    border-color: #444;
  }
  
  .cart-footer-section {
    background: #1e1e1e;
  }
  
  .summary-divider {
    background: #444;
  }
}

/* Focus Styles for Accessibility */
.qty-btn:focus,
.remove-btn:focus,
.customer-input:focus,
.payment-method-btn:focus,
.checkout-btn:focus,
.clear-btn:focus {
  outline: 2px solid #1976D2;
  outline-offset: 2px;
}

/* Touch Device Optimizations */
@media (hover: none) and (pointer: coarse) {
  .product-card {
    transform: none;
  }
  
  .product-overlay {
    opacity: 1;
    background: rgba(25, 118, 210, 0.7);
  }
  
  .qty-btn,
  .remove-btn {
    min-height: 44px;
    min-width: 44px;
  }
  
  .payment-method-btn {
    min-height: 60px;
  }
  
  .checkout-btn {
    min-height: 50px;
  }
}
</style>