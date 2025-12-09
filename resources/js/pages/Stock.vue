<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: ApplicationLayout
});

const props = defineProps({
    stocks: {
        type: Array,
        default: () => []
    },
    items: {
        type: Array,
        default: () => []
    },
    units: {
        type: Array,
        default: () => []
    }
});

const showStockAddDialog = ref(false);
const showStockEditDialog = ref(false);
const stockAddFormRef = ref(null);
const stockEditFormRef = ref(null);
const disableStockAddSubmit = ref(false);
const disableStockEditSubmit = ref(false);

const stockAddForm = useForm({
    item_id: null,
    unit_id: null,
    quantity: null
});

const stockEditForm = useForm({
    id: null,
    item_id: null,
    unit_id: null,
    quantity: null
});

const stockFormRule = {
    item_id: [
        (value) => !!value || "Barang harus dipilih"
    ],
    unit_id: [
        (value) => !!value || "Unit ukuran harus dipilih"
    ],
    quantity: [
        (value) => !!value || "Jumlah harus diisi",
        (value) => value >= 0 || "Jumlah harus setidaknya 0",
        (value) => Number.isInteger(Number(value)) || "Jumlah harus berupa angka bulat"
    ]
};

const openEditDialog = (stock) => {
    stockEditForm.id = stock.id;
    stockEditForm.item_id = stock.item_id;
    stockEditForm.unit_id = stock.unit_id;
    stockEditForm.quantity = stock.quantity;
    stockEditForm.clearErrors();
    showStockEditDialog.value = true;
};

async function submitStockAddForm() {
    const validated = await stockAddFormRef.value.validate();
    if (validated.valid) {
        disableStockAddSubmit.value = true;
        stockAddForm.post('/items/stocks', {
            onSuccess: () => {
                showStockAddDialog.value = false;
                stockAddFormRef.value.reset();
                disableStockAddSubmit.value = false;
                router.reload();
            },
            onError: () => {
                disableStockAddSubmit.value = false;
            }
        });
    }
}

async function submitStockEditForm() {
    const validated = await stockEditFormRef.value.validate();
    if (validated.valid) {
        disableStockEditSubmit.value = true;
        stockEditForm.put(`/items/stocks/${stockEditForm.id}`, {
            onSuccess: () => {
                showStockEditDialog.value = false;
                stockEditFormRef.value.reset();
                disableStockEditSubmit.value = false;
                router.reload();
            },
            onError: () => {
                disableStockEditSubmit.value = false;
            }
        });
    }
}

const deleteStock = (id) => {
    stockEditForm.delete(`/items/stocks/${id}`, {
        onSuccess: () => {
            router.reload();
        }
    });
};

const getItemName = (itemId) => {
    const item = props.items.find(i => i.id === itemId);
    return item ? item.name : '-';
};

const getUnitName = (unitId) => {
    const unit = props.units.find(u => u.id === unitId);
    return unit ? unit.name : '-';
};
</script>

