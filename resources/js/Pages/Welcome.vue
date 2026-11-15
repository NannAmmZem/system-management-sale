<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { useProductStore } from '@/Stores/productStore';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const store = useProductStore();
const page = usePage();

// Check if user is authenticated
const isAuthenticated = computed(() => page.props.auth.user !== null);

// Login prompt dialog
const showLoginDialog = ref(false);

const props = defineProps({
    products: Array,
    categories: Array,
    stats: Object,
    search: String, // Search query from URL
});

// Local reactive state for products (will be updated in real-time)
const localProducts = ref(props.products || []);
const localCategories = ref(props.categories || []);
const localStats = ref(props.stats || {});

// Loading state
const isUpdating = ref(false);

// Polling interval
let pollingInterval = null;

// Fetch latest products
const fetchLatestProducts = async () => {
    try {
        isUpdating.value = true;
        const response = await axios.get('/api/products');
        if (response.data.success) {
            const previousCount = localProducts.value.length;
            
            localProducts.value = response.data.products;
            localCategories.value = response.data.categories;
            localStats.value = response.data.stats;
            
            // Show notification if new products were added
            const currentCount = localProducts.value.length;
            
            if (currentCount > previousCount) {
                const newProductsCount = currentCount - previousCount;
                showNotification(`🎉 ${newProductsCount} new product${newProductsCount > 1 ? 's' : ''} added!`, 'success');
            }
        }
    } catch (error) {
        console.error('Failed to fetch latest products:', error);
    } finally {
        isUpdating.value = false;
    }
};

// Start polling when component mounts
onMounted(() => {
    // Poll every 10 seconds for new products
    pollingInterval = setInterval(fetchLatestProducts, 10000);
    
    // If there's a search query from URL, apply it
    if (props.search) {
        searchQuery.value = props.search;
        // Scroll to menu section
        setTimeout(() => {
            scrollToMenu();
        }, 500);
    }
});

// Stop polling when component unmounts
onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

// Search and filter state
const searchQuery = ref(props.search || '');
const selectedCategory = ref('');
const priceRange = ref([0, 1000]);
const sortBy = ref('name');

// Make favorites reactive with computed
const favoritesList = computed(() => store.favorites);

// Track which product was just clicked for instant feedback
const justClicked = ref(null);

// Filtered and sorted products
const filteredProducts = computed(() => {
    let filtered = localProducts.value || [];
    
    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(product => 
            product.name_en?.toLowerCase().includes(query) ||
            product.name_kh?.toLowerCase().includes(query) ||
            product.description?.toLowerCase().includes(query) ||
            product.category?.name?.toLowerCase().includes(query)
        );
    }
    
    // Category filter
    if (selectedCategory.value) {
        filtered = filtered.filter(product => 
            product.category_id === selectedCategory.value
        );
    }
    
    // Price range filter
    filtered = filtered.filter(product => 
        product.sale_price >= priceRange.value[0] && 
        product.sale_price <= priceRange.value[1]
    );
    
    // Sort
    filtered.sort((a, b) => {
        switch (sortBy.value) {
            case 'price_low':
                return a.sale_price - b.sale_price;
            case 'price_high':
                return b.sale_price - a.sale_price;
            case 'newest':
                return new Date(b.created_at) - new Date(a.created_at);
            case 'popular':
                return (b.sales_count || 0) - (a.sales_count || 0);
            default:
                return a.name_en?.localeCompare(b.name_en || '');
        }
    });
    
    return filtered;
});

// Check if item is favorited - now reactive
const isItemFavorited = (itemId) => {
    return favoritesList.value.some(item => item.id === itemId);
};

// Toggle favorite with authentication check
const toggleFavorite = (product) => {
    // Check if user is logged in
    if (!isAuthenticated.value) {
        showLoginDialog.value = true;
        return;
    }

    // Set clicked state for instant visual feedback
    justClicked.value = product.id;
    setTimeout(() => {
        justClicked.value = null;
    }, 600); // Match animation duration

    if (store.isInFavorites(product.id)) {
        store.removeFromFavorites(product.id);
        showNotification(`💔 ${product.name_en} removed from wishlist!`, 'warning');
    } else {
        store.addToFavorites(product);
        showNotification(`❤️ ${product.name_en} added to wishlist!`, 'success');
    }
};

