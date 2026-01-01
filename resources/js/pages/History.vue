<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref, computed, watch, watchEffect } from 'vue';
import { router } from '@inertiajs/vue3';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';

defineOptions({
    layout: ApplicationLayout
});

const props = defineProps({
    transactions: {
        type: Object,
        default: []
    }
});

const search = ref('');
const filterType = ref('all');
const startDate = ref('');
const endDate = ref('');
const menuStartDate = ref(false);
const menuEndDate = ref(false);
const detailDialog = ref(false);
const selectedTransaction = ref(null);
const currentPage = ref(props.transactions.current_page);
const isExporting = ref(false);

const headers = [
    { title: 'Tanggal', key: 'transaction_date', sortable: false },
    { title: 'Tipe', key: 'type', sortable: false },
    { title: 'Supplier/Penerima', key: 'party', sortable: false },
    { title: 'Divisi', key: 'division', sortable: false },
    { title: 'Total Jenis Barang', key: 'total_items', sortable: false },
    { title: 'Catatan', key: 'notes', sortable: false },
    { title: 'Aksi', key: 'actions', sortable: false, align: 'center' }
];

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return date.toLocaleDateString('id-ID', options);
}

const formattedStartDate = computed(() => {
    if (!startDate.value) return '';
    const date = startDate.value instanceof Date ? startDate.value : new Date(startDate.value);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
});

const formattedEndDate = computed(() => {
    if (!endDate.value) return '';
    const date = endDate.value instanceof Date ? endDate.value : new Date(endDate.value);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('id-ID', options);
});

// Get supplier atau recipient name
function getPartyName(transaction) {
    if (transaction.type === 'in') {
        return transaction.supplier || '-';
    } else {
        return transaction.recipient?.name || '-';
    }
}