<template>
    <Head title="Stok"></Head>
    <v-container>
        <v-row>
            <v-col>
                <h1 class="text-4xl font-medium">Kelola <span class="text-blue-accent-3">Stok</span></h1>
            </v-col>
        </v-row>
        <v-row>
            <v-col>
                <v-btn variant="tonal" color="blue-darken-2">
                    <span>
                        <v-icon icon="mdi-plus" />
                        Tambah stok
                    </span>
                    <v-dialog activator="parent" max-width="800" v-slot="{ isActive }" v-model="showStockAddDialog">
                        <v-card>
                            <v-card-title class="text-center">Tambah Stok</v-card-title>
                            <v-divider />
                            <v-card-text>
                                <v-form ref="stockAddFormRef" @submit.prevent="submitStockAddForm">
                                    <v-select
                                        v-model="stockAddForm.item_id"
                                        :items="items"
                                        item-title="name"
                                        item-value="id"
                                        label="Barang"
                                        density="comfortable"
                                        :rules="stockFormRule.item_id"
                                        :error-messages="stockAddForm.errors.item_id"
                                    />
                                    <v-select
                                        v-model="stockAddForm.unit_id"
                                        :items="units"
                                        item-title="name"
                                        item-value="id"
                                        label="Unit Ukuran"
                                        density="comfortable"
                                        :rules="stockFormRule.unit_id"
                                        :error-messages="stockAddForm.errors.unit_id"
                                    />
                                    <v-text-field
                                        v-model="stockAddForm.quantity"
                                        label="Jumlah"
                                        type="number"
                                        density="comfortable"
                                        :rules="stockFormRule.quantity"
                                        :error-messages="stockAddForm.errors.quantity"
                                    />
                                </v-form>
                            </v-card-text>
                            <v-card-actions>
                                <v-btn @click="isActive.value = false">Cancel</v-btn>
                                <v-btn color="blue-darken-4" :disabled="disableStockAddSubmit"
                                    @click="submitStockAddForm">
                                    <span v-if="!disableStockAddSubmit">Submit</span>
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
        
        <!-- Edit Stock Dialog -->
        <v-dialog max-width="800" v-model="showStockEditDialog">
            <v-card>
                <v-card-title class="text-center">Sunting Stok</v-card-title>
                <v-divider />
                <v-card-text>
                    <v-form ref="stockEditFormRef">
                        <v-select
                            v-model="stockEditForm.item_id"
                            :items="items"
                            item-title="name"
                            item-value="id"
                            label="Barang"
                            density="comfortable"
                            :rules="stockFormRule.item_id"
                            :error-messages="stockEditForm.errors.item_id"
                        />
                        <v-select
                            v-model="stockEditForm.unit_id"
                            :items="units"
                            item-title="name"
                            item-value="id"
                            label="Unit Ukuran"
                            density="comfortable"
                            :rules="stockFormRule.unit_id"
                            :error-messages="stockEditForm.errors.unit_id"
                        />
                        <v-text-field
                            v-model="stockEditForm.quantity"
                            label="Jumlah"
                            type="number"
                            density="comfortable"
                            :rules="stockFormRule.quantity"
                            :error-messages="stockEditForm.errors.quantity"
                        />
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-btn @click="showStockEditDialog = false">Cancel</v-btn>
                    <v-btn color="blue-darken-4" :disabled="disableStockEditSubmit"
                        @click="submitStockEditForm">
                        <span v-if="!disableStockEditSubmit">Update</span>
                        <span v-else>
                            <v-progress-circular indeterminate size="20" />
                        </span>
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        
        <!-- Desktop Table View -->
        <v-row class="hidden! md:block!">
            <v-col>
                <v-data-table 
                    :headers="[
                        { title: 'No', key: 'no', width: '80px' },
                        { title: 'Barang', key: 'item_name' },
                        { title: 'Jumlah', key: 'quantity', width: '150px' },
                        { title: 'Unit', key: 'unit_name', width: '150px' },
                        { title: 'More', key: 'more', width: '100px', align: 'center' }
                    ]" 
                    :items="stocks.map((stock, index) => ({ 
                        no: index + 1, 
                        item_name: getItemName(stock.item_id),
                        quantity: stock.quantity,
                        unit_name: getUnitName(stock.unit_id),
                        more: stock.id,
                        fullData: stock 
                    }))"
                    class="hidden! md:block!">
                    <template #headers="{ headers }">
                        <tr class="bg-blue-darken-2">
                            <th v-for="i in (headers.at(0).length)" :key="i" 
                                :style="headers.at(0).at(i - 1).width ? `width: ${headers.at(0).at(i - 1).width}` : ''"
                                :class="headers.at(0).at(i - 1).align === 'center' ? 'text-center' : ''">
                                {{ headers.at(0).at(i - 1).title }}
                            </th>
                        </tr>
                    </template>
                    <template #item.more="{ item }">
                        <div class="text-center">
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
                                                        <div>Apakah yakin untuk menghapus stok <span
                                                                class="text-blue-600">{{ item.item_name }}</span> dengan jumlah <span
                                                                class="text-blue-600">{{ item.quantity }} {{ item.unit_name }}</span>?</div>
                                                    </v-card-text>
                                                    <v-card-actions>
                                                        <v-btn @click="deleteStock(item.more); isActive.value = false">Ya</v-btn>
                                                        <v-btn @click="isActive.value = false">Batal</v-btn>
                                                    </v-card-actions>
                                                </v-card>
                                            </v-dialog>
                                        </v-list-item>
                                    </v-list>
                                </v-menu>
                            </v-btn>
                        </div>
                    </template>
                </v-data-table>
            </v-col>
        </v-row>
        
        <!-- Mobile Card View -->
        <v-row class="md:hidden!">
            <v-col>
                <v-row v-for="(stock, index) in stocks" :key="stock.id">
                    <v-col>
                        <v-card>
                            <v-card-actions class="flex justify-end bg-blue-darken-2">
                                <v-btn variant="text" icon>
                                    <v-icon icon="mdi-dots-vertical" />
                                    <v-menu activator="parent">
                                        <v-list density="compact">
                                            <v-list-item 
                                                value="edit" 
                                                @click="openEditDialog(stock)">
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
                                                            <div>Apakah yakin untuk menghapus stok <span
                                                                    class="text-blue-600">{{ getItemName(stock.item_id) }}</span> dengan jumlah <span
                                                                    class="text-blue-600">{{ stock.quantity }} {{ getUnitName(stock.unit_id) }}</span>?</div>
                                                        </v-card-text>
                                                        <v-card-actions>
                                                            <v-btn @click="deleteStock(stock.id); isActive.value = false">Ya</v-btn>
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
                                    <v-col cols="5">Barang</v-col>
                                    <v-col>{{ getItemName(stock.item_id) }}</v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="5">Jumlah</v-col>
                                    <v-col>{{ stock.quantity }}</v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="5">Unit</v-col>
                                    <v-col>{{ getUnitName(stock.unit_id) }}</v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
                
                <v-row v-if="!stocks || stocks.length === 0">
                    <v-col>
                        <v-card>
                            <v-card-text class="text-center text-grey">
                                Belum ada stok yang ditambahkan
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>
    </v-container>
</template>