<script setup>
import ShopLayout from '@/Layouts/ShopLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const page = usePage();

// Logout function
const logout = () => {
    router.post(route('logout'));
};

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

// Check if user is admin/staff
const isStaff = computed(() => {
    const user = page.props.auth?.user;
    if (!user || !user.roles) return false;
    return user.roles.includes('admin') || user.roles.includes('staff');
});

// Active tab
const activeTab = ref('profile');

// Profile form
const profileForm = useForm({
    name: page.props.auth.user.name,
    email: page.props.auth.user.email,
});

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Delete account form
const deleteForm = useForm({
    password: '',
});

// Snackbar
const snackbar = ref(false);
const snackbarMessage = ref('');
const snackbarColor = ref('success');

// Show/hide password
const showCurrentPassword = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const showDeletePassword = ref(false);

// Delete dialog
const deleteDialog = ref(false);

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

// Submit profile
const submitProfile = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            snackbarMessage.value = 'Profile updated successfully!';
            snackbarColor.value = 'success';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to update profile';
            snackbarColor.value = 'error';
            snackbar.value = true;
        }
    });
};

// Submit password
const submitPassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            snackbarMessage.value = 'Password updated successfully!';
            snackbarColor.value = 'success';
            snackbar.value = true;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to update password';
            snackbarColor.value = 'error';
            snackbar.value = true;
        }
    });
};

// Delete account
const confirmDelete = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            deleteDialog.value = false;
        },
        onError: () => {
            snackbarMessage.value = 'Failed to delete account';
            snackbarColor.value = 'error';
            snackbar.value = true;
        }
    });
};

// Get user initials
const userInitials = computed(() => {
    const name = page.props.auth.user.name;
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
});
</script>

