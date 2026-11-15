<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useProductStore } from '@/Stores/productStore';

const store = useProductStore();
const page = usePage();

// --- State Management ---
const isScrolled = ref(false);
const searchQuery = ref('');
const mobileDrawer = ref(false);
const cartCount = computed(() => store.cartItemCount);
const favCount = computed(() => store.favoriteCount);

// --- Flash Message Snackbar ---
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        snackbarMessage.value = flash.success;
        snackbarColor.value = 'success';
        snackbar.value = true;
    } else if (flash?.error) {
        snackbarMessage.value = flash.error;
        snackbarColor.value = 'error';
        snackbar.value = true;
    }
}, { deep: true });

// --- Search Functionality ---
const handleSearch = () => {
    if (searchQuery.value.trim()) {
        // Navigate to home page with search query
        router.visit(route('home'), {
            data: { search: searchQuery.value.trim() },
            preserveState: true,
            preserveScroll: false,
        });
    }
};

const handleSearchKeypress = (event) => {
    if (event.key === 'Enter') {
        handleSearch();
    }
};

// --- Scroll Logic for Glass Effect ---
const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));

// --- Navigation Function for Mobile Drawer ---
const navigateTo = (routeName) => {
    mobileDrawer.value = false; // Close drawer
    router.visit(route(routeName));
};

// --- Logout Function ---
const logout = () => {
    mobileDrawer.value = false; // Close drawer
    router.post(route('logout'));
};

// --- Check if user has admin or staff role ---
const hasAdminAccess = computed(() => {
    const user = page.props.auth?.user;
    if (!user || !user.roles) return false;
    return user.roles.includes('admin') || user.roles.includes('staff');
});
</script>

