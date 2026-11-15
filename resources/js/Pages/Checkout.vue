<template>
  <Head title="Secure Checkout - SMS Store" />

  <div class="checkout-container">
    <!-- Animated Background -->
    <div class="animated-bg">
      <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="checkout-nav">
      <div class="nav-container">
        <div class="nav-content">
          <Link href="/" class="brand-logo">
            <v-icon size="28" color="primary">mdi-shopping</v-icon>
            <span>SMS Store</span>
          </Link>
          
          <div class="nav-actions">
            <v-btn icon variant="text" color="white" @click="$inertia.visit('/cart')">
              <v-badge v-if="cartCount > 0" :content="cartCount" color="primary">
                <v-icon>mdi-arrow-left</v-icon>
              </v-badge>
              <v-icon v-else>mdi-arrow-left</v-icon>
            </v-btn>
          </div>
        </div>
      </div>
    </nav>

    <!-- Progress Steps -->
    <div class="progress-container">
      <!-- Session Warning -->
      <v-alert
        v-if="showSessionWarning"
        type="warning"
        variant="tonal"
        closable
        class="mb-4 mx-auto"
        style="max-width: 1200px;"
        @click:close="showSessionWarning = false"
      >
        <div class="d-flex align-center">
          <v-icon class="mr-2">mdi-clock-alert</v-icon>
          <div class="flex-grow-1">
            <strong>Session Warning:</strong> This page has been open for a while. 
            If you experience issues, please refresh the page before placing your order.
          </div>
          <v-btn
            color="warning"
            variant="outlined"
            size="small"
            @click="refreshPage"
            class="ml-4"
          >
            <v-icon start size="small">mdi-refresh</v-icon>
            Refresh Now
          </v-btn>
        </div>
      </v-alert>
      
      <div class="progress-steps">
        <div class="step active">
          <div class="step-circle">
            <v-icon color="white">mdi-shopping-cart</v-icon>
          </div>
          <span>Cart</span>
        </div>
        <div class="step-line active"></div>
        <div class="step active">
          <div class="step-circle">
            <v-icon color="white">mdi-credit-card</v-icon>
          </div>
          <span>Checkout</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
          <div class="step-circle">
            <v-icon color="grey">mdi-check-circle</v-icon>
          </div>
          <span>Complete</span>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="checkout-content">
      <!-- Empty Cart State -->
      <div v-if="cartItems.length === 0" class="empty-state">
        <div class="empty-icon">
          <v-icon size="120" color="grey-darken-2">mdi-cart-outline</v-icon>
        </div>
        <h2>Your cart is empty</h2>
        <p>Add some amazing products to your cart before checking out!</p>
        <Link href="/">
          <v-btn color="primary" size="large" variant="flat" class="mt-6">
            <v-icon start>mdi-shopping</v-icon>
            Start Shopping
          </v-btn>
        </Link>
      </div>

      <!-- Checkout Form -->
      <div v-else class="checkout-grid">
        <!-- Left Column - Forms -->
        <div class="checkout-forms">
          <!-- Customer Information -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon">
                <v-icon color="primary">mdi-account-circle</v-icon>
              </div>
              <div>
                <h3>Customer Information</h3>
                <p>Enter your details for order confirmation</p>
              </div>
              <v-spacer></v-spacer>
              <v-btn 
                color="info" 
                variant="outlined" 
                size="small"
                @click="fillTestData"
                prepend-icon="mdi-auto-fix"
              >
                Fill Test Data
              </v-btn>
            </div>
            
            <div class="form-grid">
              <v-text-field
                v-model="form.customer_name"
                label="Full Name"
                prepend-inner-icon="mdi-account"
                variant="outlined"
                :error-messages="errors.customer_name"
                required
                class="form-field"
              />
              
              <v-text-field
                v-model="form.customer_email"
                label="Email Address"
                prepend-inner-icon="mdi-email"
                variant="outlined"
                type="email"
                :error-messages="errors.customer_email"
                required
                class="form-field"
              />
              
              <v-text-field
                v-model="form.customer_phone"
                label="Phone Number"
                prepend-inner-icon="mdi-phone"
                variant="outlined"
                type="tel"
                :error-messages="errors.customer_phone"
                required
                class="form-field"
              />
            </div>
          </div>

          <!-- Shipping Information -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon">
                <v-icon color="success">mdi-truck-delivery</v-icon>
              </div>
              <div>
                <h3>Shipping Information</h3>
                <p>Where should we deliver your order?</p>
              </div>
            </div>
            
            <div class="form-grid">
              <v-text-field
                v-model="form.shipping_address"
                label="Street Address"
                prepend-inner-icon="mdi-map-marker"
                variant="outlined"
                :error-messages="errors.shipping_address"
                required
                class="form-field full-width"
              />
              
              <v-text-field
                v-model="form.city"
                label="City"
                prepend-inner-icon="mdi-city"
                variant="outlined"
                :error-messages="errors.city"
                required
                class="form-field"
              />
              
              <v-text-field
                v-model="form.postal_code"
                label="Postal Code"
                prepend-inner-icon="mdi-mailbox"
                variant="outlined"
                class="form-field"
              />
            </div>
          </div>

          <!-- Payment Method -->
          <div class="form-section">
            <div class="section-header">
              <div class="section-icon">
                <v-icon color="warning">mdi-credit-card</v-icon>
              </div>
              <div>
                <h3>Payment Method</h3>
                <p>Choose your preferred payment option</p>
              </div>
            </div>
            
            <div class="payment-methods">
              <div
                v-for="method in paymentMethods"
                :key="method.value"
                :class="['payment-card', { 'selected': form.payment_method === method.value }]"
                @click="form.payment_method = method.value"
              >
                <div class="payment-icon">
                  <v-icon :color="method.color" size="32">{{ method.icon }}</v-icon>
                </div>
                <div class="payment-info">
                  <h4>{{ method.title }}</h4>
                  <p>{{ method.description }}</p>
                </div>
                <div class="payment-radio">
                  <v-radio
                    :value="method.value"
                    :model-value="form.payment_method"
                    color="primary"
                  />
                </div>
              </div>
            </div>

            <v-textarea
              v-model="form.notes"
              label="Order Notes (Optional)"
              prepend-inner-icon="mdi-note-text"
              variant="outlined"
              rows="3"
              placeholder="Any special instructions for your order?"
              class="form-field mt-4"
            />
          </div>
        </div>

        <!-- Right Column - Order Summary -->
        <div class="order-summary">
          <div class="summary-card">
            <div class="summary-header">
              <h3>Order Summary</h3>
              <v-chip color="primary" size="small">{{ cartItems.length }} items</v-chip>
            </div>
            
            <!-- Cart Items -->
            <div class="cart-items">
              <div
                v-for="item in cartItems"
                :key="item.id"
                class="cart-item"
              >
                <div class="item-image">
                  <img
                    :src="item.image_url || item.image"
                    :alt="item.name"
                    @error="$event.target.src = '/images/no-image.svg'"
                  />
                </div>
                <div class="item-details">
                  <h4>{{ item.name }}</h4>
                  <p>Qty: {{ item.quantity }}</p>
                </div>
                <div class="item-price">
                  ${{ (item.price * item.quantity).toFixed(2) }}
                </div>
              </div>
            </div>

            <!-- Price Breakdown -->
            <div class="price-breakdown">
              <div class="price-row">
                <span>Subtotal</span>
                <span>${{ subtotal.toFixed(2) }}</span>
              </div>
              <div class="price-row">
                <span>Shipping</span>
                <span>${{ shipping.toFixed(2) }}</span>
              </div>
              <div class="price-row">
                <span>Tax (10%)</span>
                <span>${{ tax.toFixed(2) }}</span>
              </div>
              <div class="price-divider"></div>
              <div class="price-row total">
                <span>Total</span>
                <span>${{ total.toFixed(2) }}</span>
              </div>
            </div>

            <!-- Place Order Button -->
            <v-btn
              color="primary"
              size="x-large"
              block
              variant="flat"
              :loading="processing"
              :disabled="processing"
              @click="processCheckout"
              class="place-order-btn"
            >
              <v-icon start>{{ processing ? 'mdi-loading' : 'mdi-lock-check' }}</v-icon>
              {{ processing ? 'Processing Payment...' : 'Place Order Securely' }}
            </v-btn>

            <div class="security-badges">
              <div class="badge">
                <v-icon size="16" color="success">mdi-shield-check</v-icon>
                <span>SSL Encrypted</span>
              </div>
              <div class="badge">
                <v-icon size="16" color="success">mdi-lock</v-icon>
                <span>Secure Payment</span>
              </div>
              <div class="badge">
                <v-icon size="16" color="success">mdi-truck-fast</v-icon>
                <span>Fast Delivery</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <v-dialog v-model="showSuccessDialog" max-width="700" persistent>
      <v-card class="success-modal">
        <div class="success-animation">
          <div class="checkmark-container">
            <div class="checkmark">
              <v-icon size="80" color="success">mdi-check-circle</v-icon>
            </div>
            <div class="success-ripple"></div>
          </div>
        </div>

        <div class="success-content">
          <h2>🎉 Order Placed Successfully!</h2>
          <p class="order-number">Order #{{ orderDetails?.order_number }}</p>
          
          <div class="success-details" v-if="orderDetails">
            <div class="detail-card">
              <h4>Order Information</h4>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="label">Customer:</span>
                  <span class="value">{{ orderDetails.customer_name }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Email:</span>
                  <span class="value">{{ orderDetails.customer_email }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Phone:</span>
                  <span class="value">{{ orderDetails.customer_phone }}</span>
                </div>
                <div class="detail-item">
                  <span class="label">Payment:</span>
                  <span class="value">{{ orderDetails.payment_method }}</span>
                </div>
                <div class="detail-item" v-if="orderDetails.invoice_number">
                  <span class="label">Invoice #:</span>
                  <span class="value">{{ orderDetails.invoice_number }}</span>
                </div>
              </div>
            </div>

            <div class="total-card">
              <div class="total-amount">${{ typeof orderDetails.total === 'number' ? orderDetails.total.toFixed(2) : parseFloat(orderDetails.total || 0).toFixed(2) }}</div>
              <div class="total-label">Total Amount</div>
            </div>

            <div class="notification-info">
              <v-icon color="success" class="mr-2">mdi-email-check</v-icon>
              <span>Receipt sent to {{ orderDetails.customer_email }}</span>
            </div>
            <div class="notification-info">
              <v-icon color="info" class="mr-2">mdi-bell-ring</v-icon>
              <span>Admin has been notified of your order</span>
            </div>
          </div>

          <div class="success-actions">
            <v-btn
              v-if="orderDetails?.receipt_url"
              color="primary"
              variant="outlined"
              @click="downloadReceipt"
              class="action-btn"
            >
              <v-icon start>mdi-download</v-icon>
              Download Receipt
            </v-btn>
            
            <v-btn
              v-if="orderDetails?.invoice_url"
              color="success"
              variant="outlined"
              @click="downloadInvoice"
              class="action-btn"
            >
              <v-icon start>mdi-file-document</v-icon>
              Download Invoice
            </v-btn>
            
            <v-btn
              color="success"
              variant="flat"
              @click="closeSuccessDialog"
              class="action-btn"
            >
              <v-icon start>mdi-shopping</v-icon>
              Continue Shopping
            </v-btn>
          </div>
        </div>
      </v-card>
    </v-dialog>

    <!-- Loading Overlay -->
    <v-overlay v-model="processing" class="loading-overlay">
      <div class="loading-content">
        <div class="loading-spinner">
          <v-progress-circular
            indeterminate
            size="80"
            width="6"
            color="primary"
          />
        </div>
        <h3>Processing Your Order</h3>
        <p>Please wait while we secure your purchase...</p>
        <div class="loading-steps">
          <div class="step-item">
            <v-icon color="success">mdi-check</v-icon>
            <span>Validating payment details</span>
          </div>
          <div class="step-item">
            <v-icon color="success">mdi-check</v-icon>
            <span>Processing payment</span>
          </div>
          <div class="step-item">
            <v-icon color="primary">mdi-loading mdi-spin</v-icon>
            <span>Generating receipt</span>
          </div>
        </div>
      </div>
    </v-overlay>

    <!-- Notification Snackbar -->
    <v-snackbar
      v-model="showSnackbar"
      :color="snackbarColor"
      :timeout="snackbarColor === 'error' ? 10000 : 5000"
      location="top"
      class="custom-snackbar"
      multi-line
    >
      <div class="snackbar-content">
        <v-icon class="mr-2">{{ snackbarIcon }}</v-icon>
        <div>
          <div>{{ snackbarMessage }}</div>
          <v-btn
            v-if="snackbarColor === 'error' && snackbarMessage.includes('Session expired')"
            color="white"
            variant="outlined"
            size="small"
            class="mt-2"
            @click="refreshPage"
          >
            <v-icon start size="small">mdi-refresh</v-icon>
            Refresh Page Now
          </v-btn>
        </div>
      </div>
      <template #actions>
        <v-btn icon="mdi-close" variant="text" @click="showSnackbar = false" />
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useProductStore } from '@/Stores/productStore';

const store = useProductStore();
const page = usePage();

// Track page load time to warn about session expiration
const pageLoadTime = ref(Date.now());
const showSessionWarning = ref(false);

// Check for flash data on mount
onMounted(() => {
  try {
    const flash = page.props.flash;
    console.log('Mounted - checking flash data:', flash);
    
    if (flash && flash.success && flash.order) {
      console.log('Found flash data on mount:', flash);
      // Ensure numeric values are properly converted
      const order = {
        ...flash.order,
        total: typeof flash.order.total === 'number' ? flash.order.total : parseFloat(flash.order.total || 0)
      };
      orderDetails.value = order;
      showSuccessDialog.value = true;
      showNotification('Order placed successfully! 🎉', 'success', 'mdi-check-circle');
    }
    
    // Check session age every 30 seconds
    setInterval(() => {
      const minutesOpen = Math.floor((Date.now() - pageLoadTime.value) / 60000);
      if (minutesOpen >= 30) {
        showSessionWarning.value = true;
      }
    }, 30000);
  } catch (error) {
    console.error('Error in onMounted:', error);
  }
});

// Form data
const form = ref({
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  shipping_address: '',
  city: '',
  postal_code: '',
  payment_method: 'cash',
  notes: ''
});

// Enhanced payment methods with better descriptions
const paymentMethods = [
  {
    value: 'cash',
    title: 'Cash on Delivery',
    description: 'Pay when you receive your order',
    icon: 'mdi-cash',
    color: 'success'
  },
  {
    value: 'card',
    title: 'Credit/Debit Card',
    description: 'Visa, Mastercard, American Express',
    icon: 'mdi-credit-card',
    color: 'primary'
  },
  {
    value: 'mobile_payment',
    title: 'Mobile Payment',
    description: 'PayPal, Apple Pay, Google Pay',
    icon: 'mdi-cellphone-nfc',
    color: 'info'
  },
  {
    value: 'bank_transfer',
    title: 'Bank Transfer',
    description: 'Direct bank account transfer',
    icon: 'mdi-bank',
    color: 'warning'
  }
];

// State
const errors = ref({});
const processing = ref(false);
const showSuccessDialog = ref(false);
const orderDetails = ref(null);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');
const snackbarIcon = ref('mdi-check-circle');

// Computed
const cartItems = computed(() => store.cart || []);
const cartCount = computed(() => store.cartItemCount);
const favoriteCount = computed(() => store.favoriteCount);
const subtotal = computed(() => store.cartTotal || 0);
const tax = computed(() => subtotal.value * 0.10);
const shipping = computed(() => cartItems.value.length > 0 ? 9.99 : 0);
const total = computed(() => subtotal.value + tax.value + shipping.value);

// Enhanced validation with better user feedback
const validateForm = () => {
  errors.value = {};
  
  // Name validation
  if (!form.value.customer_name || !form.value.customer_name.trim()) {
    errors.value.customer_name = 'Full name is required';
  } else if (form.value.customer_name.trim().length < 2) {
    errors.value.customer_name = 'Name must be at least 2 characters';
  }
  
  // Email validation
  if (!form.value.customer_email || !form.value.customer_email.trim()) {
    errors.value.customer_email = 'Email address is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.customer_email.trim())) {
    errors.value.customer_email = 'Please enter a valid email address';
  }
  
  // Phone validation (more lenient)
  if (!form.value.customer_phone || !form.value.customer_phone.trim()) {
    errors.value.customer_phone = 'Phone number is required';
  } else {
    const cleanPhone = form.value.customer_phone.replace(/[\s\-\(\)\.]/g, '');
    if (cleanPhone.length < 10) {
      errors.value.customer_phone = 'Please enter a valid phone number (at least 10 digits)';
    }
  }
  
  // Address validation (more lenient)
  if (!form.value.shipping_address || !form.value.shipping_address.trim()) {
    errors.value.shipping_address = 'Shipping address is required';
  } else if (form.value.shipping_address.trim().length < 5) {
    errors.value.shipping_address = 'Please enter a complete address (at least 5 characters)';
  }
  
  // City validation
  if (!form.value.city || !form.value.city.trim()) {
    errors.value.city = 'City is required';
  }
  
  // Debug logging
  console.log('Form validation results:', {
    hasErrors: Object.keys(errors.value).length > 0,
    errors: errors.value,
    formData: {
      customer_name: form.value.customer_name,
      customer_email: form.value.customer_email,
      customer_phone: form.value.customer_phone,
      shipping_address: form.value.shipping_address,
      city: form.value.city
    }
  });
  
  return Object.keys(errors.value).length === 0;
};

// Enhanced checkout process
const processCheckout = async () => {
  console.log('Starting checkout process...');
  console.log('Form data:', form.value);
  
  const validationResult = validateForm();
  console.log('Validation result:', validationResult);
  
  if (!validationResult) {
    // Show specific field errors if available
    const errorFields = Object.keys(errors.value);
    const firstError = errors.value[errorFields[0]];
    showNotification(firstError || 'Please fill in all required fields correctly', 'error', 'mdi-alert-circle');
    return;
  }
  
  if (cartItems.value.length === 0) {
    showNotification('Your cart is empty!', 'warning', 'mdi-cart-outline');
    return;
  }
  
  processing.value = true;
  
  const orderData = {
    customer_name: form.value.customer_name,
    customer_email: form.value.customer_email,
    customer_phone: form.value.customer_phone,
    shipping_address: form.value.shipping_address,
    city: form.value.city,
    postal_code: form.value.postal_code,
    payment_method: form.value.payment_method,
    notes: form.value.notes,
    items: cartItems.value.map(item => ({
      product_id: item.id,
      product_name: item.name,
      quantity: item.quantity,
      price: item.price
    })),
    subtotal: subtotal.value,
    tax: tax.value,
    shipping: shipping.value,
    total: total.value
  };
  
  console.log('Order data:', orderData);
  
  try {
    // Use Inertia for form submission with better error handling
    router.post('/checkout/process', orderData, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        console.log('Checkout success response:', page);
        console.log('Page props:', page.props);
        console.log('Flash data:', page.props.flash);
        
        // Clear cart
        store.clearCart();
        
        // Check for flash data in props
        const flash = page.props.flash;
        if (flash && flash.success && flash.order) {
          console.log('Order details from flash:', flash.order);
          // Ensure numeric values are properly converted
          const order = {
            ...flash.order,
            total: typeof flash.order.total === 'number' ? flash.order.total : parseFloat(flash.order.total || 0)
          };
          orderDetails.value = order;
          showSuccessDialog.value = true;
          showNotification('Order placed successfully! 🎉', 'success', 'mdi-check-circle');
        } else {
          // Fallback: check if we're on the checkout page with success data
          console.log('No flash data, checking page component and props...');
          
          // If we're back on checkout page, check for any success indicators
          if (page.component === 'Checkout') {
            // Look for any success data in the response
            const props = page.props;
            if (props.flash && props.flash.success) {
              orderDetails.value = props.flash.order || {
                order_number: 'N/A',
                customer_name: form.value.customer_name,
                customer_email: form.value.customer_email,
                total: total.value
              };
              showSuccessDialog.value = true;
              showNotification('Order placed successfully! 🎉', 'success', 'mdi-check-circle');
            } else {
              console.log('No success data found in response');
              showNotification('Order processed but no confirmation received', 'warning', 'mdi-alert');
            }
          }
        }
      },
      onError: (errors) => {
        console.error('Checkout error:', errors);
        
        // Check if it's a CSRF token error (419) - reload immediately
        if (errors && (errors.message && errors.message.includes('419') || errors['419'])) {
          showNotification('Session expired. Refreshing page...', 'warning', 'mdi-refresh');
          // Reload page immediately to get fresh CSRF token
          setTimeout(() => {
            window.location.reload();
          }, 1000);
          return;
        }
        
        // Handle validation errors
        if (typeof errors === 'object' && errors !== null) {
          const errorMessages = Object.values(errors).flat();
          const firstError = errorMessages[0] || 'Checkout failed. Please try again.';
          showNotification(firstError, 'error', 'mdi-alert-circle');
        } else {
          showNotification('Checkout failed. Please try again.', 'error', 'mdi-alert-circle');
        }
      },
      onFinish: () => {
        try {
          processing.value = false;
        } catch (error) {
          console.error('Error in onFinish:', error);
          processing.value = false;
        }
      }
    });
  } catch (error) {
    console.error('Checkout error:', error);
    
    // Check if it's a network error with 419 status
    if (error.response && error.response.status === 419) {
      showNotification('Session expired. Refreshing page...', 'error', 'mdi-alert-circle');
      setTimeout(() => {
        window.location.reload();
      }, 2000);
    } else {
      showNotification('An error occurred during checkout. Please try again.', 'error', 'mdi-alert-circle');
    }
    processing.value = false;
  }
};

