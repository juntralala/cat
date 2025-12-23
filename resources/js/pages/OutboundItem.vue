<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref, computed, onBeforeMount, onBeforeUpdate } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineOptions({
    layout: ApplicationLayout
});

const props = defineProps({
    items: Array,
    measurementUnits: Array,
    recipients: Array
});

const successDialog = ref(false);
const errorDialog = ref(false);
const errorMessage = ref('');
const menuDate = ref(false);
let successTimeout = null;

// Watch perubahan recipient_id untuk auto-fill division
function onRecipientChange(recipient) {
    if(!recipient) {
        console.warn("recive recipient null");
        return;
    }
    if(recipient instanceof String || typeof recipient === 'string') {
        console.log("recive recipient string");
        return; 
    }

    if (recipient.division) {
        form.division = recipient.division;
    }
}

const form = useForm({
    type: 'out',
    recipient_id: null,
    division: '',
    transaction_date: new Date(),
    notes: '',
    transaction_details: [
        {
            item_id: null,
            unit_id: null,
            quantity: 1
        }
    ]
});

// Format tanggal ke bahasa Indonesia (tanpa hari)
const formattedDate = computed(() => {
    if (!form.transaction_date) return '';

    const date = form.transaction_date;
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };

    return date.toLocaleDateString('id-ID', options);
});

function addItem() {
    form.transaction_details.push({
        item_id: null,
        unit_id: null,
        quantity: 1
    });
}

function deleteItem(index) {
    if (form.transaction_details.length > 1) {
        form.transaction_details.splice(index, 1);
    }
}

function submitTransaction() {
    form
        .transform(data => {
            // Ubah format tanggal ke YYYY-MM-DD sebelum dikirim
            if (data.transaction_date instanceof Date) {
                const year = data.transaction_date.getFullYear();
                const month = String(data.transaction_date.getMonth() + 1).padStart(2, '0');
                const day = String(data.transaction_date.getDate()).padStart(2, '0');
                data.transaction_date = `${year}-${month}-${day}`;
            }
            return data;
        })
        .post(route('items.outbound'), {
            onSuccess: () => {
                // Tampilkan dialog sukses
                successDialog.value = true;

                // Reset form
                form.reset();
                form.transaction_date = new Date();
                form.transaction_details = [
                    {
                        item_id: null,
                        unit_id: null,
                        quantity: 1
                    }
                ];

                // Auto close dialog setelah 3 detik
                successTimeout = setTimeout(() => {
                    successDialog.value = false;
                }, 3000);
            },
            onError: (errors) => {
                console.error('Validation errors:', errors);

                // Tampilkan dialog error
                errorDialog.value = true;

                // Ambil pesan error pertama atau error umum
                if (errors.error) {
                    errorMessage.value = errors.error;
                } else {
                    const firstError = Object.values(errors)[0];
                    errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
                }
            }
        });
}

function closeSuccessDialog() {
    successDialog.value = false;
    if (successTimeout) {
        clearTimeout(successTimeout);
    }
}

function closeErrorDialog() {
    errorDialog.value = false;
    errorMessage.value = '';
}

function cancel() {
    form.reset();
    window.history.back();
}
onBeforeMount(() => {
    props.recipients.map(
        item => item.name_nickname = item.nickname ? `${item.name} (${item.nickname})` : item.name
    );
});
onBeforeUpdate(() => {
    props.recipients.map(
        item => item.name_nickname = item.nickname ? `${item.name} (${item.nickname})` : item.name
    );
});
</script>