<template>
    <v-app class="bg-black">
        <!-- Mobile Navigation Drawer -->
        <v-navigation-drawer
            v-model="mobileDrawer"
            temporary
            location="left"
            class="mobile-drawer"
            width="280"
        >
            <div class="drawer-header">
                <v-icon icon="mdi-coffee" size="32" color="amber"></v-icon>
                <span class="text-h6 font-weight-bold ml-2">Nann's Coffee</span>
            </div>
            
            <v-divider></v-divider>
            
            <!-- Mobile Search -->
            <div class="pa-4">
                <v-text-field
                    v-model="searchQuery"
                    density="compact"
                    variant="outlined"
                    label="Search products..."
                    prepend-inner-icon="mdi-magnify"
                    hide-details
                    rounded
                    @keypress.enter="handleSearch"
                >
                    <template v-slot:append-inner>
                        <v-btn 
                            icon="mdi-arrow-right" 
                            size="x-small" 
                            variant="text"
                            @click="handleSearch"
                            :disabled="!searchQuery.trim()"
                        ></v-btn>
                    </template>
                </v-text-field>
            </div>
            
            <v-list>
                <v-list-item prepend-icon="mdi-home" @click="navigateTo('home')">
                    <v-list-item-title>Home</v-list-item-title>
                </v-list-item>
                
                <v-list-item prepend-icon="mdi-book-open" @click="navigateTo('story.index')">
                    <v-list-item-title>Our Story</v-list-item-title>
                </v-list-item>
                
                <v-list-item prepend-icon="mdi-heart" @click="navigateTo('wishlist.index')">
                    <v-list-item-title>
                        Wishlist
                        <v-chip v-if="favCount > 0" size="small" color="red" class="ml-2">
                            {{ favCount }}
                        </v-chip>
                    </v-list-item-title>
                </v-list-item>
                
                <v-list-item prepend-icon="mdi-cart" @click="navigateTo('cart.index')">
                    <v-list-item-title>
                        Cart
                        <v-chip v-if="cartCount > 0" size="small" color="amber" class="ml-2">
                            {{ cartCount }}
                        </v-chip>
                    </v-list-item-title>
                </v-list-item>
                
                <v-divider class="my-2"></v-divider>
                
                <div v-if="$page.props.auth.user">
                    <v-list-item 
                        v-if="hasAdminAccess" 
                        prepend-icon="mdi-view-dashboard"
                        @click="navigateTo('dashboard')"
                    >
                        <v-list-item-title>Dashboard</v-list-item-title>
                    </v-list-item>
                    
                    <v-list-item prepend-icon="mdi-account" @click="navigateTo('profile.edit')">
                        <v-list-item-title>{{ $page.props.auth.user.name }}</v-list-item-title>
                    </v-list-item>
                    
                    <v-list-item @click="logout" prepend-icon="mdi-logout">
                        <v-list-item-title>Logout</v-list-item-title>
                    </v-list-item>
                </div>
                
                <div v-else>
                    <v-list-item prepend-icon="mdi-login" @click="navigateTo('login')">
                        <v-list-item-title>Login</v-list-item-title>
                    </v-list-item>
                    
                    <v-list-item prepend-icon="mdi-account-plus" @click="navigateTo('register')">
                        <v-list-item-title>Sign Up</v-list-item-title>
                    </v-list-item>
                </div>
            </v-list>
        </v-navigation-drawer>

        <!-- App Bar -->
        <v-app-bar 
            :color="isScrolled ? 'brown-darken-4' : 'transparent'" 
            :elevation="isScrolled ? 4 : 0"
            class="transition-all"
            :height="$vuetify.display.mobile ? 64 : 80"
            fixed
        >
            <!-- Mobile Menu Button -->
            <template v-slot:prepend>
                <v-btn 
                    v-if="$vuetify.display.mobile"
                    icon="mdi-menu"
                    @click="mobileDrawer = !mobileDrawer"
                    color="white"
                    class="mr-2"
                ></v-btn>
            </template>

            <!-- Logo -->
            <Link :href="route('home')" class="text-decoration-none d-flex align-center">
                <v-icon icon="mdi-coffee" :size="$vuetify.display.mobile ? 24 : 32" color="amber-lighten-4" class="mr-2"></v-icon>
                <div :class="$vuetify.display.mobile ? 'text-body-1' : 'text-h5'" class="font-weight-bold text-amber-lighten-4 brand-font">
                    <span v-if="!$vuetify.display.mobile">Nann's <span class="text-white">Coffee.</span></span>
                    <span v-else>Nann's</span>
                </div>
            </Link>

            <v-spacer></v-spacer>

            <!-- Desktop Search -->
            <div v-if="!$vuetify.display.mobile" class="d-none d-md-flex" style="width: 400px;">
                <v-text-field
                    v-model="searchQuery"
                    density="compact"
                    variant="solo"
                    label="Search coffee, products..."
                    prepend-inner-icon="mdi-magnify"
                    single-line
                    hide-details
                    bg-color="white"
                    rounded="pill"
                    class="mr-4"
                    @keypress.enter="handleSearch"
                >
                    <template v-slot:append-inner>
                        <v-btn 
                            icon="mdi-arrow-right" 
                            size="small" 
                            variant="text"
                            color="green-darken-3"
                            @click="handleSearch"
                            :disabled="!searchQuery.trim()"
                        ></v-btn>
                    </template>
                </v-text-field>
            </div>

            <v-spacer v-if="!$vuetify.display.mobile"></v-spacer>

            <!-- Desktop Navigation -->
            <div v-if="!$vuetify.display.mobile" class="d-none d-md-flex align-center gap-2">
                <Link :href="route('home')" class="nav-link">Menu</Link>
                <Link :href="route('story.index')" class="nav-link">Story</Link>
                
                <v-btn variant="text" color="white" class="text-capitalize" href="#contact-footer">
                    Contact Staff
                </v-btn>

                <Link :href="route('wishlist.index')">
                    <v-btn icon color="white">
                        <v-badge :content="favCount" color="red" floating v-if="favCount > 0">
                            <v-icon icon="mdi-heart-outline"></v-icon>
                        </v-badge>
                        <v-icon v-else icon="mdi-heart-outline"></v-icon>
                    </v-btn>
                </Link>

                <Link :href="route('cart.index')">
                    <v-btn icon color="white" class="mr-4">
                        <v-badge :content="cartCount" color="amber-darken-2" floating v-if="cartCount > 0">
                            <v-icon icon="mdi-cart-outline"></v-icon>
                        </v-badge>
                        <v-icon v-else icon="mdi-cart-outline"></v-icon>
                    </v-btn>
                </Link>

                <div v-if="$page.props.auth.user">
                    <Link 
                        v-if="hasAdminAccess" 
                        :href="route('dashboard')"
                    >
                        <v-btn variant="outlined" color="amber-accent-2" class="font-weight-bold">
                            DASHBOARD
                        </v-btn>
                    </Link>

                    <Link v-else :href="route('profile.edit')">
                        <v-btn variant="outlined" color="white" prepend-icon="mdi-account-circle">
                            {{ $page.props.auth.user.name }}
                        </v-btn>
                    </Link>
                </div>

                <div v-else class="d-flex align-center">
                    <Link :href="route('login')">
                        <v-btn variant="text" color="white">Login</v-btn>
                    </Link>
                    <Link :href="route('register')">
                        <v-btn color="amber-darken-2" variant="flat" class="text-black font-weight-bold" rounded="pill">
                            Sign Up
                        </v-btn>
                    </Link>
                </div>
            </div>

            <!-- Mobile Actions -->
            <div v-if="$vuetify.display.mobile" class="d-flex align-center">
                <Link :href="route('wishlist.index')">
                    <v-btn icon color="white" size="small">
                        <v-badge :content="favCount" color="red" floating v-if="favCount > 0">
                            <v-icon icon="mdi-heart-outline" size="20"></v-icon>
                        </v-badge>
                        <v-icon v-else icon="mdi-heart-outline" size="20"></v-icon>
                    </v-btn>
                </Link>

                <Link :href="route('cart.index')">
                    <v-btn icon color="white" size="small">
                        <v-badge :content="cartCount" color="amber-darken-2" floating v-if="cartCount > 0">
                            <v-icon icon="mdi-cart-outline" size="20"></v-icon>
                        </v-badge>
                        <v-icon v-else icon="mdi-cart-outline" size="20"></v-icon>
                    </v-btn>
                </Link>
            </div>
        </v-app-bar>

        <v-main class="bg-grey-darken-4">
            <slot />
        </v-main>

        <!-- Flash Message Snackbar -->
        <v-snackbar
            v-model="snackbar"
            :color="snackbarColor"
            :timeout="5000"
            location="top"
            rounded="pill"
        >
            <div class="d-flex align-center">
                <v-icon 
                    :icon="snackbarColor === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'" 
                    class="mr-2"
                ></v-icon>
                {{ snackbarMessage }}
            </div>
            <template v-slot:actions>
                <v-btn
                    variant="text"
                    @click="snackbar = false"
                    icon="mdi-close"
                    size="small"
                ></v-btn>
            </template>
        </v-snackbar>

        <!-- Modern Starbucks-Style Footer -->
        <footer id="contact-footer" class="bg-gradient-to-b from-gray-900 to-black text-white">
            <v-container class="py-12">
                <!-- Main Footer Content -->
                <v-row class="mb-8">
                    <!-- Brand Section -->
                    <v-col cols="12" md="4" class="mb-6 mb-md-0">
                        <div class="d-flex align-center mb-4">
                            <v-icon icon="mdi-coffee" size="40" color="amber-lighten-2" class="mr-3"></v-icon>
                            <div>
                                <h2 class="text-h5 font-weight-bold text-amber-lighten-2">Nann's Coffee</h2>
                                <p class="text-caption text-grey-lighten-1">Premium Coffee Experience</p>
                            </div>
                        </div>
                        <p class="text-body-2 text-grey-lighten-1 mb-4">
                            Crafting exceptional coffee experiences since 2026. Every cup tells a story, every sip creates a memory.
                        </p>
                        <!-- Social Media -->
                        <div class="d-flex gap-2">
                            <v-btn icon size="small" variant="outlined" color="amber-lighten-2">
                                <v-icon icon="mdi-facebook"></v-icon>
                            </v-btn>
                            <v-btn icon size="small" variant="outlined" color="amber-lighten-2">
                                <v-icon icon="mdi-instagram"></v-icon>
                            </v-btn>
                            <v-btn icon size="small" variant="outlined" color="amber-lighten-2">
                                <v-icon icon="mdi-twitter"></v-icon>
                            </v-btn>
                            <v-btn icon size="small" variant="outlined" color="amber-lighten-2">
                                <v-icon icon="mdi-youtube"></v-icon>
                            </v-btn>
                        </div>
                    </v-col>

                    <!-- Quick Links -->
                    <v-col cols="6" md="2" class="mb-6 mb-md-0">
                        <h4 class="text-subtitle-1 font-weight-bold text-amber-lighten-2 mb-4">Quick Links</h4>
                        <div class="d-flex flex-column gap-2">
                            <Link :href="route('home')" class="footer-link">
                                <v-icon icon="mdi-chevron-right" size="small" class="mr-1"></v-icon>
                                Menu
                            </Link>
                            <Link :href="route('story.index')" class="footer-link">
                                <v-icon icon="mdi-chevron-right" size="small" class="mr-1"></v-icon>
                                Our Story
                            </Link>
                            <Link :href="route('cart.index')" class="footer-link">
                                <v-icon icon="mdi-chevron-right" size="small" class="mr-1"></v-icon>
                                Cart
                            </Link>
                            <Link :href="route('wishlist.index')" class="footer-link">
                                <v-icon icon="mdi-chevron-right" size="small" class="mr-1"></v-icon>
                                Wishlist
                            </Link>
                        </div>
                    </v-col>

                    <!-- Customer Service -->
                    <v-col cols="6" md="2" class="mb-6 mb-md-0">
                        <h4 class="text-subtitle-1 font-weight-bold text-amber-lighten-2 mb-4">Support</h4>
                        <div class="d-flex flex-column gap-2">
                            <a href="#" class="footer-link">
                                <v-icon icon="mdi-chevron-right" size="small" class="mr-1"></v-icon>
                                Help Center
                            </a>
                            <a href="#" class="footer-link">
                                <v-icon icon="mdi-chevron-right" size="small" class="mr-1"></v-icon>
                                Contact Us
                            </a>
                            <a href="#" class="footer-link">
                                <v-icon icon="mdi-chevron-right" size="small" class="mr-1"></v-icon>
                                FAQs
                            </a>
                            <a href="#" class="footer-link">
                                <v-icon icon="mdi-chevron-right" size="small" class="mr-1"></v-icon>
                                Shipping Info
                            </a>
                        </div>
                    </v-col>

                    <!-- Newsletter -->
                    <v-col cols="12" md="4">
                        <h4 class="text-subtitle-1 font-weight-bold text-amber-lighten-2 mb-4">Stay Connected</h4>
                        <p class="text-body-2 text-grey-lighten-1 mb-4">
                            Subscribe to get special offers, free giveaways, and updates.
                        </p>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <v-text-field
                                placeholder="Enter your email"
                                variant="outlined"
                                density="compact"
                                hide-details
                                bg-color="rgba(255,255,255,0.1)"
                                color="amber-lighten-2"
                                class="newsletter-input flex-grow-1"
                            >
                                <template v-slot:prepend-inner>
                                    <v-icon icon="mdi-email-outline" size="small"></v-icon>
                                </template>
                            </v-text-field>
                            <v-btn 
                                color="amber-lighten-2" 
                                variant="flat"
                                class="text-black font-weight-bold"
                                :block="$vuetify.display.xs"
                            >
                                Subscribe
                            </v-btn>
                        </div>
                    </v-col>
                </v-row>

                <!-- Divider -->
                <v-divider class="border-opacity-25 my-6"></v-divider>

                <!-- Bottom Bar -->
                <v-row align="center">
                    <v-col cols="12" md="6" class="text-center text-md-left">
                        <p class="text-caption text-grey-lighten-1 mb-0">
                            &copy; 2026 Nann's Coffee Company. All rights reserved.
                        </p>
                    </v-col>
                    <v-col cols="12" md="6" class="text-center text-md-right">
                        <div class="d-flex justify-center justify-md-end flex-wrap gap-4">
                            <a href="#" class="text-caption text-grey-lighten-1 text-decoration-none hover-amber">Privacy Policy</a>
                            <a href="#" class="text-caption text-grey-lighten-1 text-decoration-none hover-amber">Terms of Service</a>
                            <a href="#" class="text-caption text-grey-lighten-1 text-decoration-none hover-amber">Cookies</a>
                        </div>
                    </v-col>
                </v-row>
            </v-container>
        </footer>
    </v-app>
