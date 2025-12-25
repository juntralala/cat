<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';

defineOptions({
    layout: ApplicationLayout
});

const props = defineProps({
    items: Array,
    baseUnits: Array // hanya di pakai di form add/edit (refactor nanti)
});

const showItemAddDialog = ref(false);
const showItemEditDialog = ref(false);
const itemAddFormRef = ref(null);
const itemEditFormRef = ref(null);
const disableItemAddSubmit = ref(false);
const disableItemEditSubmit = ref(false);

const itemAddForm = useForm({
    name: '',
    base_measurement_unit_id: ''
});

const itemEditForm = useForm({
    id: null,
    name: '',
    base_measurement_unit_id: ''
});

const itemFormRule = {
    name: [
        (value) => !!value || "Harus diisi",
        (value) => value.length >= 2 || "Nama barang harus setidaknya 2 karakter",
        (value) => value.length < 255 || "Nama barang tidak boleh lebih 255 karakter",
    ]
};

const openEditDialog = (item) => {
    itemEditForm.id = item.id;
    itemEditForm.name = item.name;
    itemEditForm.base_measurement_unit_id = item.base_measurement_unit.id;
    itemEditForm.clearErrors();
    showItemEditDialog.value = true;
    itemEditForm.defaults();
};

async function submitItemAddForm() {
    const validated = await itemAddFormRef.value.validate();
    if (validated.valid) {
        disableItemAddSubmit.value = true;
        itemAddForm.post('/items', {
            onSuccess: () => {
                showItemAddDialog.value = false;
                itemAddFormRef.value.reset();
                disableItemAddSubmit.value = false;
                router.reload();
            },
            onError: () => {
                disableItemAddSubmit.value = false;
            }
        });
    }
}

async function submitItemEditForm() {
    const validated = await itemEditFormRef.value.validate();
    if (validated.valid) {
        disableItemEditSubmit.value = true;
        itemEditForm.put(`/items/${itemEditForm.id}`, {
            onSuccess: () => {
                showItemEditDialog.value = false;
                itemEditFormRef.value.reset();
                disableItemEditSubmit.value = false;
                router.reload();
            },
            onError: () => {
                disableItemEditSubmit.value = false;
            },
        });
    }
}

const deleteItem = (id) => {
    itemEditForm.delete(`/items/${id}`, {
        onSuccess: () => {
            router.reload();
        }
    });
};
</script>

