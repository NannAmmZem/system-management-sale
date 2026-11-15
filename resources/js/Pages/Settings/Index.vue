<template>
  <AdminLayout>
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 via-blue-900/20 to-indigo-900/20"></div>
      <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
      <div class="absolute top-3/4 left-1/2 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen" :class="$vuetify.display.mobile ? 'p-3' : 'p-6'">
      <!-- Header -->
      <div class="mb-8">
        <v-row align="center" justify="space-between">
          <v-col cols="12" :md="$vuetify.display.mobile ? 12 : 6">
            <h1 :class="$vuetify.display.mobile ? 'text-h5' : 'text-4xl'" class="font-bold text-white mb-2 flex items-center">
              <v-icon icon="mdi-cog" class="mr-3 text-amber-400" :size="$vuetify.display.mobile ? 28 : 40"></v-icon>
              <span v-if="!$vuetify.display.xs">System Settings</span>
              <span v-else>Settings</span>
            </h1>
            <p :class="$vuetify.display.mobile ? 'text-body-2' : ''" class="text-gray-300">
              <span v-if="!$vuetify.display.xs">Configure your store management system</span>
              <span v-else>Configure system</span>
            </p>
          </v-col>
          <v-col cols="12" :md="$vuetify.display.mobile ? 12 : 6" class="d-flex justify-end" :class="$vuetify.display.mobile ? 'flex-column ga-2' : 'flex-row ga-3'">
            <v-btn 
              @click="clearCache" 
              :loading="clearingCache"
              color="orange"
              variant="outlined"
              prepend-icon="mdi-cached"
              :size="$vuetify.display.mobile ? 'small' : 'default'"
              :block="$vuetify.display.xs"
            >
              Clear Cache
            </v-btn>
            <v-btn 
              @click="createBackup" 
              :loading="creatingBackup"
              color="green"
              variant="outlined"
              prepend-icon="mdi-backup-restore"
              :size="$vuetify.display.mobile ? 'small' : 'default'"
              :block="$vuetify.display.xs"
            >
              Create Backup
            </v-btn>
          </v-col>
        </v-row>
      </div>

      <!-- Settings Grid -->
      <v-row class="mb-6">
        
        <!-- Company Settings -->
        <v-col cols="12" lg="8">
          <div class="backdrop-blur-xl bg-white/10 rounded-3xl border border-white/20 shadow-2xl" :class="$vuetify.display.mobile ? 'p-4' : 'p-8'">
            <h2 :class="$vuetify.display.mobile ? 'text-h6' : 'text-2xl'" class="font-bold text-white mb-6 flex items-center">
              <v-icon icon="mdi-office-building" class="mr-3 text-blue-400" :size="$vuetify.display.mobile ? 20 : 24"></v-icon>
              Company Information
            </h2>
            
            <form @submit.prevent="updateSettings">
              <v-row class="mb-6">
                <v-col cols="12" md="6">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Company Name</label>
                  <v-text-field
                    v-model="form.company_name"
                    variant="outlined"
                    density="compact"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                    :error-messages="form.errors.company_name"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                  <v-text-field
                    v-model="form.company_email"
                    type="email"
                    variant="outlined"
                    density="compact"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                    :error-messages="form.errors.company_email"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Phone Number</label>
                  <v-text-field
                    v-model="form.company_phone"
                    variant="outlined"
                    density="compact"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                    :error-messages="form.errors.company_phone"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" md="6">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Currency</label>
                  <v-select
                    v-model="form.currency"
                    :items="currencies"
                    variant="outlined"
                    density="compact"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                    :error-messages="form.errors.currency"
                  ></v-select>
                </v-col>
              </v-row>
              
              <v-row class="mb-6">
                <v-col cols="12">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Company Address</label>
                  <v-textarea
                    v-model="form.company_address"
                    variant="outlined"
                    rows="3"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                  ></v-textarea>
                </v-col>
              </v-row>
              
              <v-row class="mb-6">
                <v-col cols="12" sm="6" md="4">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Tax Rate (%)</label>
                  <v-text-field
                    v-model.number="form.tax_rate"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    variant="outlined"
                    density="compact"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" sm="6" md="4">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Low Stock Alert</label>
                  <v-text-field
                    v-model.number="form.low_stock_alert"
                    type="number"
                    min="1"
                    variant="outlined"
                    density="compact"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                  ></v-text-field>
                </v-col>
                
                <v-col cols="12" sm="12" md="4">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Timezone</label>
                  <v-select
                    v-model="form.timezone"
                    :items="timezones"
                    variant="outlined"
                    density="compact"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                  ></v-select>
                </v-col>
              </v-row>
              
              <v-row class="mb-6">
                <v-col cols="12">
                  <label class="block text-sm font-medium text-gray-300 mb-2">Receipt Footer Message</label>
                  <v-textarea
                    v-model="form.receipt_footer"
                    variant="outlined"
                    rows="2"
                    color="amber"
                    bg-color="rgba(255,255,255,0.1)"
                    class="glass-input"
                    placeholder="Thank you for your business!"
                  ></v-textarea>
                </v-col>
              </v-row>
              
              <!-- Notification Settings -->
              <div class="mb-6">
                <h3 :class="$vuetify.display.mobile ? 'text-body-1' : 'text-lg'" class="font-semibold text-white flex items-center mb-4">
                  <v-icon icon="mdi-bell" class="mr-2 text-green-400" :size="$vuetify.display.mobile ? 18 : 20"></v-icon>
                  Notification Preferences
                </h3>
                
                <v-row>
                  <v-col cols="12" sm="6" md="4">
                    <v-switch
                      v-model="form.auto_backup"
                      label="Auto Backup"
                      color="amber"
                      density="compact"
                      class="text-white"
                    ></v-switch>
                  </v-col>
                  
                  <v-col cols="12" sm="6" md="4">
                    <v-switch
                      v-model="form.email_notifications"
                      label="Email Notifications"
                      color="amber"
                      density="compact"
                      class="text-white"
                    ></v-switch>
                  </v-col>
                  
                  <v-col cols="12" sm="6" md="4">
                    <v-switch
                      v-model="form.sms_notifications"
                      label="SMS Notifications"
                      color="amber"
                      density="compact"
                      class="text-white"
                    ></v-switch>
                  </v-col>
                </v-row>
              </div>
              
              <div class="d-flex justify-end">
                <v-btn
                  type="submit"
                  :loading="form.processing"
                  :disabled="form.processing"
                  color="amber"
                  :size="$vuetify.display.mobile ? 'default' : 'large'"
                  prepend-icon="mdi-content-save"
                  :class="$vuetify.display.mobile ? 'px-4' : 'px-8'"
                  :block="$vuetify.display.xs"
                >
                  Save Settings
                </v-btn>
              </div>
            </form>
          </div>
        </v-col>
        
        <!-- System Information & Tools -->
        <v-col cols="12" lg="4">
          <v-row>
            
            <!-- System Stats -->
            <v-col cols="12">
              <div class="backdrop-blur-xl bg-white/10 rounded-3xl border border-white/20 shadow-2xl" :class="$vuetify.display.mobile ? 'p-4' : 'p-6'">
                <h3 :class="$vuetify.display.mobile ? 'text-body-1' : 'text-xl'" class="font-bold text-white mb-4 flex items-center">
                  <v-icon icon="mdi-information" class="mr-2 text-cyan-400" :size="$vuetify.display.mobile ? 18 : 20"></v-icon>
                  System Information
                </h3>
                
                <div class="space-y-4">
                  <div class="flex justify-between items-center">
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-gray-300">PHP Version</span>
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-white font-mono">{{ stats.php_version }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center">
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-gray-300">Laravel Version</span>
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-white font-mono">{{ stats.laravel_version }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center">
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-gray-300">Database Size</span>
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-white font-mono">{{ formatBytes(stats.database_size) }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center">
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-gray-300">Storage Used</span>
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-white font-mono">{{ formatBytes(stats.storage_used) }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center">
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-gray-300">Cache Size</span>
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-white font-mono">{{ formatBytes(stats.cache_size) }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center">
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-gray-300">System Uptime</span>
                    <span :class="$vuetify.display.mobile ? 'text-caption' : ''" class="text-white font-mono">{{ stats.uptime }}</span>
                  </div>
                </div>
              </div>
            </v-col>
            
            <!-- Recent Backups -->
            <v-col cols="12">
              <div class="backdrop-blur-xl bg-white/10 rounded-3xl border border-white/20 shadow-2xl" :class="$vuetify.display.mobile ? 'p-4' : 'p-6'">
                <h3 :class="$vuetify.display.mobile ? 'text-body-1' : 'text-xl'" class="font-bold text-white mb-4 flex items-center">
                  <v-icon icon="mdi-backup-restore" class="mr-2 text-green-400" :size="$vuetify.display.mobile ? 18 : 20"></v-icon>
                  Recent Backups
                </h3>
                
                <div :class="$vuetify.display.mobile ? 'space-y-2' : 'space-y-3'" :style="{ maxHeight: $vuetify.display.mobile ? '200px' : '264px', overflowY: 'auto' }">
                  <div 
                    v-for="backup in backups.slice(0, 5)" 
                    :key="backup.name"
                    :class="$vuetify.display.mobile ? 'p-2' : 'p-3'"
                    class="flex justify-between items-center rounded-lg bg-white/5 border border-white/10"
                  >
                    <div>
                      <div :class="$vuetify.display.mobile ? 'text-caption' : 'text-sm'" class="text-white font-medium">{{ backup.name }}</div>
                      <div class="text-gray-400 text-xs">{{ backup.created_at }}</div>
                    </div>
                    <div class="text-gray-300 text-xs">
                      {{ formatBytes(backup.size) }}
                    </div>
                  </div>
                  
                  <div v-if="backups.length === 0" :class="$vuetify.display.mobile ? 'py-3' : 'py-4'" class="text-center text-gray-400">
                    No backups found
                  </div>
                </div>
              </div>
            </v-col>
            
            <!-- Quick Actions -->
            <v-col cols="12">
              <div class="backdrop-blur-xl bg-white/10 rounded-3xl border border-white/20 shadow-2xl" :class="$vuetify.display.mobile ? 'p-4' : 'p-6'">
                <h3 :class="$vuetify.display.mobile ? 'text-body-1' : 'text-xl'" class="font-bold text-white mb-4 flex items-center">
                  <v-icon icon="mdi-lightning-bolt" class="mr-2 text-yellow-400" :size="$vuetify.display.mobile ? 18 : 20"></v-icon>
                  Quick Actions
                </h3>
                
                <div :class="$vuetify.display.mobile ? 'space-y-2' : 'space-y-3'">
                  <v-btn
                    @click="clearCache"
                    :loading="clearingCache"
                    block
                    color="orange"
                    variant="outlined"
                    prepend-icon="mdi-cached"
                    :size="$vuetify.display.mobile ? 'small' : 'default'"
                  >
                    <span v-if="!$vuetify.display.xs">Clear System Cache</span>
                    <span v-else>Clear Cache</span>
                  </v-btn>
                  
                  <v-btn
                    @click="createBackup"
                    :loading="creatingBackup"
                    block
                    color="green"
                    variant="outlined"
                    prepend-icon="mdi-backup-restore"
                    :size="$vuetify.display.mobile ? 'small' : 'default'"
                  >
                    <span v-if="!$vuetify.display.xs">Create Database Backup</span>
                    <span v-else>Create Backup</span>
                  </v-btn>
                  
                  <v-btn
                    @click="optimizeSystem"
                    :loading="optimizing"
                    block
                    color="purple"
                    variant="outlined"
                    prepend-icon="mdi-tune"
                    :size="$vuetify.display.mobile ? 'small' : 'default'"
                  >
                    Optimize System
                  </v-btn>
                </div>
              </div>
            </v-col>
            
          </v-row>
        </v-col>
      </v-row>
    </div>

    <!-- Success Snackbar -->
    <v-snackbar
      v-model="snackbar.show"
      :color="snackbar.color"
      :timeout="4000"
      location="top right"
    >
      {{ snackbar.message }}
      <template v-slot:actions>
        <v-btn variant="text" @click="snackbar.show = false">Close</v-btn>
      </template>
    </v-snackbar>
  </AdminLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