<template>
    <Head title="My Profile" />

    <ShopLayout>
        <!-- Animated Background -->
        <div class="profile-background">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
        </div>

        <v-container class="py-8 py-md-12 position-relative">
            <v-row justify="center">
                <!-- Profile Header Card -->
                <v-col cols="12" md="10" lg="8">
                    <v-card class="profile-header-card glass-card mb-6" elevation="10">
                        <v-card-text class="pa-6 pa-md-8">
                            <div class="d-flex flex-column flex-md-row align-center text-center text-md-left">
                                <!-- Avatar -->
                                <v-avatar 
                                    size="120" 
                                    class="profile-avatar mb-4 mb-md-0 mr-md-6"
                                    color="amber-darken-2"
                                >
                                    <span class="text-h3 font-weight-bold text-white">{{ userInitials }}</span>
                                </v-avatar>

                                <!-- User Info -->
                                <div class="flex-grow-1">
                                    <h1 class="text-h4 text-md-h3 font-weight-bold text-white mb-2">
                                        {{ $page.props.auth.user.name }}
                                    </h1>
                                    <p class="text-h6 text-amber-lighten-2 mb-3">
                                        <v-icon icon="mdi-email" size="small" class="mr-2"></v-icon>
                                        {{ $page.props.auth.user.email }}
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 align-center">
                                        <v-chip 
                                            v-if="isStaff" 
                                            color="amber" 
                                            variant="flat" 
                                            prepend-icon="mdi-shield-star"
                                            class="font-weight-bold"
                                        >
                                            {{ $page.props.auth.user.roles[0].toUpperCase() }}
                                        </v-chip>
                                        <v-chip 
                                            v-else
                                            color="green-darken-2" 
                                            variant="flat" 
                                            prepend-icon="mdi-coffee"
                                            class="font-weight-bold"
                                        >
                                            COFFEE LOVER
                                        </v-chip>
                                        
                                        <!-- Logout Button -->
                                        <v-btn
                                            color="red-darken-2"
                                            variant="outlined"
                                            prepend-icon="mdi-logout"
                                            @click="logout"
                                            class="font-weight-bold"
                                            size="small"
                                        >
                                            Logout
                                        </v-btn>
                                    </div>
                                </div>

                                <!-- Quick Stats -->
                                <div class="d-none d-lg-flex flex-column gap-3 ml-6">
                                    <v-card class="stat-card" color="rgba(255, 193, 7, 0.1)">
                                        <v-card-text class="pa-3 text-center">
                                            <div class="text-h5 font-weight-bold text-amber">0</div>
                                            <div class="text-caption text-grey-lighten-1">Orders</div>
                                        </v-card-text>
                                    </v-card>
                                    <v-card class="stat-card" color="rgba(76, 175, 80, 0.1)">
                                        <v-card-text class="pa-3 text-center">
                                            <div class="text-h5 font-weight-bold text-green">0</div>
                                            <div class="text-caption text-grey-lighten-1">Favorites</div>
                                        </v-card-text>
                                    </v-card>
                                </div>
                            </div>
                        </v-card-text>
                    </v-card>

                    <!-- Tabs -->
                    <v-tabs
                        v-model="activeTab"
                        bg-color="transparent"
                        color="amber"
                        class="mb-6"
                        grow
                    >
                        <v-tab value="profile" class="text-white font-weight-bold">
                            <v-icon icon="mdi-account-circle" class="mr-2"></v-icon>
                            Profile Info
                        </v-tab>
                        <v-tab value="password" class="text-white font-weight-bold">
                            <v-icon icon="mdi-lock-reset" class="mr-2"></v-icon>
                            Password
                        </v-tab>
                        <v-tab value="security" class="text-white font-weight-bold">
                            <v-icon icon="mdi-shield-lock" class="mr-2"></v-icon>
                            Security
                        </v-tab>
                    </v-tabs>

                    <!-- Tab Content -->
                    <v-window v-model="activeTab">
                        <!-- Profile Information Tab -->
                        <v-window-item value="profile">
                            <v-card class="glass-card" elevation="10">
                                <v-card-title class="pa-6 bg-gradient-amber">
                                    <v-icon icon="mdi-account-edit" class="mr-2"></v-icon>
                                    Update Profile Information
                                </v-card-title>
                                <v-card-text class="pa-6 pa-md-8">
                                    <v-form @submit.prevent="submitProfile">
                                        <v-row>
                                            <v-col cols="12">
                                                <v-text-field
                                                    v-model="profileForm.name"
                                                    label="Full Name"
                                                    variant="outlined"
                                                    prepend-inner-icon="mdi-account"
                                                    :error-messages="profileForm.errors.name"
                                                    color="amber"
                                                    bg-color="rgba(255, 255, 255, 0.05)"
                                                    class="custom-input"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-text-field
                                                    v-model="profileForm.email"
                                                    label="Email Address"
                                                    type="email"
                                                    variant="outlined"
                                                    prepend-inner-icon="mdi-email"
                                                    :error-messages="profileForm.errors.email"
                                                    color="amber"
                                                    bg-color="rgba(255, 255, 255, 0.05)"
                                                    class="custom-input"
                                                ></v-text-field>
                                            </v-col>
                                        </v-row>

                                        <v-btn
                                            type="submit"
                                            color="amber-darken-2"
                                            size="large"
                                            :loading="profileForm.processing"
                                            class="mt-4 font-weight-bold"
                                            block
                                        >
                                            <v-icon icon="mdi-content-save" class="mr-2"></v-icon>
                                            Save Changes
                                        </v-btn>
                                    </v-form>
                                </v-card-text>
                            </v-card>
                        </v-window-item>

                        <!-- Update Password Tab -->
                        <v-window-item value="password">
                            <v-card class="glass-card" elevation="10">
                                <v-card-title class="pa-6 bg-gradient-green">
                                    <v-icon icon="mdi-lock-reset" class="mr-2"></v-icon>
                                    Change Password
                                </v-card-title>
                                <v-card-text class="pa-6 pa-md-8">
                                    <v-form @submit.prevent="submitPassword">
                                        <v-row>
                                            <v-col cols="12">
                                                <v-text-field
                                                    v-model="passwordForm.current_password"
                                                    label="Current Password"
                                                    :type="showCurrentPassword ? 'text' : 'password'"
                                                    variant="outlined"
                                                    prepend-inner-icon="mdi-lock"
                                                    :append-inner-icon="showCurrentPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                                    @click:append-inner="showCurrentPassword = !showCurrentPassword"
                                                    :error-messages="passwordForm.errors.current_password"
                                                    color="green-darken-2"
                                                    bg-color="rgba(255, 255, 255, 0.05)"
                                                    class="custom-input"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-text-field
                                                    v-model="passwordForm.password"
                                                    label="New Password"
                                                    :type="showPassword ? 'text' : 'password'"
                                                    variant="outlined"
                                                    prepend-inner-icon="mdi-lock-plus"
                                                    :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
                                                    @click:append-inner="showPassword = !showPassword"
                                                    :error-messages="passwordForm.errors.password"
                                                    color="green-darken-2"
                                                    bg-color="rgba(255, 255, 255, 0.05)"
                                                    class="custom-input"
                                                ></v-text-field>
                                            </v-col>
                                            <v-col cols="12">
                                                <v-text-field
                                                    v-model="passwordForm.password_confirmation"
                                                    label="Confirm New Password"
                                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                                    variant="outlined"
                                                    prepend-inner-icon="mdi-lock-check"
                                                    :append-inner-icon="showPasswordConfirmation ? 'mdi-eye-off' : 'mdi-eye'"
                                                    @click:append-inner="showPasswordConfirmation = !showPasswordConfirmation"
                                                    color="green-darken-2"
                                                    bg-color="rgba(255, 255, 255, 0.05)"
                                                    class="custom-input"
                                                ></v-text-field>
                                            </v-col>
                                        </v-row>

                                        <v-btn
                                            type="submit"
                                            color="green-darken-2"
                                            size="large"
                                            :loading="passwordForm.processing"
                                            class="mt-4 font-weight-bold"
                                            block
                                        >
                                            <v-icon icon="mdi-shield-check" class="mr-2"></v-icon>
                                            Update Password
                                        </v-btn>
                                    </v-form>
                                </v-card-text>
                            </v-card>
                        </v-window-item>

                        <!-- Security Tab -->
                        <v-window-item value="security">
                            <v-card class="glass-card" elevation="10">
                                <v-card-title class="pa-6 bg-gradient-red">
                                    <v-icon icon="mdi-shield-alert" class="mr-2"></v-icon>
                                    Account Security
                                </v-card-title>
                                <v-card-text class="pa-6 pa-md-8">
                                    <v-alert
                                        type="warning"
                                        variant="tonal"
                                        class="mb-6"
                                        icon="mdi-alert-circle"
                                    >
                                        <div class="text-subtitle-1 font-weight-bold mb-2">Delete Account</div>
                                        <div class="text-body-2">
                                            Once your account is deleted, all of its resources and data will be permanently deleted. 
                                            Before deleting your account, please download any data or information that you wish to retain.
                                        </div>
                                    </v-alert>

                                    <v-btn
                                        color="red-darken-2"
                                        size="large"
                                        variant="outlined"
                                        @click="deleteDialog = true"
                                        class="font-weight-bold"
                                        block
                                    >
                                        <v-icon icon="mdi-delete-forever" class="mr-2"></v-icon>
                                        Delete Account
                                    </v-btn>
                                </v-card-text>
                            </v-card>
                        </v-window-item>
                    </v-window>
                </v-col>
            </v-row>
        </v-container>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="500">
            <v-card class="glass-card-dark">
                <v-card-title class="pa-6 bg-red-darken-3 text-white">
                    <v-icon icon="mdi-alert-circle" class="mr-2"></v-icon>
                    Confirm Account Deletion
                </v-card-title>
                <v-card-text class="pa-6">
                    <p class="text-body-1 text-white mb-4">
                        Are you sure you want to delete your account? This action cannot be undone.
                    </p>
                    <v-text-field
                        v-model="deleteForm.password"
                        label="Enter your password to confirm"
                        :type="showDeletePassword ? 'text' : 'password'"
                        variant="outlined"
                        prepend-inner-icon="mdi-lock"
                        :append-inner-icon="showDeletePassword ? 'mdi-eye-off' : 'mdi-eye'"
                        @click:append-inner="showDeletePassword = !showDeletePassword"
                        :error-messages="deleteForm.errors.password"
                        color="red"
                        bg-color="rgba(255, 255, 255, 0.05)"
                        class="custom-input"
                    ></v-text-field>
                </v-card-text>
                <v-card-actions class="pa-6 pt-0">
                    <v-btn
                        variant="text"
                        @click="deleteDialog = false"
                        class="text-white"
                    >
                        Cancel
                    </v-btn>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="red-darken-2"
                        variant="flat"
                        @click="confirmDelete"
                        :loading="deleteForm.processing"
                        class="font-weight-bold"
                    >
                        Delete Account
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Snackbar -->
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
    </ShopLayout>