<template>
    <Head title="Barang"></Head>
    <v-container>
        <v-row>
            <v-col>
                <PageTitleHighlightPart first-part-title="Kelola" second-part-title="Barang"/>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-btn variant="tonal" color="blue-darken-2">
                    <span>
                        <v-icon icon="mdi-plus" />
                        Tambah barang
                    </span>
                    <v-dialog activator="parent" max-width="800" v-slot="{ isActive }" v-model="showItemAddDialog">
                        <v-card>
                            <v-card-title class="text-center">Tambah Barang</v-card-title>
                            <v-divider />
                            <v-card-text>
                                <v-form ref="itemAddFormRef" @submit.prevent="submitItemAddForm">
                                    <v-text-field 
                                        v-model="itemAddForm.name" 
                                        label="Nama Barang" 
                                        density="comfortable"
                                        :rules="itemFormRule.name"
                                        :error-messages="itemAddForm.errors.name" />
                                    <v-autocomplete 
                                        v-model="itemAddForm.base_measurement_unit_id"
                                        :error-messages="itemAddForm.errors.base_measurement_unit_id"
                                        label="Satuan dasar"
                                        :items="baseUnits"
                                        item-title="name"
                                        item-value="id"/>
                                </v-form>
                            </v-card-text>
                            <v-card-actions>
                                <v-btn @click="isActive.value = false">Cancel</v-btn>
                                <v-btn color="blue-darken-4" :disabled="disableItemAddSubmit"
                                    @click="submitItemAddForm">
                                    <span v-if="!disableItemAddSubmit">Submit</span>
                                    <span v-else>
                                        <v-progress-circular indeterminate size="20" />
                                    </span>
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </v-btn>
            </v-col>
        </v-row>

        <!-- Edit Item Dialog -->
        <v-dialog max-width="800" v-model="showItemEditDialog">
            <v-card>
                <v-card-title class="text-center">Sunting Barang</v-card-title>
                <v-divider />
                <v-card-text>
                    <v-form ref="itemEditFormRef">
                        <v-text-field 
                        v-model="itemEditForm.name" 
                        label="Nama Barang" 
                        density="comfortable"
                        :rules="itemFormRule.name"
                        :error-messages="itemEditForm.errors.name" />
                        <v-autocomplete
                        v-model="itemEditForm.base_measurement_unit_id"
                        :error-messages="itemAddForm.errors.base_measurement_unit_id"
                        label="Satuan dasar"
                        :items="baseUnits"
                        item-title="name"
                        item-value="id"/>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-btn @click="showItemEditDialog = false">Cancel</v-btn>
                    <v-btn color="blue-darken-4"
                    :disabled="disableItemEditSubmit || (!itemEditForm.isDirty)"
                    @click="submitItemEditForm">
                    <span v-if="!disableItemEditSubmit">Update</span>
                        <span v-else>
                            <v-progress-circular indeterminate size="20" />
                        </span>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        
        <!-- Desktop -->
        <v-row class="hidden! md:block!">
            <v-col>
                <v-data-table 
                    :headers="[
                        { title: 'No', key: 'no', width: '6%' },
                        { title: 'Nama Barang', key: 'name' },
                        { title: 'Satuan Dasar', key: 'baseMeasurementUnit' },
                        { title: 'Tindakan', key: 'more', width: '10%' }
                    ]" 
                    :items="items.map((item, index) => ({ 
                        no: index + 1, 
                        name: item.name, 
                        baseMeasurementUnit: item?.base_measurement_unit.name, 
                        more: item.id,
                        fullData: item
                    }))"
                    class="hidden! md:block!">
                    <template #headers="{ headers }">
                        <tr class="bg-blue-darken-2">
                            <th v-for="i in (headers.at(0).length)" :key="i">{{ headers.at(0).at(i - 1).title }}</th>
                        </tr>
                    </template>
                    <template #item.more="{ item }">
                        <v-btn variant="text" icon>
                            <v-icon icon="mdi-dots-vertical" />
                                <v-menu activator="parent">
                                    <v-list density="compact">
                                        <v-list-item 
                                            value="edit" 
                                            @click="openEditDialog(item.fullData)">
                                            <v-icon icon="mdi-pencil" class="mr-2" />
                                            Sunting
                                        </v-list-item>
                                        <v-list-item value="delete">
                                            <v-icon icon="mdi-delete" class="mr-2" />
                                            Hapus
                                            <v-dialog activator="parent" max-width="400" v-slot="{ isActive }">
                                                <v-card>
                                                    <v-card-title
                                                        class="text-wrap text-center bg-blue-darken-2">Konfirmasi!</v-card-title>
                                                    <v-card-text>
                                                        <div>Apakah yakin untuk menghapus barang dengan nama <span
                                                                class="text-blue-600">{{ item.name }}</span></div>
                                                    </v-card-text>
                                                    <v-card-actions>
                                                        <v-btn @click="deleteItem(item.more); isActive.value = false">Ya</v-btn>
                                                        <v-btn @click="isActive.value = false">Batal</v-btn>
                                                    </v-card-actions>
                                                </v-card>
                                            </v-dialog>
                                        </v-list-item>
                                    </v-list>
                                </v-menu>
                        </v-btn>
                    </template>
                </v-data-table>
            </v-col>
        </v-row>
        
        <!-- Mobile -->
        <v-row class="md:hidden!">
            <v-col>
                <v-row v-for="(item, index) in items" :key="item.id">
                    <v-col>
                        <v-card>
                            <v-card-actions class="flex justify-end bg-blue-darken-2">
                                <v-btn variant="text" icon>
                                    <v-icon icon="mdi-dots-vertical" />
                                    <v-menu activator="parent">
                                        <v-list density="compact">
                                            <v-list-item 
                                                value="edit" 
                                                @click="openEditDialog(item)">
                                                <v-icon icon="mdi-pencil" class="mr-2" />
                                                Sunting
                                            </v-list-item>
                                            <v-list-item value="delete">
                                                <v-icon icon="mdi-delete" class="mr-2" />
                                                Hapus
                                                <v-dialog activator="parent" max-width="400" v-slot="{ isActive }">
                                                    <v-card>
                                                        <v-card-title
                                                            class="text-wrap text-center bg-blue-darken-2">Konfirmasi!</v-card-title>
                                                        <v-card-text>
                                                            <div>Apakah yakin untuk menghapus barang dengan nama <span
                                                                    class="text-blue-600">{{ item.name }}</span></div>
                                                        </v-card-text>
                                                        <v-card-actions>
                                                            <v-btn @click="deleteItem(item.id); isActive.value = false">Ya</v-btn>
                                                            <v-btn @click="isActive.value = false">Batal</v-btn>
                                                        </v-card-actions>
                                                    </v-card>
                                                </v-dialog>
                                            </v-list-item>
                                        </v-list>
                                    </v-menu>
                                </v-btn>
                            </v-card-actions>
                            <v-card-text>
                                <v-row>
                                    <v-col cols="5">No</v-col>
                                    <v-col>{{ index + 1 }}</v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="5">Nama Barang</v-col>
                                    <v-col>{{ item.name }}</v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
                
                <v-row v-if="!items || items.length === 0">
                    <v-col>
                        <v-card>
                            <v-card-text class="text-center text-grey">
                                Tidak ada barang yang tersedia
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </v-container>
</template>