// Props
const props = defineProps({
  settings: Object,
  stats: Object,
  backups: Array
})

// Get page props for flash messages
const page = usePage()

// Reactive form data using Inertia form helper
const form = useForm({
  company_name: props.settings.company_name,
  company_email: props.settings.company_email,
  company_phone: props.settings.company_phone,
  company_address: props.settings.company_address,
  tax_rate: props.settings.tax_rate,
  currency: props.settings.currency,
  timezone: props.settings.timezone,
  low_stock_alert: props.settings.low_stock_alert,
  receipt_footer: props.settings.receipt_footer,
  auto_backup: props.settings.auto_backup,
  email_notifications: props.settings.email_notifications,
  sms_notifications: props.settings.sms_notifications
})

// Loading states
const clearingCache = ref(false)
const creatingBackup = ref(false)
const optimizing = ref(false)

// Snackbar
const snackbar = reactive({
  show: false,
  message: '',
  color: 'success'
})

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    showSnackbar(flash.success, 'success')
  }
  if (flash?.error) {
    showSnackbar(flash.error, 'error')
  }
}, { deep: true })

// Options
const currencies = [
  { title: 'US Dollar (USD)', value: 'USD' },
  { title: 'Euro (EUR)', value: 'EUR' },
  { title: 'British Pound (GBP)', value: 'GBP' },
  { title: 'Japanese Yen (JPY)', value: 'JPY' },
  { title: 'Canadian Dollar (CAD)', value: 'CAD' }
]