</template>

<style scoped>
/* Animated Background */
.profile-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
    z-index: 0;
    overflow: hidden;
}

.orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.3;
    animation: float 20s infinite ease-in-out;
}

.orb-1 {
    width: 500px;
    height: 500px;
    background: linear-gradient(135deg, #00704A, #00A86B);
    top: -10%;
    left: -10%;
    animation-delay: 0s;
}

.orb-2 {
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, #FFC107, #FF6F00);
    bottom: -10%;
    right: -10%;
    animation-delay: 7s;
}

.orb-3 {
    width: 350px;
    height: 350px;
    background: linear-gradient(135deg, #4CAF50, #8BC34A);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    animation-delay: 14s;
}

@keyframes float {
    0%, 100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -30px) scale(1.1);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.9);
    }
}

/* Glass Card Effect */
.glass-card {
    background: rgba(255, 255, 255, 0.05) !important;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px !important;
    overflow: hidden;
}

.glass-card-dark {
    background: rgba(0, 0, 0, 0.7) !important;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px !important;
}

/* Profile Header Card */
.profile-header-card {
    background: linear-gradient(135deg, rgba(0, 112, 74, 0.3), rgba(0, 168, 107, 0.2)) !important;
    border: 2px solid rgba(255, 193, 7, 0.3);
}

