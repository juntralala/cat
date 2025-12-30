<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';

defineOptions({
    layout: ApplicationLayout
});

const props = defineProps({
    units: {
        type: Array,
        default: []
    }
});
const baseUnits = props.units.filter(unit => unit.is_base == true);

const dialog = ref(false);
const editingId = ref(null);
const form = useForm({
    name: '',
    is_base: true,
    base_measurement_unit_id: '',
    conversion: 1,
});
const errorDialog = ref(false);
const errorMessage = ref('');

const openAddDialog = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    dialog.value = true;
};

const openEditDialog = (unit) => {
    editingId.value = unit.id;
    form.name = unit.name;
    switch (true) {
        case unit.is_base == true: form.is_base = true; break;
        case unit.is_base == false && unit.base_measurement_unit_id != null: form.is_base = 'derived'; break;
        case unit.is_base == false && unit.base_measurement_unit_id == null: form.is_base = 'derived_per_sku'; break;
        default: "unpredicted";
    };
    form.base_measurement_unit_id = unit.base_measurement_unit_id;
    form.conversion = unit.conversion;
    form.clearErrors();
    dialog.value = true;
};

const submitForm = () => {
    if (editingId.value) {
        form
            .transform(data => {
                return {
                    ...data,
                    is_base: data.is_base == true,
                    base_measurement_unit_id: (data.is_base != 'derived') ? null : data.base_measurement_unit_id,
                    conversion: (data.is_base != 'derived') ? null : data.conversion,
                }
            })
            .put(`/items/units/${editingId.value}`, {
                onSuccess: () => {
                    dialog.value = false;
                    form.reset();
                    router.reload();
                }
            });
    } else {
        form.transform(data => {
            return {
                ...data,
                is_base: data.is_base == true,
                base_measurement_unit_id: (data.is_base != 'derived') ? null : data.base_measurement_unit_id,
                conversion: (data.is_base != 'derived') ? null : data.conversion,
            }
        }).post('/items/units', {
            onSuccess: () => {
                dialog.value = false;
                form.reset();
                router.reload();
            },
            onError: (errors) => {
                errorMessage.value = 'Terjadi kesalahan saat menyimpan unit ukuran.';
                errorDialog.value = true;

                timeout(() => {
                    errorDialog.value = false;
                    errorMessage.value = '';
                }, 3000);
            }
        }
        );
    }
};

const deleteUnit = (id) => {
    form.delete(`/items/units/${id}`, {
        onSuccess: () => {
            router.reload();
        }
    });
};

const closeDialog = () => {
    dialog.value = false;
    form.reset();
    form.clearErrors();
};

const closeErrorDialog = () => {
    errorDialog.value = false;
    errorMessage.value = '';
};
</script>

<template>
    <v-container>
        <v-dialog v-model="errorDialog" max-width="400">
            <v-card>
                <v-card-text class="text-center pa-8">
                    <v-icon icon="mdi-alert-circle" size="64" color="error" class="mb-4"></v-icon>
                    <h2 class="text-h5 font-weight-bold mb-2">Gagal!</h2>
                    <p class="text-body-1">{{ errorMessage }}</p>
                </v-card-text>
                <v-card-actions class="justify-center pb-6">
                    <v-btn color="error" variant="flat" @click="closeErrorDialog">
                        Tutup
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-row>
            <v-col>
                <PageTitleHighlightPart first-part-title="Data" second-part-title="Unit Satuan" />
            </v-col>
        </v-row>

        <v-row>
            <v-col>
                <v-btn variant="tonal" color="primary" prepend-icon="mdi-plus" @click="openAddDialog">
                    Tambah Unit Ukuran
                </v-btn>
            </v-col>
        </v-row>

        <v-row>
            <v-col cols="12">

                <v-table class="borderless-table">
                    <thead class="bg-blue-darken-2">
                        <tr>
                            <th class="text-left w-1/12">No</th>
                            <th class="text-left">Nama Unit</th>
                            <th class="text-left w-1/12">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(unit, index) in units" :key="unit.id">
                            <td>{{ index + 1 }}</td>
                            <td>{{ unit.name }}</td>
                            <td>
                                <v-btn size="small" icon="mdi-dots-vertical" variant="text"></v-btn>
                                <v-menu activator="parent">
                                    <v-list density="compact">
                                        <v-list-item value="edit" @click="openEditDialog(unit)">
                                            <v-icon icon="mdi-pencil" class="mr-2" />
                                            Edit
                                        </v-list-item>
                                        <v-list-item value="delete">
                                            <v-icon icon="mdi-delete" class="mr-2" />
                                            Hapus
                                            <v-dialog v-slot="{ isActive }" activator="parent" max-width="400">
                                                <v-card>
                                                    <v-card-title class="text-center">Konfirmasi!</v-card-title>
                                                    <v-card-text>
                                                        Apakah Anda yakin ingin menghapus <span
                                                            class="font-weight-bold">{{ unit.name }}</span>?
                                                    </v-card-text>
                                                    <v-card-actions>
                                                        <v-spacer></v-spacer>
                                                        <v-btn @click="isActive.value = false">Batal</v-btn>
                                                        <v-btn color="error"
                                                            @click="deleteUnit(unit.id); isActive.value = false">Hapus</v-btn>
                                                    </v-card-actions>
                                                </v-card>
                                            </v-dialog>
                                        </v-list-item>
                                    </v-list>
                                </v-menu>
                            </td>
                        </tr>
                        <tr v-if="!units || units.length === 0">
                            <td colspan="3" class="text-center text-grey">
                                Belum ada unit ukuran yang ditambahkan
                            </td>
                        </tr>
                    </tbody>
                </v-table>
            </v-col>
        </v-row>

        <!-- Add/Edit Unit Dialog Form -->
        <v-dialog v-model="dialog" max-width="600px" persistent>
            <v-card>
                <v-card-title>
                    <span class="text-h5">{{ editingId ? 'Edit Unit Ukuran' : 'Tambah Unit Ukuran Baru' }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container>
                        <v-form @submit.prevent="submitForm">
                            <v-row>
                                <v-col cols="12">
                                    <v-text-field v-model="form.name" label="Nama Unit"
                                        :error-messages="form.errors.name" placeholder="e.g., pcs, kg, liter, box"
                                        required variant="outlined"></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col>
                                    <v-btn-toggle v-model="form.is_base" variant="tonal" density="comfortable"
                                        color="blue" divided mandatory>
                                        <v-btn :value="true">Dasar</v-btn>
                                        <v-btn value="derived">Turunan</v-btn>
                                        <v-btn value="derived_per_sku">Turunan Per SKU</v-btn>
                                    </v-btn-toggle>
                                </v-col>
                            </v-row>
                            <v-row v-if="form.is_base == 'derived'">
                                <v-col>
                                    <v-autocomplete v-model="form.base_measurement_unit_id" label="Satuan dasar"
                                        :items="baseUnits" item-title="name" item-value="id"
                                        :error-messages="form.errors.base_measurement_unit_id" />
                                    <v-number-input v-model="form.conversion" label="Konversi ke satuan dasar" min="1"
                                        control-variant="hidden" :error-messages="form.errors.conversion" />
                                </v-col>
                            </v-row>
                        </v-form>
                    </v-container>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="grey-darken-1" variant="text" :disabled="form.processing" @click="closeDialog">
                        Batal
                    </v-btn>
                    <v-btn color="primary" variant="tonal" :loading="form.processing" @click="submitForm">
                        {{ editingId ? 'Perbarui' : 'Simpan' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>