const downloadReceipt = () => {
  if (orderDetails.value?.id) {
    // Use direct download route
    window.open(`/orders/${orderDetails.value.id}/receipt`, '_blank');
  } else {
    showNotification('Receipt not available for download', 'warning', 'mdi-alert');
  }
};

const downloadInvoice = () => {
  if (orderDetails.value?.id) {
    // Use direct download route
    window.open(`/orders/${orderDetails.value.id}/invoice`, '_blank');
  } else {
    showNotification('Invoice not available for download', 'warning', 'mdi-alert');
  }
};

const closeSuccessDialog = () => {
  showSuccessDialog.value = false;
  window.location.href = '/';
};

const showNotification = (message, color = 'success', icon = 'mdi-check-circle') => {
  snackbarMessage.value = message;
  snackbarColor.value = color;
  snackbarIcon.value = icon;
  showSnackbar.value = true;
};

// Helper function to fill form with test data
const fillTestData = () => {
  form.value = {
    customer_name: 'John Doe',
    customer_email: 'john.doe@example.com',
    customer_phone: '+1 (555) 123-4567',
    shipping_address: '123 Main Street, Apartment 4B',
    city: 'New York',
    postal_code: '10001',
    payment_method: 'cash',
    notes: 'Please deliver during business hours'
  };
  showNotification('Test data filled in!', 'info', 'mdi-information');
};

