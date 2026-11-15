<script setup>
import { ref, reactive } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    products: Array,
    categories: Array,
    suppliers: Array
});

const dialog = ref(false);
const isEditing = ref(false);
const previewImage = ref(null);
const fileInput = ref(null);

// Snackbar for notifications
const snackbar = reactive({
    show: false,
    message: '',
    color: 'success'
});

const form = useForm({
    id: null,
    name_en: '',
    name_kh: '',
    barcode: '',
    category_id: null,
    supplier_id: null,
    cost_price: 0,
    sale_price: 0,
    qty: 0,
    alert_qty: 10,
    description: '',
    status: 'active',
    image: null,
});

const openCreate = () => {
    form.reset();
    form.status = 'active';
    form.alert_qty = 10;
    isEditing.value = false;
    previewImage.value = null;
    dialog.value = true;
};

const openEdit = (item) => {
    form.id = item.id;
    form.name_en = item.name_en || '';
    form.name_kh = item.name_kh || '';
    form.barcode = item.barcode || '';
    form.category_id = item.category_id;
    form.supplier_id = item.supplier_id;
    form.cost_price = item.cost_price || 0;
    form.sale_price = item.sale_price || 0;
    form.qty = item.qty || 0;
    form.alert_qty = item.alert_qty || 10;
    form.description = item.description || '';
    form.status = item.status || 'active';
    form.image = null; // Reset file input
    
    // Show existing image using image_url
    previewImage.value = item.image_url;
    isEditing.value = true;
    dialog.value = true;
};

const handleFileChange = (eventOrFiles) => {
    console.log('File input changed:', eventOrFiles);
    
    let files;
    if (eventOrFiles && eventOrFiles.target) {
        // This is a change event
        files = eventOrFiles.target.files;
    } else if (Array.isArray(eventOrFiles)) {
        // This is from v-model update
        files = eventOrFiles;
    } else if (eventOrFiles) {
        // Single file from v-model
        files = [eventOrFiles];
    }
    
    if (files && files.length > 0) {
        const file = files[0];
        console.log('Selected file:', file.name, file.size, file.type);
        form.image = file;
        previewImage.value = URL.createObjectURL(file);
    } else {
        console.log('No file selected');
        form.image = null;
        previewImage.value = null;
    }
};

const submit = () => {
    // Convert numeric fields to proper types
    form.cost_price = parseFloat(form.cost_price) || 0;
    form.sale_price = parseFloat(form.sale_price) || 0;
    form.qty = parseInt(form.qty) || 0;
    form.alert_qty = parseInt(form.alert_qty) || 10;

    console.log('Submitting form with image:', form.image);

    if (isEditing.value) {
        // For editing with file upload, use POST with _method field for Laravel method spoofing
        // Create FormData manually to ensure _method is included
        const formData = new FormData();
        
        // Add all form fields
        formData.append('_method', 'PUT');
        formData.append('name_en', form.name_en || '');
        formData.append('name_kh', form.name_kh || '');
        formData.append('barcode', form.barcode || '');
        formData.append('category_id', form.category_id || '');
        if (form.supplier_id) formData.append('supplier_id', form.supplier_id);
        formData.append('cost_price', form.cost_price);
        formData.append('sale_price', form.sale_price);
        formData.append('qty', form.qty);
        formData.append('alert_qty', form.alert_qty);
        formData.append('description', form.description || '');
        formData.append('status', form.status || 'active');
        
        // Add image if present
        if (form.image && form.image instanceof File) {
            formData.append('image', form.image);
        }
        
        // Use router.post with FormData
        router.post(route('products.update', form.id), formData, {
            preserveScroll: true,
            onSuccess: () => {
                console.log('Update success');
                dialog.value = false;
                showSnackbar('Product updated successfully!', 'success');
                form.reset();
                previewImage.value = null;
            },
            onError: (errors) => {
                console.error('Update errors:', errors);
                showSnackbar('Please check the form for errors', 'error');
            }
        });
    } else {
        // For creating, use POST method
        form.post(route('products.store'), {
            preserveScroll: true,
            onSuccess: (response) => {
                console.log('Create success:', response);
                dialog.value = false;
                showSnackbar('Product created successfully!', 'success');
                form.reset();
                previewImage.value = null;
            },
            onError: (errors) => {
                console.error('Create errors:', errors);
                showSnackbar('Please check the form for errors', 'error');
            }
        });
    }
};

const deleteProduct = (id) => {
    if(confirm('Are you sure you want to delete this product?')) {
        form.delete(route('products.destroy', id), {
            onSuccess: () => {
                showSnackbar('Product deleted successfully!', 'success');
            },
            onError: () => {
                showSnackbar('Failed to delete product', 'error');
            }
        });
    }
};

