<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineOptions({
    layout: ApplicationLayout
});

const props = defineProps({
    settings: Object
});

// Inisialisasi form dengan data dari props
const form = useForm({
    app_name: props.settings?.app_name || '',
    app_icon: null,
    company_name: props.settings?.company_name || '',
    company_address: props.settings?.company_address || '',
    company_phone_number: props.settings?.company_phone_number || '',
});

// Ref untuk preview icon dan file input
const iconPreview = ref(null);
const fileInputRef = ref(null);

// Set preview saat component mount jika ada icon
onMounted(() => {
    if (props.settings?.app_icon) {
        iconPreview.value = props.settings.app_icon;
    }
});

const handleFileChange = (event) => {
    const files = event.target?.files || event;
    
    if (files && files.length > 0) {
        const file = files[0];
        form.app_icon = file;
        
        // Buat preview
        const reader = new FileReader();
        reader.onload = (e) => {
            iconPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('settings.update'), {
        forceFormData: true, 
        preserveScroll: true,
        onSuccess: () => {
            console.log('Settings updated successfully');
        },
        onError: (errors) => {
            console.error('Error updating settings:', errors);
        }
    });
};

// Reset file input
const clearIcon = () => {
    form.app_icon = null;
    iconPreview.value = props.settings?.app_icon 
        ? (props.settings.app_icon.startsWith('http') 
            ? props.settings.app_icon 
            : `/storage/${props.settings.app_icon}`)
        : null;
};
</script>

<template>
    <v-container>
        <v-card class="pa-6">
            <v-card-title>Pengaturan</v-card-title>
            <v-card-text>
                <form @submit.prevent="submit">
                    <v-row>
                        <v-col cols="12" md="6">
                            <v-text-field 
                                v-model="form.app_name"
                                label="Nama Aplikasi" 
                                variant="outlined"
                                :error-messages="form.errors.app_name"
                            ></v-text-field>
                        </v-col>
                        
                        <v-col cols="12" md="6">
                            <v-text-field 
                                v-model="form.company_name"
                                label="Nama Instansi" 
                                variant="outlined"
                                :error-messages="form.errors.company_name"
                            ></v-text-field>
                        </v-col>
                        
                        <v-col cols="12" md="6">
                            <v-text-field 
                                v-model="form.company_phone_number"
                                label="Nomer Telepon" 
                                type="tel" 
                                variant="outlined"
                                :error-messages="form.errors.company_phone_number"
                            ></v-text-field>
                        </v-col>
                        
                        <v-col cols="12" md="6">
                            <v-file-input 
                                ref="fileInputRef"
                                label="Icon" 
                                accept="image/*" 
                                variant="outlined" 
                                prepend-icon="" 
                                prepend-inner-icon="mdi-image"
                                :error-messages="form.errors.app_icon"
                                @change="handleFileChange"
                            ></v-file-input>
                            
                            <!-- Preview Icon -->
                            <div v-if="iconPreview" class="mt-2 d-flex align-center gap-2">
                                <v-img 
                                    :src="iconPreview" 
                                    max-width="100" 
                                    max-height="100"
                                    class="rounded border"
                                ></v-img>
                                <v-btn
                                    v-if="form.app_icon"
                                    icon="mdi-close"
                                    size="small"
                                    variant="text"
                                    @click="clearIcon"
                                ></v-btn>
                            </div>
                        </v-col>
                        
                        <v-col cols="12" md="6">
                            <v-textarea 
                                v-model="form.company_address"
                                label="Alamat" 
                                variant="outlined" 
                                rows="3"
                                :error-messages="form.errors.company_address"
                            ></v-textarea>
                        </v-col>
                    </v-row>
                    
                    <v-btn 
                        type="submit"
                        color="primary" 
                        class="mt-6"
                        :loading="form.processing"
                        :disabled="form.processing"
                    >
                        Simpan
                    </v-btn>
                </form>
            </v-card-text>
        </v-card>
    </v-container>
</template>