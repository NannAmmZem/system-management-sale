<template>
  <Head :title="product.name_en" />

  <div class="min-h-screen bg-gradient-to-br from-grey-900 to-black">
    <!-- Navigation -->
    <nav class="bg-black/50 backdrop-blur-lg border-b border-white/10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <Link href="/" class="text-white font-bold text-xl">
              SMS Store
            </Link>
          </div>
          
          <div class="flex items-center space-x-4">
            <v-btn
              icon="mdi-heart"
              variant="text"
              color="white"
              @click="toggleFavorite"
            >
              <v-badge v-if="favoriteCount > 0" :content="favoriteCount" color="error">
                <v-icon>mdi-heart</v-icon>
              </v-badge>
              <v-icon v-else>mdi-heart-outline</v-icon>
            </v-btn>
            
            <v-btn
              icon="mdi-shopping"
              variant="text"
              color="white"
              @click="$inertia.visit('/cart')"
            >
              <v-badge v-if="cartCount > 0" :content="cartCount" color="primary">
                <v-icon>mdi-shopping</v-icon>
              </v-badge>
              <v-icon v-else>mdi-shopping-outline</v-icon>
            </v-btn>
            
            <template v-if="$page.props.auth.user">
              <v-btn variant="outlined" color="white" @click="$inertia.visit('/dashboard')">
                Dashboard
              </v-btn>
            </template>
            <template v-else>
              <v-btn variant="outlined" color="white" @click="$inertia.visit('/login')">
                Login
              </v-btn>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Product Detail Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <Link href="/" class="text-grey-400 hover:text-white transition-colors">
              Home
            </Link>
          </li>
          <li class="text-grey-500">/</li>
          <li>
            <span class="text-grey-400">{{ product.category?.name || 'Products' }}</span>
          </li>
          <li class="text-grey-500">/</li>
          <li class="text-white font-medium">{{ product.name_en }}</li>
        </ol>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Product Images -->
        <div class="space-y-4">
          <div class="aspect-square bg-white/5 rounded-2xl overflow-hidden backdrop-blur-lg border border-white/10">
            <img
              :src="product.image_url"
              :alt="product.name_en"
              class="w-full h-full object-cover"
              @error="handleImageError"
            />
          </div>
          
          <!-- Thumbnail Gallery (if multiple images in future) -->
          <div class="grid grid-cols-4 gap-2">
            <div 
              v-for="i in 4" 
              :key="i"
              class="aspect-square bg-white/5 rounded-lg overflow-hidden backdrop-blur-lg border border-white/10 cursor-pointer hover:border-primary/50 transition-colors"
            >
              <img
                :src="product.image_url"
                :alt="product.name_en"
                class="w-full h-full object-cover opacity-70 hover:opacity-100 transition-opacity"
              />
            </div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="space-y-6">
          <div>
            <h1 class="text-4xl font-bold text-white mb-2">{{ product.name_en }}</h1>
            <p v-if="product.name_kh" class="text-xl text-grey-400 mb-4">{{ product.name_kh }}</p>
            
            <div class="flex items-center space-x-4 mb-4">
              <v-chip color="primary" variant="flat">
                {{ product.category?.name || 'Uncategorized' }}
              </v-chip>
              <v-chip 
                :color="product.qty > 0 ? 'success' : 'error'" 
                variant="flat"
              >
                {{ product.qty > 0 ? 'In Stock' : 'Out of Stock' }}
              </v-chip>
            </div>

            <div class="flex items-baseline space-x-4 mb-6">
              <span class="text-4xl font-bold text-primary">
                ${{ product.sale_price }}
              </span>
              <span v-if="product.cost_price < product.sale_price" class="text-lg text-grey-400 line-through">
                ${{ product.cost_price }}
              </span>
              <span v-if="product.cost_price < product.sale_price" class="text-success font-medium">
                {{ Math.round(((product.sale_price - product.cost_price) / product.cost_price) * 100) }}% OFF
              </span>
            </div>
          </div>

          <!-- Product Details -->
          <div class="bg-white/5 rounded-xl p-6 backdrop-blur-lg border border-white/10">
            <h3 class="text-lg font-semibold text-white mb-4">Product Details</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-grey-400">SKU:</span>
                <span class="text-white ml-2">{{ product.barcode }}</span>
              </div>
              <div>
                <span class="text-grey-400">Stock:</span>
                <span class="text-white ml-2">{{ product.qty }} units</span>
              </div>
              <div>
                <span class="text-grey-400">Supplier:</span>
                <span class="text-white ml-2">{{ product.supplier?.name || 'N/A' }}</span>
              </div>
              <div>
                <span class="text-grey-400">Status:</span>
                <span class="text-white ml-2 capitalize">{{ product.status }}</span>
              </div>
            </div>
            
            <div v-if="product.description" class="mt-4 pt-4 border-t border-white/10">
              <span class="text-grey-400">Description:</span>
              <p class="text-white mt-2">{{ product.description }}</p>
            </div>
          </div>

          <!-- Quantity Selector & Actions -->
          <div class="space-y-4">
            <div class="flex items-center space-x-4">
              <span class="text-white font-medium">Quantity:</span>
              <div class="flex items-center bg-white/5 rounded-lg border border-white/10">
                <v-btn
                  icon="mdi-minus"
                  variant="text"
                  size="small"
                  color="white"
                  @click="decreaseQuantity"
                  :disabled="quantity <= 1"
                />
                <span class="px-4 py-2 text-white font-medium min-w-[3rem] text-center">
                  {{ quantity }}
                </span>
                <v-btn
                  icon="mdi-plus"
                  variant="text"
                  size="small"
                  color="white"
                  @click="increaseQuantity"
                  :disabled="quantity >= product.qty"
                />
              </div>
              <span class="text-grey-400 text-sm">
                {{ product.qty }} available
              </span>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-4">
              <v-btn
                color="primary"
                size="large"
                variant="flat"
                class="flex-1"
                :disabled="product.qty <= 0"
                @click="addToCart"
                :loading="addingToCart"
              >
                <v-icon start>mdi-cart-plus</v-icon>
                Add to Cart
              </v-btn>
              
              <v-btn
                color="success"
                size="large"
                variant="flat"
                class="flex-1"
                :disabled="product.qty <= 0"
                @click="buyNow"
                :loading="buyingNow"
              >
                <v-icon start>mdi-flash</v-icon>
                Buy Now
              </v-btn>
            </div>

            <v-btn
              variant="outlined"
              color="white"
              size="large"
              class="w-full"
              @click="toggleFavorite"
            >
              <v-icon start>{{ isFavorite ? 'mdi-heart' : 'mdi-heart-outline' }}</v-icon>
              {{ isFavorite ? 'Remove from Favorites' : 'Add to Favorites' }}
            </v-btn>
          </div>

          <!-- Product Features -->
          <div class="bg-white/5 rounded-xl p-6 backdrop-blur-lg border border-white/10">
            <h3 class="text-lg font-semibold text-white mb-4">Why Choose This Product?</h3>
            <div class="space-y-3">
              <div class="flex items-center space-x-3">
                <v-icon color="success" size="20">mdi-check-circle</v-icon>
                <span class="text-white">High Quality Materials</span>
              </div>
              <div class="flex items-center space-x-3">
                <v-icon color="success" size="20">mdi-check-circle</v-icon>
                <span class="text-white">Fast & Secure Delivery</span>
              </div>
              <div class="flex items-center space-x-3">
                <v-icon color="success" size="20">mdi-check-circle</v-icon>
                <span class="text-white">30-Day Return Policy</span>
              </div>
              <div class="flex items-center space-x-3">
                <v-icon color="success" size="20">mdi-check-circle</v-icon>
                <span class="text-white">24/7 Customer Support</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Related Products -->
      <div v-if="relatedProducts.length > 0" class="mt-16">
        <h2 class="text-2xl font-bold text-white mb-8">Related Products</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="relatedProduct in relatedProducts"
            :key="relatedProduct.id"
            class="bg-white/5 rounded-xl overflow-hidden backdrop-blur-lg border border-white/10 hover:border-primary/50 transition-all duration-300 hover:transform hover:scale-105 cursor-pointer"
            @click="$inertia.visit(`/product/${relatedProduct.id}`)"
          >
            <div class="aspect-square overflow-hidden">
              <img
                :src="relatedProduct.image_url"
                :alt="relatedProduct.name_en"
                class="w-full h-full object-cover"
              />
            </div>
            <div class="p-4">
              <h3 class="font-semibold text-white mb-2 truncate">{{ relatedProduct.name_en }}</h3>
              <div class="flex items-center justify-between">
                <span class="text-primary font-bold">${{ relatedProduct.sale_price }}</span>
                <v-chip size="small" color="primary" variant="flat">
                  {{ relatedProduct.category?.name }}
                </v-chip>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Snackbar -->
    <v-snackbar
      v-model="showSnackbar"
      :color="snackbarColor"
      timeout="3000"
      location="top right"
    >
      {{ snackbarMessage }}
      <template #actions>
        <v-btn icon="mdi-close" variant="text" @click="showSnackbar = false" />
      </template>
    </v-snackbar>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useProductStore } from '@/Stores/productStore.js';