const timezones = [
  { title: 'Eastern Time (America/New_York)', value: 'America/New_York' },
  { title: 'Central Time (America/Chicago)', value: 'America/Chicago' },
  { title: 'Mountain Time (America/Denver)', value: 'America/Denver' },
  { title: 'Pacific Time (America/Los_Angeles)', value: 'America/Los_Angeles' },
  { title: 'UTC (UTC)', value: 'UTC' }
]

// Methods
const updateSettings = () => {
  form.post(route('settings.update'), {
    preserveScroll: true,
    onError: (errors) => {
      console.error('Validation errors:', errors)
      showSnackbar('Please check the form for errors', 'error')
    }
  })
}

const clearCache = () => {
  clearingCache.value = true
  
  router.post(route('settings.clear-cache'), {}, {
    preserveScroll: true,
    onFinish: () => {
      clearingCache.value = false
    }
  })
}

const createBackup = () => {
  creatingBackup.value = true
  
  router.post(route('settings.backup'), {}, {
    preserveScroll: true,
    onFinish: () => {
      creatingBackup.value = false
    },
    onSuccess: () => {
      // Reload to show new backup
      setTimeout(() => {
        router.reload({ only: ['backups'] })
      }, 1000)
    }
  })
}

const optimizeSystem = async () => {
  optimizing.value = true
  
  // Simulate optimization process
  setTimeout(() => {
    showSnackbar('System optimization completed successfully!', 'success')
    optimizing.value = false
  }, 2000)
}

