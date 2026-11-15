<script setup>
import { ref, computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { useDisplay } from 'vuetify';

const { mobile } = useDisplay();
const drawer = ref(!mobile.value); // Start closed on mobile
const rail = ref(false);  
const page = usePage();

// --- SAFE USER DATA ---
const user = computed(() => {
    return page.props.auth.user || { 
        name: 'Admin User', 
        role: 'Staff', 
        avatar: 'https://randomuser.me/api/portraits/men/1.jpg' 
    };
});

// Get user permissions
const userPermissions = computed(() => {
    return page.props.auth?.user?.permissions || [];
});

// Check if user has permission
const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

// Menu Items with permission requirements
const allMenuItems = [
  { title: 'Dashboard', icon: 'mdi-view-dashboard', route: 'dashboard', permission: 'view dashboard' },
  { title: 'POS System', icon: 'mdi-monitor-dashboard', route: 'pos', permission: 'access pos' },
  { title: 'Products', icon: 'mdi-package-variant', route: 'products.index', permission: 'view products' },
  { title: 'Categories', icon: 'mdi-shape', route: 'categories.index', permission: 'view categories' },
  { title: 'Employees', icon: 'mdi-account-group', route: 'employees.index', permission: 'view employees' },
  { title: 'Members', icon: 'mdi-account-star', route: 'customers.index', permission: 'view customers' },
  { title: 'Suppliers', icon: 'mdi-truck', route: 'suppliers.index', permission: 'view suppliers' },
  { title: 'Promotions', icon: 'mdi-ticket-percent', route: 'promotions.index', permission: 'view promotions' },
  { title: 'Sales Report', icon: 'mdi-chart-bar', route: 'reports.index', permission: 'view reports' },
  { title: 'Attendance', icon: 'mdi-clock-check', route: 'attendance.index', permission: 'view attendance' },
  { title: 'Positions', icon: 'mdi-shield-account', route: 'roles.index', permission: 'view roles' },
  { title: 'Settings', icon: 'mdi-cog', route: 'settings.index', permission: 'view settings' },
];

// Filter menu items based on user permissions
const items = computed(() => {
    return allMenuItems.filter(item => hasPermission(item.permission));
});

const navigateTo = (routeName) => {
    // Close drawer on mobile after navigation
    if (mobile.value) {
        drawer.value = false;
    }
    
    if (routeName && route().has(routeName)) {
        router.get(route(routeName));
    } else {
        console.warn(`Route ${routeName} not found.`);
    }
};

const logout = () => {
    router.post(route('logout'));
};

const toggleDrawer = () => {
    drawer.value = !drawer.value;
};
</script>

<template>
  <v-app theme="dark"> 
    
    <v-navigation-drawer
      v-model="drawer"
      :rail="rail && !mobile"
      :permanent="!mobile"
      :temporary="mobile"
      @click="rail = false"
      elevation="5"
      color="grey-darken-4"
      :width="mobile ? 280 : 256"
    >
      <v-list-item
        :class="mobile ? 'py-3' : 'py-4'"
        :prepend-avatar="'https://ui-avatars.com/api/?name=' + (user?.name || 'Admin') + '&background=random'"
        :title="user?.name || 'User'"
        :subtitle="user?.roles && user.roles.length > 0 ? user.roles[0].toUpperCase() : 'STAFF'" 
        nav
      >
        <template v-slot:append>
          <v-btn 
            v-if="!mobile"
            variant="text" 
            icon="mdi-chevron-left" 
            @click.stop="rail = !rail"
          ></v-btn>
          <v-btn 
            v-else
            variant="text" 
            icon="mdi-close" 
            @click.stop="drawer = false"
          ></v-btn>
        </template>
      </v-list-item>

      <v-divider></v-divider>

      <v-list density="compact" nav>
        <v-list-item 
          v-for="(item, i) in items" 
          :key="i"
          :value="item"
          color="amber-accent-4"
          @click.prevent="navigateTo(item.route)"
          class="mb-1"
        >
          <template v-slot:prepend>
            <v-icon :icon="item.icon"></v-icon>
          </template>
          <v-list-item-title v-text="item.title"></v-list-item-title>
        </v-list-item>
      </v-list>
      
      <template v-slot:append>
        <div class="pa-2">
            <v-btn 
                block 
                color="red-darken-3" 
                prepend-icon="mdi-logout" 
                v-if="!rail || mobile"
                @click="logout"
            >
                Logout
            </v-btn>
            <v-btn 
                icon="mdi-logout" 
                color="red-darken-3" 
                variant="text" 
                v-else
                @click="logout"
            ></v-btn>
        </div>
      </template>
    </v-navigation-drawer>

    <v-app-bar 
      elevation="1" 
      color="grey-darken-4"
      :height="mobile ? 56 : 64"
    >
      <!-- Mobile Menu Button -->
      <v-btn 
        v-if="mobile"
        icon="mdi-menu"
        @click="toggleDrawer"
        class="mr-2"
      ></v-btn>

      <v-app-bar-title class="font-weight-bold d-flex align-center">
         <v-icon icon="mdi-coffee" color="amber" class="mr-2"></v-icon>
         <span :class="mobile ? 'text-body-1' : 'text-h6'">
           {{ mobile ? 'SMS' : 'Sale Management System' }}
         </span>
      </v-app-bar-title>
      
      <v-spacer></v-spacer>
      
      <Link :href="route('home')">
          <v-btn 
            variant="outlined" 
            color="amber" 
            :size="mobile ? 'small' : 'default'"
            :class="mobile ? 'mr-2' : 'mr-4'"
          >
            <v-icon v-if="mobile" icon="mdi-web"></v-icon>
            <span v-else>View Website</span>
          </v-btn>
      </Link>

      <v-btn 
        icon="mdi-bell-outline"
        :size="mobile ? 'small' : 'default'"
      ></v-btn>
    </v-app-bar>

    <v-main class="bg-grey-darken-3"> 
      <v-container 
        fluid 
        :class="mobile ? 'pa-4' : 'pa-6'"
      >
         <slot />
      </v-container>
    </v-main>
  </v-app>
</template>