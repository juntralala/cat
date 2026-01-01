<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

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

const iconPreview = ref(null);

// Set preview saat component mount jika ada icon
onMounted(() => {
    if (props.settings?.app_icon) {
        iconPreview.value = props.settings.app_icon;
    }
});

onUnmounted(() => {
    URL.revokeObjectURL(iconPreview); // <- untuk menghindari memori leak
})

const handleFileChange = (event) => {
    const file = event?.target?.files[0];
    if (file == null || file == undefined) {
        console.error("handle file change: file is null or undefined");
    }
    URL.revokeObjectURL(iconPreview);
    form.app_icon = file;
    iconPreview.value = URL.createObjectURL(file);
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
            : `${props.settings.app_icon}`)
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
                        <v-col cols="12" class="mt-2">
                            <label for="icon" class="d-flex align-center justify-center gap-2">
                                <input type="file" id="icon" @change="handleFileChange" accept="image/**" alt="app icon"
                                    class="hidden" />
                                <v-img v-if="iconPreview" :src="iconPreview" min-width="150" min-height="150"
                                    max-width="150" max-height="150" class="rounded-full cursor-pointer" />
                                <v-icon v-else icon="mdi-account-circle" size="175" />
                                <v-btn v-if="form.app_icon" icon="mdi-close" size="small" variant="text"
                                    @click="clearIcon" />
                            </label>
                            <div class="flex flex-col items-center">
                                <p class="font-medium text-lg">Logo Aplikasi</p>
                                <p class="text-xs text-gray-400">Klik untuk mengganti logo</p>
                            </div>
                        </v-col>

                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.app_name" label="Nama Aplikasi" variant="outlined"
                                :error-messages="form.errors.app_name"></v-text-field>
                        </v-col>

                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.company_name" label="Nama Instansi" variant="outlined"
                                :error-messages="form.errors.company_name"></v-text-field>
                        </v-col>

                        <v-col cols="12" md="6">
                            <v-text-field v-model="form.company_phone_number" label="Nomer Telepon" type="tel"
                                variant="outlined" :error-messages="form.errors.company_phone_number"></v-text-field>
                        </v-col>

                        <v-col cols="12" md="6">
                            <v-textarea v-model="form.company_address" label="Alamat" variant="outlined" rows="3"
                                :error-messages="form.errors.company_address"></v-textarea>
                        </v-col>
                    </v-row>

                    <v-btn type="submit" color="primary" class="mt-6" :loading="form.processing"
                        :disabled="form.processing">
                        Simpan
                    </v-btn>
                </form>
            </v-card-text>
        </v-card>
    </v-container>
</template>