// Show temporary notification
const showNotification = (message, type = 'success') => {
    const colors = {
        success: '#00704A',
        warning: '#D4A574',
        error: '#D62B1F'
    };
    
    // Create notification element
    const notification = document.createElement('div');
    notification.innerHTML = `
        <div style="
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: ${colors[type] || colors.success};
            color: white;
            padding: 16px 24px;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            z-index: 9999;
            font-weight: 600;
            min-width: 250px;
            text-align: center;
            font-family: 'SoDo Sans', Helvetica, Arial, sans-serif;
        ">
            ${message}
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
};

const orderItem = (product) => {
    // Check if user is logged in
    if (!isAuthenticated.value) {
        showLoginDialog.value = true;
        return;
    }
    
    // Add to cart using Pinia store
    store.addToCart(product);
    
    // Show success notification
    showNotification(`☕ ${product.name_en} added to cart!`, 'success');
};

const viewProductDetails = (productId) => {
    window.location.href = `/product/${productId}`;
};

// Clear filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    priceRange.value = [0, 1000];
    sortBy.value = 'name';
};

// Scroll to menu section
const scrollToMenu = () => {
    const menuSection = document.getElementById('menu');
    if (menuSection) {
        menuSection.scrollIntoView({ behavior: 'smooth' });
    }
};

// Featured promotions (Starbucks-style)
const promotions = [
    {
        id: 1,
        title: "For the love of Coffee",
        subtitle: "New seasonal favorites are here",
        description: "Discover our handcrafted beverages made with premium ingredients and love.",
        buttonText: "Order now",
        action: () => scrollToMenu(),
        bgColor: "bg-green-700",
        textColor: "text-white"
    },
    {
        id: 2,
        title: "Rewards Program",
        subtitle: "Free drink? It's yours.",
        description: "Join our rewards program and get a free handcrafted drink with your first purchase.",
        buttonText: "Join now",
        action: () => {
            if (!isAuthenticated.value) {
                window.location.href = route('register');
            } else {
                showNotification('You are already a member!', 'success');
            }
        },
        bgColor: "bg-amber-50",
        textColor: "text-green-800"
    },
    {
        id: 3,
        title: "Fresh Pastries",
        subtitle: "Snack smart with fresh baked goods",
        description: "Fuel your day with our selection of fresh pastries, sandwiches, and healthy snacks.",
        buttonText: "Explore menu",
        action: () => scrollToMenu(),
        bgColor: "bg-rose-50",
        textColor: "text-green-800"
    }
];
</script>

<template>
    <Head title="Nann's Coffee - Premium Coffee Experience" />

    <ShopLayout>
        <div class="min-h-screen bg-white starbucks-page">

        <!-- Hero Section - Starbucks Style -->
        <section class="bg-gradient-to-br from-green-700 via-green-600 to-green-800 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <div class="text-center lg:text-left">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                            It's a great day for
                            <span class="block text-amber-300">coffee</span>
                        </h1>
                        <p class="text-lg sm:text-xl text-green-100 mb-8 max-w-lg mx-auto lg:mx-0">
                            Start your day with our handcrafted beverages made with the finest ingredients and served with love.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <v-btn 
                                color="white" 
                                variant="flat" 
                                size="large"
                                class="text-green-700 font-bold rounded-full px-8 py-3"
                                @click="scrollToMenu"
                            >
                                Start an order
                            </v-btn>
                            <Link :href="route('story.index')">
                                <v-btn 
                                    variant="outlined" 
                                    color="white"
                                    size="large"
                                    class="font-bold rounded-full px-8 py-3 w-full"
                                >
                                    Our story
                                </v-btn>
                            </Link>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="bg-white/10 backdrop-blur-sm rounded-3xl p-6 sm:p-8">
                            <div class="grid grid-cols-2 gap-4 sm:gap-6">
                                <div class="text-center">
                                    <div class="text-3xl sm:text-4xl font-bold mb-2">{{ (localProducts || []).length }}+</div>
                                    <div class="text-green-100 text-sm sm:text-base">Premium Products</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl sm:text-4xl font-bold mb-2">{{ localStats?.customers || '1000' }}+</div>
                                    <div class="text-green-100 text-sm sm:text-base">Happy Customers</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl sm:text-4xl font-bold mb-2">4.9</div>
                                    <div class="text-green-100 text-sm sm:text-base">Customer Rating</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-3xl sm:text-4xl font-bold mb-2">24/7</div>
                                    <div class="text-green-100 text-sm sm:text-base">Service</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Promotional Cards - Starbucks Style -->
        <section class="py-12 sm:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    <div 
                        v-for="promo in promotions" 
                        :key="promo.id"
                        :class="[promo.bgColor, promo.textColor]"
                        class="rounded-2xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300"
                    >
                        <div class="p-6 sm:p-8">
                            <h3 class="text-xl sm:text-2xl font-bold mb-2">{{ promo.title }}</h3>
                            <h4 class="text-lg sm:text-xl font-semibold mb-4 opacity-90">{{ promo.subtitle }}</h4>
                            <p class="text-sm sm:text-base mb-6 opacity-80 leading-relaxed">{{ promo.description }}</p>
                            <v-btn 
                                :color="promo.textColor.includes('white') ? 'white' : 'green-darken-3'"
                                :variant="promo.textColor.includes('white') ? 'outlined' : 'flat'"
                                class="font-semibold rounded-full px-6"
                                size="default"
                                @click="promo.action"
                            >
                                {{ promo.buttonText }}
                            </v-btn>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Menu Section -->
        <section ref="menuSection" id="menu" class="py-12 sm:py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-8 sm:mb-12">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                        Explore our menu
                    </h2>
                    <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto">
                        Handcrafted beverages and fresh food made with premium ingredients
                    </p>
                </div>

                <!-- Search and Filters - Starbucks Style -->
                <div class="bg-white rounded-2xl shadow-sm p-4 sm:p-6 mb-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <!-- Search -->
                        <v-text-field
                            v-model="searchQuery"
                            label="Search menu..."
                            prepend-inner-icon="mdi-magnify"
                            variant="outlined"
                            density="compact"
                            color="green-darken-3"
                            class="starbucks-input"
                            clearable
                            hide-details
                        />
                        
                        <!-- Category Filter -->
                        <v-select
                            v-model="selectedCategory"
                            :items="localCategories || []"
                            item-title="name"
                            item-value="id"
                            label="Category"
                            prepend-inner-icon="mdi-tag"
                            variant="outlined"
                            density="compact"
                            color="green-darken-3"
                            class="starbucks-input"
                            clearable
                            hide-details
                        />
                        
                        <!-- Sort -->
                        <v-select
                            v-model="sortBy"
                            :items="[
                                { title: 'Name A-Z', value: 'name' },
                                { title: 'Price: Low to High', value: 'price_low' },
                                { title: 'Price: High to Low', value: 'price_high' },
                                { title: 'Newest First', value: 'newest' },
                                { title: 'Most Popular', value: 'popular' }
                            ]"
                            label="Sort by"
                            prepend-inner-icon="mdi-sort"
                            variant="outlined"
                            density="compact"
                            color="green-darken-3"
                            class="starbucks-input"
                            hide-details
                        />
                        
                        <!-- Clear Filters -->
                        <v-btn
                            color="green-darken-3"
                            variant="outlined"
                            prepend-icon="mdi-filter-remove"
                            @click="clearFilters"
                            class="h-10 font-semibold rounded-full"
                        >
                            <span class="hidden sm:inline">Clear Filters</span>
                            <span class="sm:hidden">Clear</span>
                        </v-btn>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="mt-4">
                        <label class="text-gray-700 text-sm font-semibold mb-2 block">
                            Price Range: ${{ priceRange[0] }} - ${{ priceRange[1] }}
                        </label>
                        <v-range-slider
                            v-model="priceRange"
                            :min="0"
                            :max="1000"
                            :step="10"
                            color="green-darken-3"
                            track-color="gray-300"
                            thumb-color="green-darken-3"
                            class="mt-2"
                        />
                    </div>
                </div>

                <!-- Results Info -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-2">
                    <div class="flex items-center gap-3">
                        <p class="text-gray-600 text-sm sm:text-base">
                            Showing {{ filteredProducts.length }} of {{ (localProducts || []).length }} items
                            <span v-if="searchQuery" class="text-green-700 font-semibold">
                                for "{{ searchQuery }}"
                            </span>
                        </p>
                        <v-progress-circular
                            v-if="isUpdating"
                            indeterminate
                            color="green-darken-3"
                            size="20"
                            width="2"
                        ></v-progress-circular>
                        <v-chip
                            v-else
                            color="green"
                            size="x-small"
                            variant="flat"
                            class="text-white"
                        >
                            <v-icon start size="12">mdi-check-circle</v-icon>
                            Live
                        </v-chip>
                    </div>
                    <div class="flex gap-2">
                        <v-chip 
                            v-if="searchQuery || selectedCategory || priceRange[0] > 0 || priceRange[1] < 1000"
                            color="green-darken-3" 
                            variant="flat"
                            closable
                            size="small"
                            @click:close="clearFilters"
                            class="text-white"
                        >
                            Filters Active
                        </v-chip>
                    </div>
                </div>

                <!-- Products Grid - Starbucks Style -->
                <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                    <div
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="product-card bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 hover:-translate-y-1 cursor-pointer group flex flex-col"
                        @click="viewProductDetails(product.id)"
                    >
                        <!-- Product Image -->
                        <div class="relative h-48 sm:h-52 overflow-hidden flex-shrink-0">
                            <img
                                :src="product.image_url || '/images/no-image.svg'"
                                :alt="product.name_en"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                @error="$event.target.src = '/images/no-image.svg'"
                            />
                            <div class="absolute top-3 right-3">
                                <v-btn
                                    :icon="isItemFavorited(product.id) ? 'mdi-heart' : 'mdi-heart-outline'"
                                    size="small"
                                    :color="isItemFavorited(product.id) ? 'red' : 'white'"
                                    variant="flat"
                                    class="shadow-md"
                                    @click.stop="toggleFavorite(product)"
                                />
                            </div>
                            <div class="absolute bottom-3 left-3">
                                <v-chip
                                    color="green-darken-3"
                                    size="small"
                                    variant="flat"
                                    class="text-white text-xs font-semibold"
                                >
                                    {{ product.category?.name || 'Uncategorized' }}
                                </v-chip>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-4 flex flex-col flex-grow">
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 mb-2 line-clamp-2 min-h-[3.5rem]">
                                {{ product.name_en }}
                            </h3>
                            <p v-if="product.description" class="text-gray-600 text-sm mb-3 line-clamp-2 min-h-[2.5rem]">
                                {{ product.description }}
                            </p>
                            <p v-else class="text-gray-400 text-sm mb-3 italic min-h-[2.5rem]">
                                No description available
                            </p>
                            
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span class="text-lg sm:text-xl font-bold text-green-700">
                                        ${{ product.sale_price }}
                                    </span>
                                    <span v-if="product.cost_price < product.sale_price" class="text-sm text-gray-400 line-through">
                                        ${{ product.cost_price }}
                                    </span>
                                </div>
                                <v-chip
                                    :color="product.qty > 0 ? 'green' : 'red'"
                                    size="x-small"
                                    variant="flat"
                                    class="text-white text-xs font-semibold"
                                >
                                    {{ product.qty > 0 ? 'Available' : 'Out of Stock' }}
                                </v-chip>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2 mt-auto">
                                <v-btn
                                    color="green-darken-3"
                                    variant="flat"
                                    size="small"
                                    class="flex-1 text-white font-semibold rounded-full"
                                    :disabled="product.qty <= 0"
                                    @click.stop="orderItem(product)"
                                >
                                    <v-icon start size="16">mdi-plus</v-icon>
                                    Add to cart
                                </v-btn>
                                <v-btn
                                    color="green-darken-3"
                                    variant="outlined"
                                    size="small"
                                    icon="mdi-eye"
                                    class="rounded-full"
                                    @click.stop="viewProductDetails(product.id)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Results -->
                <div v-else class="text-center py-12 sm:py-16">
                    <v-icon size="80" color="gray-400" class="mb-4">mdi-coffee-off-outline</v-icon>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">No items found</h3>
                    <p class="text-gray-600 mb-6 text-sm sm:text-base">Try adjusting your search or filters</p>
                    <v-btn color="green-darken-3" @click="clearFilters" class="font-semibold rounded-full px-6">
                        Clear All Filters
                    </v-btn>
                </div>
            </div>
        </section>
        </div>
    </ShopLayout>

    <!-- Login Required Dialog -->
    <v-dialog v-model="showLoginDialog" max-width="500px">
        <v-card class="rounded-2xl">
            <v-card-title class="bg-green-700 text-white pa-6 text-center">
                <div class="text-xl font-bold">
                    <v-icon icon="mdi-account-lock" class="mr-2"></v-icon>
                    Sign in required
                </div>
            </v-card-title>
            
            <v-card-text class="pa-6 text-center">
                <v-icon icon="mdi-coffee" size="60" color="green-darken-3" class="mb-4"></v-icon>
                <h3 class="text-xl font-bold mb-3 text-gray-900">Join the experience</h3>
                <p class="text-gray-600 mb-4">
                    Sign in to add items to your cart and enjoy personalized recommendations.
                </p>
            </v-card-text>
            
            <v-card-actions class="pa-6 pt-0 justify-center flex-column gap-3">
                <Link :href="route('login')" class="w-full">
                    <v-btn 
                        color="green-darken-3" 
                        variant="flat"
                        prepend-icon="mdi-login"
                        class="font-bold rounded-full w-full text-white"
                        size="large"
                    >
                        Sign in
                    </v-btn>
                </Link>
                <Link :href="route('register')" class="w-full">
                    <v-btn 
                        color="green-darken-3" 
                        variant="outlined"
                        prepend-icon="mdi-account-plus"
                        class="font-bold rounded-full w-full"
                        size="large"
                    >
                        Join now
                    </v-btn>
                </Link>
                <v-btn 
                    variant="text" 
                    color="gray" 
                    @click="showLoginDialog = false"
                    class="mt-2"
                >
                    Maybe later
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>
/* Hide ShopLayout navigation for Starbucks-style page */
.starbucks-page :deep(.v-app-bar) {
  display: none !important;
}

/* Product Card Equal Heights */
.product-card {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.product-card .p-4 {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

/* Ensure consistent title height */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Minimum heights for consistent card sizing */
.min-h-\[3\.5rem\] {
  min-height: 3.5rem;
}

.min-h-\[2\.5rem\] {
  min-height: 2.5rem;
}

/* Starbucks-inspired styling */
.starbucks-input :deep(.v-field) {
  border-radius: 50px !important;
  border: 2px solid #e5e7eb !important;
}

.starbucks-input :deep(.v-field--focused) {
  border-color: #00704A !important;
  box-shadow: 0 0 0 3px rgba(0, 112, 74, 0.1) !important;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Starbucks green theme */
:root {
  --starbucks-green: #00704A;
  --starbucks-light-green: #1e3932;
  --starbucks-gold: #D4A574;
}

/* Responsive Design */
@media (max-width: 640px) {
  .hero-title {
    font-size: 2.5rem !important;
  }
  
  .promo-card {
    padding: 1.5rem !important;
  }
  
  .product-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }
}

@media (min-width: 641px) and (max-width: 768px) {
  .product-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .product-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
  }
}

/* Hover effects */
.hover-lift:hover {
  transform: translateY(-4px);
  transition: transform 0.3s ease;
}

/* Custom animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in-up {
  animation: fadeInUp 0.6s ease-out;
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
  .hover\:shadow-lg:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
  }
  
  .hover\:-translate-y-1:hover {
    transform: none !important;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .bg-green-700 {
    background-color: #065f46 !important;
  }
  
  .text-gray-600 {
    color: #374151 !important;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .transition-all,
  .transition-colors,
  .transition-transform,
  .transition-shadow {
    transition: none !important;
  }
  
  .hover\:scale-105:hover,
  .group-hover\:scale-105 {
    transform: none !important;
  }
}
</style>