// Refresh page function
const refreshPage = () => {
  window.location.reload();
};
</script>

<style scoped>
/* Modern Checkout Styles */
.checkout-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
  position: relative;
  overflow-x: hidden;
}

/* Animated Background */
.animated-bg {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  pointer-events: none;
}

.floating-shapes {
  position: relative;
  width: 100%;
  height: 100%;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(45deg, rgba(25, 118, 210, 0.1), rgba(156, 39, 176, 0.1));
  animation: float 20s infinite ease-in-out;
}

.shape-1 {
  width: 300px;
  height: 300px;
  top: 10%;
  left: -10%;
  animation-delay: 0s;
}

.shape-2 {
  width: 200px;
  height: 200px;
  top: 60%;
  right: -5%;
  animation-delay: 5s;
}

.shape-3 {
  width: 150px;
  height: 150px;
  top: 30%;
  right: 20%;
  animation-delay: 10s;
}

.shape-4 {
  width: 250px;
  height: 250px;
  bottom: 10%;
  left: 10%;
  animation-delay: 15s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  25% { transform: translateY(-20px) rotate(90deg); }
  50% { transform: translateY(-40px) rotate(180deg); }
  75% { transform: translateY(-20px) rotate(270deg); }
}

/* Navigation */
.checkout-nav {
  position: relative;
  z-index: 10;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.nav-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.nav-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 70px;
}