// Convert Date to YYYY-MM-DD format
function dateToString(date) {
    if (!date) return '';
    if (typeof date === 'string') return date;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// filter transaction
let timer;
watchEffect(() => {
    clearTimeout(timer);
    const params = {};

    if (filterType.value !== 'all') {
        params.type = filterType.value;
    }
    if (search.value) {
        params.search = search.value;
    }
    if (startDate.value) {
        params.start_date = dateToString(startDate.value);
    }
    if (endDate.value) {
        params.end_date = dateToString(endDate.value);
    }
    if (currentPage.value) {
        params.page = currentPage.value;
    }

    setTimeout(() => {
        router.get(route('items.transactions.history'), params, {
            preserveState: true,
            preserveScroll: true,
            only: ['transactions']
        });
    }, 300);
});

// Clear filters
function clearFilters() {
    search.value = '';
    filterType.value = 'all';
    startDate.value = '';
    endDate.value = '';
    currentPage.value = 1;
}

// Show detail dialog
function showDetail(transaction) {
    selectedTransaction.value = transaction;
    detailDialog.value = true;
}

// Close detail dialog
function closeDetail() {
    detailDialog.value = false;
    selectedTransaction.value = null;
}

// Export to spreadhseet
function exportToXlsx() {
    isExporting.value = true;
    let timeoutId = setTimeout(() => {
        isExporting.value = false;
    }, 10_000);

    const params = {};

    if (filterType.value !== 'all') {
        params.type = filterType.value;
    }
    if (search.value) {
        params.search = search.value;
    }
    if (startDate.value && endDate.value) {
        params.start_date = dateToString(startDate.value);
        params.end_date = dateToString(endDate.value);
    }

    // Create download link to export endpoint
    const queryString = new URLSearchParams(params).toString();
    const url = `${route('items.transactions.history.export.xlsx')}?${queryString}`;

    // Download file
    window.location.href = url;
    isExporting.value = null;
    clearTimeout(timeoutId);
}
</script>

<template>
    <v-container fluid>
        <!-- Detail Dialog -->
        <v-dialog v-model="detailDialog" max-width="800">
            <v-card v-if="selectedTransaction">
                <v-card-title class="d-flex align-center pa-6 bg-grey-lighten-4">
                    <v-icon :icon="selectedTransaction.type === 'in' ? 'mdi-arrow-down-bold' : 'mdi-arrow-up-bold'"
                        :color="selectedTransaction.type === 'in' ? 'blue-accent-3' : 'red-accent-3'" size="32"
                        class="mr-3"></v-icon>
                    <div>
                        <h2 class="text-h5 font-weight-bold">
                            Detail Transaksi {{ selectedTransaction.type === 'in' ? 'Masuk' : 'Keluar' }}
                        </h2>
                        <p class="text-body-2 text-grey-darken-1 mb-0">{{
                            formatDate(selectedTransaction.transaction_date) }}</p>
                    </div>
                </v-card-title>

                <v-card-text class="pa-6">
                    <!-- Info Header -->
                    <v-row>
                        <v-col cols="12" md="6">
                            <div class="mb-4">
                                <p class="text-caption text-grey-darken-1 mb-1">
                                    {{ "Petugas" }}
                                </p>
                                <p class="text-body-1 font-weight-medium">
                                    {{ selectedTransaction.user.name }}
                                </p>
                            </div>
                        </v-col>
                        <v-col cols="12" md="6">
                            <div class="mb-4">
                                <p class="text-caption text-grey-darken-1 mb-1">
                                    {{ selectedTransaction.type === 'in' ? 'Supplier' : 'Penerima' }}
                                </p>
                                <p class="text-body-1 font-weight-medium">
                                    {{ getPartyName(selectedTransaction) }}
                                </p>
                            </div>
                        </v-col>
                    </v-row>

                    <v-row v-if="selectedTransaction.notes">
                        <v-col cols="12">
                            <div class="mb-4">
                                <p class="text-caption text-grey-darken-1 mb-1">Catatan</p>
                                <p class="text-body-1">{{ selectedTransaction.notes }}</p>
                            </div>
                        </v-col>
                    </v-row>

                    <v-divider class="my-4"></v-divider>

                    <!-- Detail Items -->
                    <h3 class="text-h6 font-weight-bold mb-4">Daftar Barang</h3>
                    <v-table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>SKU</th>
                                <th>Satuan</th>
                                <th>Harga Satuan</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(items, index) in selectedTransaction.transaction_items" :key="items.id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ items.sku?.item?.name }}</td>
                                <td>{{ items.sku?.sku }}</td>
                                <td>{{ items.unit?.name }}</td>
                                <td>{{ items.price }}</td>
                                <td class="text-right font-weight-bold">{{ items.quantity }}</td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card-text>

                <v-card-actions class="pa-6 pt-0">
                    <v-spacer></v-spacer>
                    <v-btn color="grey" variant="flat" @click="closeDetail">
                        Tutup
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- Header -->
        <v-list class="pt-0!">
            <v-list-item>
                <v-row align="center">
                    <v-col class="pt-2">
                        <PageTitleHighlightPart first-part-title="Riwayat" second-part-title="Transaksi" />
                    </v-col>
                    <v-col cols="auto">
                        <v-tooltip>
                            <template #activator="{ props }">
                                <v-btn v-bind="props" color="blue-accent-3" variant="tonal" :loading="isExporting"
                                    :disabled="!transactions.data || transactions.data.length === 0"
                                    @click="exportToXlsx">
                                    <v-icon icon="mdi-download" start></v-icon>
                                    SpreadSheet
                                </v-btn>
                            </template>
                            <span>Unduh data berdasarkan filter yang aktif saat ini</span>
                        </v-tooltip>
                    </v-col>
                </v-row>
            </v-list-item>

            <!-- Filters -->
            <v-list-item class="mt-6">
                <v-row>
                    <!-- Search -->
                    <v-col cols="12" md="6">
                        <v-text-field v-model="search" density="comfortable"
                            placeholder="Cari berdasarkan supplier, penerima, divisi..."
                            prepend-inner-icon="mdi-magnify" variant="outlined" clearable />
                    </v-col>

                    <!-- Type Filter -->
                    <v-col cols="12" md="6">
                        <v-select v-model="filterType" density="comfortable" :items="[
                            { title: 'Semua Transaksi', value: 'all' },
                            { title: 'Barang Masuk', value: 'in' },
                            { title: 'Barang Keluar', value: 'out' }
                        ]" variant="outlined" prepend-inner-icon="mdi-filter" />
                    </v-col>

                    <!-- Start Date -->
                    <v-col cols="12" md="5">
                        <v-menu v-model="menuStartDate" :close-on-content-click="false" transition="scale-transition"
                            min-width="auto">
                            <template #activator="{ props: menuProps }">
                                <v-text-field v-model="formattedStartDate" density="comfortable" label="Tanggal Mulai"
                                    readonly v-bind="menuProps" prepend-inner-icon="mdi-calendar" variant="outlined"
                                    clearable @click:clear="startDate = ''" />
                            </template>
                            <v-date-picker v-model="startDate" locale="id" hide-header header="Pilih Tanggal Mulai"
                                @update:model-value="menuStartDate = false" />
                        </v-menu>
                    </v-col>

                    <!-- End Date -->
                    <v-col cols="12" md="5">
                        <v-menu v-model="menuEndDate" :close-on-content-click="false" transition="scale-transition"
                            min-width="auto">
                            <template #activator="{ props: menuProps }">
                                <v-text-field v-model="formattedEndDate" density="comfortable" label="Tanggal Akhir"
                                    readonly v-bind="menuProps" prepend-inner-icon="mdi-calendar" variant="outlined"
                                    clearable @click:clear="endDate = ''" />
                            </template>
                            <v-date-picker v-model="endDate" locale="id" hide-header header="Pilih Tanggal Akhir"
                                @update:model-value="menuEndDate = false" />
                        </v-menu>
                    </v-col>

                    <!-- Clear Filters Button -->
                    <v-col cols="12" md="2" class="d-flex align-center mb-5">
                        <v-btn color="grey" variant="outlined" block @click="clearFilters">
                            <v-icon icon="mdi-filter-remove" start></v-icon>
                            <span class="ms-1">Reset</span>
                        </v-btn>
                    </v-col>
                </v-row>
            </v-list-item>

            <!-- Table -->
            <v-list-item>
                <v-data-table :headers="headers" :items="transactions.data" :items-per-page="15" class="elevation-1">
                    <!-- Transaction Date -->
                    <template #item.transaction_date="{ item }">
                        <span class="font-weight-medium">{{ formatDate(item.transaction_date) }}</span>
                    </template>

                    <!-- Type Badge -->
                    <template #item.type="{ item }">
                        <v-chip :color="item.type === 'in' ? 'blue-accent-3' : 'red-accent-3'" size="small"
                            variant="tonal">
                            <v-icon :icon="item.type === 'in' ? 'mdi-arrow-down-bold' : 'mdi-arrow-up-bold'" start
                                size="16"></v-icon>
                            {{ item.type === 'in' ? 'Masuk' : 'Keluar' }}
                        </v-chip>
                    </template>

                    <!-- Party (Supplier/Recipient) -->
                    <template #item.party="{ item }">
                        {{ getPartyName(item) }}
                    </template>

                    <!-- Division -->
                    <template #item.division="{ item }">
                        {{ item.division || '-' }}
                    </template>

                    <!-- Total Items -->
                    <template #item.total_items="{ item }">
                        <v-chip size="small" variant="tonal">
                            {{ item.transaction_items?.length || 0 }} jenis
                        </v-chip>
                    </template>

                    <!-- Notes -->
                    <template #item.notes="{ item }">
                        <span class="text-truncate d-inline-block" style="max-width: 200px;">
                            {{ item.notes || '-' }}
                        </span>
                    </template>

                    <!-- Actions -->
                    <template #item.actions="{ item }">
                        <v-btn icon size="small" variant="text" @click="showDetail(item)">
                            <v-icon icon="mdi-eye"></v-icon>
                        </v-btn>
                    </template>

                    <template #bottom></template>
                </v-data-table>
            </v-list-item>

            <!-- Pagination -->
            <v-list-item v-if="transactions.last_page > 1" class="mt-4">
                <v-pagination v-model="currentPage" :length="transactions.last_page" total-visible="7" />
            </v-list-item>
        </v-list>
    </v-container>
</template>