</template>

<style scoped>
.transition-all { 
    transition: background-color 0.3s ease-in-out, box-shadow 0.3s ease-in-out; 
}

.nav-link { 
    color: white; 
    text-decoration: none; 
    font-weight: 500; 
    margin: 0 15px;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.8;
    transition: all 0.2s ease;
}

.nav-link:hover { 
    opacity: 1; 
    color: #FFD180; 
}

.brand-font {
    font-family: 'Playfair Display', serif !important;
}

.hover-white:hover {
    color: white !important;
    text-decoration: underline !important;
}

.footer-link {
    color: #e0e0e0;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.footer-link:hover {
    color: #FFD180 !important;
    transform: translateX(4px);
}

.hover-amber:hover {
    color: #FFD180 !important;
}

.newsletter-input :deep(.v-field) {
    border-color: rgba(255, 255, 255, 0.2) !important;
}

.newsletter-input :deep(.v-field--focused) {
    border-color: #FFD180 !important;
}

.newsletter-input :deep(.v-field__input) {
    color: white !important;
}

.newsletter-input :deep(.v-field__input::placeholder) {
    color: rgba(255, 255, 255, 0.5) !important;
}

/* Mobile Drawer Styles */
.mobile-drawer {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
}

.drawer-header {
    padding: 1rem;
    display: flex;
    align-items: center;
    background: rgba(255, 193, 7, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.mobile-drawer :deep(.v-list-item) {
    color: white;
    border-radius: 8px;
    margin: 4px 8px;
}

.mobile-drawer :deep(.v-list-item:hover) {
    background: rgba(255, 193, 7, 0.1);
}

.mobile-drawer :deep(.v-list-item--active) {
    background: rgba(255, 193, 7, 0.2);
    color: #FFC107;
}

.mobile-drawer :deep(.v-list-item__prepend > .v-icon) {
    color: #FFC107;
}

/* Mobile Responsive Adjustments */
@media (max-width: 600px) {
    .v-app-bar {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
    
    .brand-font {
        font-size: 1rem !important;
    }
    
    /* Footer adjustments for mobile */
    .footer-links {
        text-align: center;
    }
    
    .footer-links h4 {
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .social-buttons {
        justify-content: center !important;
        gap: 0.5rem;
    }
    
    .newsletter-section {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .hero-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .stat-item {
        min-width: 120px;
    }
    
    .partner-cards {
        font-size: 0.875rem !important;
    }
}

/* Ensure proper spacing on mobile */
@media (max-width: 768px) {
    .v-container {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }
    
    .v-row {
        margin-left: -8px !important;
        margin-right: -8px !important;
    }
    
    .v-col {
        padding-left: 8px !important;
        padding-right: 8px !important;
    }
}
</style>