const showSnackbar = (message, color = 'success') => {
    snackbar.message = message;
    snackbar.color = color;
    snackbar.show = true;
};
</script>

<template>
    <AdminLayout>
        <!-- Animated Background -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-gradient-to-br from-purple-900/20 via-blue-900/20 to-indigo-900/20"></div>
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
        </div>

        <!-- Header -->
        <div class="relative z-10 mb-8">
            <v-row align="center" justify="space-between">
                <v-col cols="12" md="8">
                    <h1 :class="$vuetify.display.mobile ? 'text-h5' : 'text-4xl'" class="font-bold text-white mb-2 flex items-center">
                        <v-icon icon="mdi-package-variant" class="mr-3 text-amber-400" :size="$vuetify.display.mobile ? 28 : 40"></v-icon>
                        <span v-if="!$vuetify.display.xs">Product Management</span>
                        <span v-else>Products</span>
                    </h1>
                    <p :class="$vuetify.display.mobile ? 'text-body-2' : ''" class="text-gray-300">
                        <span v-if="!$vuetify.display.xs">Manage your store inventory and products</span>
                        <span v-else>Manage inventory</span>
                    </p>
                </v-col>
                <v-col cols="12" md="4" class="d-flex justify-end">
                    <v-btn 
                        color="amber" 
                        :size="$vuetify.display.mobile ? 'default' : 'large'"
                        prepend-icon="mdi-plus" 
                        @click="openCreate"
                        :class="$vuetify.display.mobile ? 'px-4' : 'px-6'"
                        :block="$vuetify.display.xs"
                    >
                        Add Product
                    </v-btn>
                </v-col>
            </v-row>
        </div>

        <!-- Products Table -->
        <div class="relative z-10">
            <div class="backdrop-blur-xl bg-white/10 rounded-3xl border border-white/20 p-6 shadow-2xl" :style="{ overflowX: 'auto' }">
                <v-table theme="dark" class="bg-transparent">
                    <thead>
                        <tr>
                            <th class="text-left text-white font-bold" :class="$vuetify.display.mobile ? 'text-caption' : ''">Image</th>
                            <th class="text-left text-white font-bold" :class="$vuetify.display.mobile ? 'text-caption' : ''">Details</th>
                            <th class="text-left text-white font-bold" :class="$vuetify.display.mobile ? 'text-caption' : ''" v-if="!$vuetify.display.xs">Category</th>
                            <th class="text-left text-white font-bold" :class="$vuetify.display.mobile ? 'text-caption' : ''" v-if="!$vuetify.display.smAndDown">Supplier</th>
                            <th class="text-left text-white font-bold" :class="$vuetify.display.mobile ? 'text-caption' : ''">Price</th>
                            <th class="text-left text-white font-bold" :class="$vuetify.display.mobile ? 'text-caption' : ''">Stock</th>
                            <th class="text-left text-white font-bold" :class="$vuetify.display.mobile ? 'text-caption' : ''" v-if="!$vuetify.display.xs">Status</th>
                            <th class="text-right text-white font-bold" :class="$vuetify.display.mobile ? 'text-caption' : ''">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in products" :key="item.id" class="hover:bg-white/5 transition-colors">
                            <td :class="$vuetify.display.mobile ? 'py-2' : 'py-4'">
                                <v-avatar :size="$vuetify.display.mobile ? 40 : 60" rounded="lg" class="border-2 border-white/20">
                                    <v-img :src="item.image_url" cover>
                                        <template v-slot:placeholder>
                                            <div class="d-flex align-center justify-center fill-height">
                                                <v-icon icon="mdi-image" :size="$vuetify.display.mobile ? 16 : 24" color="grey"></v-icon>
                                            </div>
                                        </template>
                                    </v-img>
                                </v-avatar>
                            </td>
                            <td>
                                <div class="text-white font-semibold" :class="$vuetify.display.mobile ? 'text-caption' : ''">{{ item.name_en }}</div>
                                <div class="text-gray-400" :class="$vuetify.display.mobile ? 'text-caption' : 'text-sm'" v-if="!$vuetify.display.xs">{{ item.name_kh || 'No Khmer name' }}</div>
                                <div class="text-gray-500 text-xs mt-1" v-if="!$vuetify.display.xs">{{ item.barcode }}</div>
                            </td>
                            <td v-if="!$vuetify.display.xs">
                                <v-chip 
                                    size="x-small" 
                                    color="blue" 
                                    variant="outlined"
                                    class="text-white"
                                >
                                    {{ item.category?.name || 'N/A' }}
                                </v-chip>
                            </td>
                            <td v-if="!$vuetify.display.smAndDown">
                                <v-chip 
                                    size="x-small" 
                                    color="purple" 
                                    variant="outlined"
                                    class="text-white"
                                >
                                    {{ item.supplier?.name || 'N/A' }}
                                </v-chip>
                            </td>
                            <td>
                                <div class="text-green-400 font-bold" :class="$vuetify.display.mobile ? 'text-body-2' : 'text-lg'">${{ item.sale_price }}</div>
                                <div class="text-gray-400 text-xs" v-if="!$vuetify.display.xs">Cost: ${{ item.cost_price }}</div>
                            </td>
                            <td>
                                <v-chip 
                                    :color="item.qty < (item.alert_qty || 10) ? 'error' : 'success'" 
                                    size="x-small"
                                    class="font-bold"
                                >
                                    {{ item.qty }}
                                </v-chip>
                                <div v-if="item.qty < (item.alert_qty || 10) && !$vuetify.display.xs" class="text-red-400 text-xs mt-1">
                                    Low!
                                </div>
                            </td>
                            <td v-if="!$vuetify.display.xs">
                                <v-chip 
                                    :color="item.status === 'active' ? 'success' : 'warning'" 
                                    size="x-small"
                                    class="font-bold"
                                >
                                    {{ item.status || 'active' }}
                                </v-chip>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end space-x-1">
                                    <v-btn 
                                        icon="mdi-pencil" 
                                        color="amber" 
                                        variant="outlined" 
                                        size="x-small"
                                        @click="openEdit(item)"
                                    ></v-btn>
                                    <v-btn 
                                        icon="mdi-delete" 
                                        color="red" 
                                        variant="outlined" 
                                        size="x-small"
                                        @click="deleteProduct(item.id)"
                                    ></v-btn>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="products.length === 0">
                            <td :colspan="$vuetify.display.xs ? 5 : $vuetify.display.smAndDown ? 6 : 8" class="text-center py-8 text-gray-400">
                                No products found. Add your first product to get started!
                            </td>
                        </tr>
                    </tbody>
                </v-table>
            </div>
        </div>

        <!-- Product Form Dialog -->
        <v-dialog v-model="dialog" :max-width="$vuetify.display.mobile ? '95%' : '800px'" persistent scrollable :fullscreen="$vuetify.display.xs">
            <v-card theme="dark" class="backdrop-blur-xl bg-white/10 border border-white/20">
                <v-card-title class="bg-gradient-to-r from-amber-500 to-orange-500 pa-4">
                    <div :class="$vuetify.display.mobile ? 'text-body-1' : 'text-xl'" class="font-bold text-white flex items-center">
                        <v-icon :icon="isEditing ? 'mdi-pencil' : 'mdi-plus'" class="mr-3" :size="$vuetify.display.mobile ? 20 : 24"></v-icon>
                        {{ isEditing ? 'Edit Product' : 'Add New Product' }}
                    </div>
                </v-card-title>
                
                <v-card-text :class="$vuetify.display.mobile ? 'pa-4' : 'pa-6'" style="max-height: 70vh; overflow-y: auto;">
                    <v-form @submit.prevent="submit">
                        <v-row>
                            <!-- Product Names -->
                            <v-col cols="12" :md="$vuetify.display.mobile ? 12 : 6">
                                <v-text-field 
                                    v-model="form.name_en" 
                                    label="Product Name (English) *"
                                    variant="outlined" 
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                    :error-messages="form.errors.name_en"
                                ></v-text-field>
                            </v-col>
                            
                            <v-col cols="12" :md="$vuetify.display.mobile ? 12 : 6">
                                <v-text-field 
                                    v-model="form.name_kh" 
                                    label="Product Name (Khmer)"
                                    variant="outlined" 
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                ></v-text-field>
                            </v-col>
                            
                            <!-- Category and Supplier -->
                            <v-col cols="12" :md="$vuetify.display.mobile ? 12 : 6">
                                <v-select 
                                    v-model="form.category_id" 
                                    :items="categories" 
                                    item-title="name" 
                                    item-value="id" 
                                    label="Category *"
                                    variant="outlined"
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                    :error-messages="form.errors.category_id"
                                ></v-select>
                            </v-col>
                            
                            <v-col cols="12" :md="$vuetify.display.mobile ? 12 : 6">
                                <v-select 
                                    v-model="form.supplier_id" 
                                    :items="suppliers" 
                                    item-title="name" 
                                    item-value="id" 
                                    label="Supplier"
                                    variant="outlined"
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                    clearable
                                ></v-select>
                            </v-col>
                            
                            <!-- Barcode and Status -->
                            <v-col cols="12" :md="$vuetify.display.mobile ? 12 : 6">
                                <v-text-field 
                                    v-model="form.barcode" 
                                    label="Barcode *"
                                    variant="outlined" 
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                    :error-messages="form.errors.barcode"
                                ></v-text-field>
                            </v-col>
                            
                            <v-col cols="12" :md="$vuetify.display.mobile ? 12 : 6">
                                <v-select 
                                    v-model="form.status" 
                                    :items="[
                                        { title: 'Active', value: 'active' },
                                        { title: 'Inactive', value: 'inactive' }
                                    ]"
                                    label="Status"
                                    variant="outlined"
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                ></v-select>
                            </v-col>
                            
                            <!-- Pricing -->
                            <v-col cols="12" :sm="6" :md="4">
                                <v-text-field 
                                    v-model.number="form.cost_price" 
                                    label="Cost Price ($) *"
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    variant="outlined" 
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                    :error-messages="form.errors.cost_price"
                                ></v-text-field>
                            </v-col>
                            
                            <v-col cols="12" :sm="6" :md="4">
                                <v-text-field 
                                    v-model.number="form.sale_price" 
                                    label="Sale Price ($) *"
                                    type="number" 
                                    step="0.01"
                                    min="0"
                                    variant="outlined" 
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                    :error-messages="form.errors.sale_price"
                                ></v-text-field>
                            </v-col>
                            
                            <!-- Inventory -->
                            <v-col cols="12" :sm="6" :md="4">
                                <v-text-field 
                                    v-model.number="form.qty" 
                                    label="Quantity *"
                                    type="number" 
                                    min="0"
                                    variant="outlined" 
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                    :error-messages="form.errors.qty"
                                ></v-text-field>
                            </v-col>
                            
                            <v-col cols="12" :sm="6" :md="$vuetify.display.mobile ? 12 : 6">
                                <v-text-field 
                                    v-model.number="form.alert_qty" 
                                    label="Low Stock Alert"
                                    type="number" 
                                    min="1"
                                    variant="outlined" 
                                    density="compact"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                ></v-text-field>
                            </v-col>
                            
                            <!-- Image Upload -->
                            <v-col cols="12" :sm="6" :md="$vuetify.display.mobile ? 12 : 6">
                                <v-file-input 
                                    ref="fileInput"
                                    v-model="form.image"
                                    label="Product Image"
                                    variant="outlined" 
                                    density="compact"
                                    prepend-icon="mdi-camera"
                                    @update:model-value="handleFileChange"
                                    @change="handleFileChange"
                                    accept="image/*"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                    :error-messages="form.errors.image"
                                ></v-file-input>
                            </v-col>
                            
                            <!-- Image Preview -->
                            <v-col cols="12" v-if="previewImage">
                                <div class="text-center">
                                    <v-avatar :size="$vuetify.display.mobile ? 80 : 100" rounded="lg" class="border-2 border-amber-400">
                                        <v-img :src="previewImage" cover></v-img>
                                    </v-avatar>
                                </div>
                            </v-col>
                            
                            <!-- Description -->
                            <v-col cols="12">
                                <v-textarea 
                                    v-model="form.description" 
                                    label="Description"
                                    variant="outlined" 
                                    rows="2"
                                    color="amber"
                                    bg-color="rgba(255,255,255,0.1)"
                                    class="glass-input"
                                ></v-textarea>
                            </v-col>
                        </v-row>
                    </v-form>
                </v-card-text>
                
                <v-card-actions :class="$vuetify.display.mobile ? 'pa-3' : 'pa-4'" class="bg-grey-darken-4">
                    <v-spacer></v-spacer>
                    <v-btn 
                        color="grey" 
                        variant="outlined"
                        @click="dialog = false"
                        :disabled="form.processing"
                        :size="$vuetify.display.mobile ? 'small' : 'default'"
                    >
                        Cancel
                    </v-btn>
                    <v-btn 
                        type="submit" 
                        color="amber" 
                        :loading="form.processing"
                        prepend-icon="mdi-content-save"
                        @click="submit"
                        :size="$vuetify.display.mobile ? 'small' : 'default'"
                    >
                        {{ isEditing ? 'Update' : 'Create' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Success/Error Snackbar -->
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

.glass-input :deep(.v-select__selection-text) {
  color: white !important;
}

.glass-input :deep(.v-field__append-inner) {
  color: rgba(255, 255, 255, 0.8) !important;
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
    padding: 1rem !important;
  }
}

@media (max-width: 600px) {
  .v-table {
    font-size: 0.75rem;
  }
  
  .v-table th,
  .v-table td {
    padding: 6px 4px !important;
  }
  
  .space-x-1 > * + * {
    margin-left: 0.25rem;
  }
  
  .backdrop-blur-xl {
    padding: 0.75rem !important;
    border-radius: 1rem !important;
  }
}

@media (max-width: 480px) {
  .v-avatar {
    margin: 0 auto;
  }
  
  .v-chip {
    font-size: 0.65rem;
    padding: 0 6px;
  }
}
</style>