.brand-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: white;
  text-decoration: none;
  font-size: 1.5rem;
  font-weight: 700;
  transition: all 0.3s ease;
}

.brand-logo:hover {
  transform: scale(1.05);
}

/* Progress Steps */
.progress-container {
  position: relative;
  z-index: 10;
  padding: 2rem 0;
  display: flex;
  justify-content: center;
}

.progress-steps {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  padding: 1.5rem 3rem;
  border-radius: 50px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: rgba(255, 255, 255, 0.6);
  transition: all 0.3s ease;
}

.step.active {
  color: white;
}

.step-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.step.active .step-circle {
  background: linear-gradient(135deg, #1976D2, #1565C0);
  box-shadow: 0 8px 25px rgba(25, 118, 210, 0.3);
}

.step-line {
  width: 60px;
  height: 2px;
  background: rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.step-line.active {
  background: linear-gradient(90deg, #1976D2, #1565C0);
}

/* Main Content */
.checkout-content {
  position: relative;
  z-index: 10;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem 4rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: white;
}

.empty-state h2 {
  font-size: 2.5rem;
  margin: 1rem 0;
  font-weight: 700;
}

.empty-state p {
  font-size: 1.2rem;
  opacity: 0.8;
  margin-bottom: 2rem;
}

/* Checkout Grid */
.checkout-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 3rem;
  align-items: start;
}

@media (max-width: 1024px) {
  .checkout-grid {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

/* Form Sections */
.checkout-forms {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.form-section:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(25, 118, 210, 0.3);
}

.section-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.section-icon {
  width: 50px;
  height: 50px;
  border-radius: 15px;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.section-header h3 {
  color: white;
  font-size: 1.4rem;
  font-weight: 600;
  margin: 0;
}

.section-header p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.form-field.full-width {
  grid-column: 1 / -1;
}

/* Payment Methods */
.payment-methods {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1rem;
}

.payment-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.payment-card:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(25, 118, 210, 0.5);
  transform: translateY(-2px);
}

.payment-card.selected {
  background: rgba(25, 118, 210, 0.15);
  border-color: #1976D2;
  box-shadow: 0 8px 25px rgba(25, 118, 210, 0.2);
}

.payment-icon {
  width: 60px;
  height: 60px;
  border-radius: 15px;
  background: rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.payment-info h4 {
  color: white;
  margin: 0 0 0.25rem 0;
  font-weight: 600;
}

.payment-info p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
}

/* Order Summary */
.order-summary {
  position: sticky;
  top: 2rem;
}

.summary-card {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.summary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.summary-header h3 {
  color: white;
  font-size: 1.4rem;
  font-weight: 600;
  margin: 0;
}

/* Cart Items */
.cart-items {
  max-height: 300px;
  overflow-y: auto;
  margin-bottom: 1.5rem;
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.cart-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 60px;
  height: 60px;
  border-radius: 10px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.1);
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-details {
  flex: 1;
}

.item-details h4 {
  color: white;
  margin: 0 0 0.25rem 0;
  font-size: 0.95rem;
  font-weight: 500;
}

.item-details p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.85rem;
}

.item-price {
  color: #1976D2;
  font-weight: 600;
  font-size: 1rem;
}

/* Price Breakdown */
.price-breakdown {
  margin-bottom: 2rem;
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  color: rgba(255, 255, 255, 0.8);
}

.price-row.total {
  color: white;
  font-size: 1.2rem;
  font-weight: 700;
  padding-top: 1rem;
}

.price-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 0.5rem 0;
}

/* Place Order Button */
.place-order-btn {
  font-size: 1.1rem !important;
  font-weight: 700 !important;
  text-transform: none !important;
  letter-spacing: 0.5px !important;
  height: 60px !important;
  border-radius: 15px !important;
  margin-bottom: 1.5rem !important;
  box-shadow: 0 8px 25px rgba(25, 118, 210, 0.3) !important;
}

/* Security Badges */
.security-badges {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
}

.badge {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.8rem;
}

/* Success Modal */
.success-modal {
  background: rgba(255, 255, 255, 0.03) !important;
  backdrop-filter: blur(20px) !important;
  border: 1px solid rgba(255, 255, 255, 0.1) !important;
  border-radius: 20px !important;
  overflow: hidden !important;
}

.success-animation {
  background: linear-gradient(135deg, #4CAF50, #2E7D32);
  padding: 3rem 2rem;
  text-align: center;
  position: relative;
}

.checkmark-container {
  position: relative;
  display: inline-block;
}

.checkmark {
  animation: checkmark-bounce 1s ease-in-out;
}

.success-ripple {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100px;
  height: 100px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  animation: ripple 2s infinite;
}

@keyframes checkmark-bounce {
  0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
  40% { transform: translateY(-10px); }
  60% { transform: translateY(-5px); }
}

@keyframes ripple {
  0% { transform: translate(-50%, -50%) scale(0.8); opacity: 1; }
  100% { transform: translate(-50%, -50%) scale(2); opacity: 0; }
}

.success-content {
  padding: 2rem;
  color: white;
  text-align: center;
}

.success-content h2 {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.order-number {
  color: #4CAF50;
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 2rem;
}

.success-details {
  margin-bottom: 2rem;
}

.detail-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.detail-card h4 {
  margin-bottom: 1rem;
  color: white;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-item .label {
  color: rgba(255, 255, 255, 0.7);
}

.detail-item .value {
  color: white;
  font-weight: 500;
}

.total-card {
  background: linear-gradient(135deg, #4CAF50, #2E7D32);
  border-radius: 15px;
  padding: 1.5rem;
  margin-bottom: 1rem;
}

.total-amount {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
}

.total-label {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1rem;
}

.notification-info {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0.5rem 0;
  color: rgba(255, 255, 255, 0.8);
}

.success-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.action-btn {
  min-width: 150px !important;
  height: 50px !important;
  border-radius: 12px !important;
  font-weight: 600 !important;
  text-transform: none !important;
}

/* Loading Overlay */
.loading-overlay {
  background: rgba(0, 0, 0, 0.8) !important;
  backdrop-filter: blur(10px) !important;
}

.loading-content {
  text-align: center;
  color: white;
}

.loading-spinner {
  margin-bottom: 2rem;
}

.loading-content h3 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.loading-content p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 2rem;
}

.loading-steps {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: center;
}

.step-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  color: rgba(255, 255, 255, 0.8);
}

/* Custom Snackbar */
.custom-snackbar {
  backdrop-filter: blur(10px) !important;
}

.snackbar-content {
  display: flex;
  align-items: center;
}

/* Responsive Design */
@media (max-width: 768px) {
  .checkout-content {
    padding: 0 1rem 2rem;
  }
  
  .form-section,
  .summary-card {
    padding: 1.5rem;
  }
  
  .progress-steps {
    padding: 1rem 2rem;
    gap: 0.5rem;
  }
  
  .step span {
    font-size: 0.8rem;
  }
  
  .payment-methods {
    grid-template-columns: 1fr;
  }
  
  .security-badges {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .success-actions {
    flex-direction: column;
  }
}

/* Animations */
@keyframes mdi-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.mdi-spin {
  animation: mdi-spin 1s linear infinite;
}
</style>