const props = defineProps({
  product: Object,
  relatedProducts: Array,
  canLogin: Boolean,
  canRegister: Boolean
});

const productStore = useProductStore();

// Reactive state
const quantity = ref(1);
const addingToCart = ref(false);
const buyingNow = ref(false);
const showSnackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

// Computed properties
const cartCount = computed(() => productStore.cartItemCount);
const favoriteCount = computed(() => productStore.favoriteCount);
const isFavorite = computed(() => 
  productStore.favorites.some(item => item.id === props.product.id)
);

// Methods
const increaseQuantity = () => {
  if (quantity.value < props.product.qty) {
    quantity.value++;
  }
};

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
};

const addToCart = async () => {
  addingToCart.value = true;
  
  try {
    for (let i = 0; i < quantity.value; i++) {
      productStore.addToCart(props.product);
    }
    
    showNotification('Product added to cart successfully!', 'success');
  } catch (error) {
    showNotification('Failed to add product to cart', 'error');
  } finally {
    addingToCart.value = false;
  }
};

const buyNow = async () => {
  buyingNow.value = true;
  
  try {
    // Add to cart first
    for (let i = 0; i < quantity.value; i++) {
      productStore.addToCart(props.product);
    }
    
    // Redirect to checkout
    window.location.href = '/checkout';
  } catch (error) {
    showNotification('Failed to proceed to checkout', 'error');
    buyingNow.value = false;
  }
};

const toggleFavorite = () => {
  if (isFavorite.value) {
    productStore.removeFromFavorites(props.product.id);
    showNotification('Removed from favorites', 'info');
  } else {
    productStore.addToFavorites(props.product);
    showNotification('Added to favorites', 'success');
  }
};

const showNotification = (message, color = 'success') => {
  snackbarMessage.value = message;
  snackbarColor.value = color;
  showSnackbar.value = true;
};

const handleImageError = (event) => {
  console.log('Image failed to load:', event.target.src);
  event.target.src = '/images/no-image.svg';
  // If svg also fails, use a data URL placeholder
  event.target.onerror = function() {
    this.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="400"%3E%3Crect fill="%23ddd" width="400" height="400"/%3E%3Ctext fill="%23999" x="50%25" y="50%25" text-anchor="middle" dy=".3em" font-family="sans-serif" font-size="18"%3ENo Image%3C/text%3E%3C/svg%3E';
  };
};
</script>

<style scoped>
.min-h-screen {
  min-height: 100vh;
}

.aspect-square {
  aspect-ratio: 1 / 1;
}

.backdrop-blur-lg {
  backdrop-filter: blur(16px);
}

.line-through {
  text-decoration: line-through;
}
</style>