.profile-avatar {
    border: 4px solid rgba(255, 193, 7, 0.5);
    box-shadow: 0 8px 32px rgba(255, 193, 7, 0.3);
}

/* Stat Cards */
.stat-card {
    border-radius: 12px !important;
    border: 1px solid rgba(255, 255, 255, 0.1);
    min-width: 100px;
}

/* Gradient Backgrounds */
.bg-gradient-amber {
    background: linear-gradient(135deg, #FFA000, #FF6F00) !important;
    color: white !important;
}

.bg-gradient-green {
    background: linear-gradient(135deg, #00704A, #00A86B) !important;
    color: white !important;
}

.bg-gradient-red {
    background: linear-gradient(135deg, #C62828, #D32F2F) !important;
    color: white !important;
}

/* Custom Input Styling */
.custom-input :deep(.v-field) {
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}

.custom-input :deep(.v-field--focused) {
    border-width: 2px !important;
}

.custom-input :deep(.v-field__input) {
    color: white !important;
}

.custom-input :deep(.v-label) {
    color: rgba(255, 255, 255, 0.7) !important;
}

.custom-input :deep(.v-icon) {
    color: rgba(255, 255, 255, 0.7) !important;
}

/* Tabs */
:deep(.v-tab) {
    text-transform: none !important;
    letter-spacing: 0.5px;
}

:deep(.v-tab--selected) {
    background: rgba(255, 193, 7, 0.1);
    border-radius: 8px;
}

/* Mobile Responsive */
@media (max-width: 960px) {
    .orb {
        filter: blur(60px);
    }
    
    .orb-1, .orb-2, .orb-3 {
        width: 300px;
        height: 300px;
    }
}

@media (max-width: 600px) {
    .profile-avatar {
        width: 100px !important;
        height: 100px !important;
    }
    
    .orb {
        filter: blur(40px);
    }
}
</style>