<template>
    <v-container fluid>
        <!-- Success Dialog -->
        <v-dialog v-model="successDialog" max-width="400" persistent>
            <v-card>
                <v-card-text class="text-center pa-8">
                    <v-icon icon="mdi-check-circle" size="64" color="success" class="mb-4"></v-icon>
                    <h2 class="text-h5 font-weight-bold mb-2">Berhasil!</h2>
                    <p class="text-body-1">Transaksi barang keluar berhasil disimpan.</p>
                </v-card-text>
                <v-card-actions class="justify-center pb-6">
                    <v-btn @click="closeSuccessDialog" color="success" variant="flat">
                        Tutup
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Error Dialog -->
        <v-dialog v-model="errorDialog" max-width="400">
            <v-card>
                <v-card-text class="text-center pa-8">
                    <v-icon icon="mdi-alert-circle" size="64" color="error" class="mb-4"></v-icon>
                    <h2 class="text-h5 font-weight-bold mb-2">Gagal!</h2>
                    <p class="text-body-1">{{ errorMessage }}</p>
                </v-card-text>
                <v-card-actions class="justify-center pb-6">
                    <v-btn @click="closeErrorDialog" color="error" variant="flat">
                        Tutup
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-row>
            <v-col>
                <h1 class="text-4xl font-medium">Barang <span class="text-blue-darken-2">Keluar</span></h1>
            </v-col>
        </v-row>

        <v-row>
            <v-col>
                <form @submit.prevent="submitTransaction">
                    <!-- Header Transaction Fields -->
                    <v-row class="pt-4">
                        <v-col cols="12" md="4">
                            <v-combobox v-model="form.recipient_id" density="comfortable" :items="recipients"
                                item-title="name_nickname" item-value="id" label="Penerima"
                                :error-messages="form.errors.recipient_id" @update:model-value="onRecipientChange" />
                        </v-col>
                        <v-col cols="12" md="4">
                            <v-text-field v-model="form.division" density="comfortable" label="Divisi (opsional)"
                                :error-messages="form.errors.division" persistent-hint />
                        </v-col>
                        <v-col cols="12" md="4">
                            <v-menu v-model="menuDate" :close-on-content-click="false" transition="scale-transition"
                                min-width="auto">
                                <template v-slot:activator="{ props: menuProps }">
                                    <v-text-field v-model="formattedDate" density="comfortable"
                                        label="Tanggal Transaksi" readonly v-bind="menuProps"
                                        prepend-inner-icon="mdi-calendar"
                                        :error-messages="form.errors.transaction_date" />
                                </template>
                                <v-date-picker v-model="form.transaction_date" @update:model-value="menuDate = false"
                                    header="Pilih Tanggal" hide-header :max="new Date()" />
                            </v-menu>
                        </v-col>
                    </v-row>

                    <v-row>
                        <v-col cols="12">
                            <v-textarea v-model="form.notes" density="comfortable" label="Catatan (opsional)" rows="2"
                                :error-messages="form.errors.notes" />
                        </v-col>
                    </v-row>

                    <v-divider class="my-6"></v-divider>

                    <!-- Transaction Details -->
                    <v-row v-for="(item, index) in form.transaction_details" :key="index" class="items-start!">
                        <v-col class="py-0!">
                            <v-autocomplete v-model="item.item_id" density="comfortable" :items="items"
                                item-title="name" item-value="id" label="Nama Barang"
                                :error-messages="form.errors[`transaction_details.${index}.item_id`]"></v-autocomplete>
                        </v-col>
                        <v-col class="py-0!" cols="2">
                            <v-autocomplete v-model="item.unit_id" density="comfortable" :items="measurementUnits"
                                item-title="name" item-value="id" label="Satuan"
                                :error-messages="form.errors[`transaction_details.${index}.unit_id`]" />
                        </v-col>
                        <v-col class="py-0!" cols="2">
                            <v-number-input v-model="item.quantity" :min="1" control-variant="split"
                                density="comfortable" label="Jumlah"
                                :error-messages="form.errors[`transaction_details.${index}.quantity`]" />
                        </v-col>
                        <v-col cols="auto" class="mt-1 py-0!">
                            <v-btn @click="deleteItem(index)" variant="tonal" color="blue" icon size="small"
                                :disabled="form.transaction_details.length === 1">
                                <v-icon icon="mdi-minus"></v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>

                    <v-row class="mt-2">
                        <v-col class="flex items-center! justify-end! pt-0">
                            <v-btn @click="addItem" variant="tonal" color="blue-accent-2" :disabled="form.processing">
                                <v-icon icon="mdi-plus" start></v-icon>
                                Tambah Barang
                            </v-btn>
                        </v-col>
                    </v-row>

                    <v-divider class="my-6"></v-divider>

                    <v-row>
                        <v-col class="flex justify-end gap-4">
                            <v-btn @click="cancel" variant="outlined" color="grey" :disabled="form.processing">
                                Batal
                            </v-btn>
                            <v-btn type="submit" variant="flat" color="blue-accent-3" :loading="form.processing"
                                :disabled="form.processing">
                                <v-icon icon="mdi-content-save" start></v-icon>
                                Simpan Transaksi
                            </v-btn>
                        </v-col>
                    </v-row>
                </form>
            </v-col>
        </v-row>
    </v-container>
</template>