const showSnackbar = (message, color = 'success') => {
  snackbar.message = message
  snackbar.color = color
  snackbar.show = true
}

const formatBytes = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>

<style scoped>
.glass-input :deep(.v-field) {
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.glass-input :deep(.v-field--focused) {
  border-color: rgb(245, 158, 11) !important;
  box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
}

.glass-input :deep(.v-field__input) {
  color: white !important;
}

.glass-input :deep(.v-field__input::placeholder) {
  color: rgba(255, 255, 255, 0.6) !important;
}

.glass-input :deep(.v-label) {
  color: rgba(255, 255, 255, 0.8) !important;
}

.glass-input :deep(.v-selection-control__wrapper) {
  color: white !important;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .backdrop-blur-xl {
    border-radius: 1.5rem !important;
  }
}

@media (max-width: 600px) {
  .backdrop-blur-xl {
    border-radius: 1rem !important;
  }
  
  .space-y-2 > * + * {
    margin-top: 0.5rem;
  }
  
  .space-y-3 > * + * {
    margin-top: 0.75rem;
  }
  
  .space-y-4 > * + * {
    margin-top: 1rem;
  }
}

@media (max-width: 480px) {
  .v-btn {
    font-size: 0.875rem;
  }
  
  .font-mono {
    font-size: 0.75rem;
  }
}
</style>