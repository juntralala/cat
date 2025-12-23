<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: ApplicationLayout
});

const props = defineProps({
    totalItems: Number,
    totalStockValue: Number,
    lowStockItems: Number,
    transactionsToday: Number,
    transactionsThisMonth: Number,
    recentTransactions: Array,
    topItems: Array,
    monthlyTransactions: Array,
    stockAlerts: Array,
    topDivisions: Array
});

// Format number with thousands separator
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
    };
    return date.toLocaleDateString('id-ID', options);
}

// Navigate to detail
function viewTransaction(id) {
    router.visit(route('items.transactions.history'));
}

function viewStock() {
    router.visit(route('items.stocks'));
}
</script>

<template>
    <v-container fluid>
        <!-- Header -->
        <v-row class="mb-4">
            <v-col>
                <h1 class="text-4xl font-medium">Dashboard <span class="text-blue-darken-2">Inventory</span></h1>
                <p class="text-grey-darken-1 mt-2">Ringkasan dan statistik sistem inventory</p>
            </v-col>
        </v-row>

        <!-- Stats Cards -->
        <v-row>
            <!-- Total Items -->
            <v-col cols="12" md="3">
                <v-card class="pa-4" elevation="2">
                    <div class="d-flex align-center justify-space-between">
                        <div>
                            <p class="text-caption text-grey-darken-1 mb-1">Total Barang</p>
                            <h2 class="text-h4 font-weight-bold text-blue-darken-2">{{ formatNumber(totalItems) }}</h2>
                            <p class="text-caption text-grey mt-1">Item terdaftar</p>
                        </div>
                        <v-avatar color="blue-lighten-4" size="56">
                            <v-icon icon="mdi-package-variant" size="32" color="blue-darken-2"></v-icon>
                        </v-avatar>
                    </div>
                </v-card>
            </v-col>

            <!-- Total Stock Value -->
            <v-col cols="12" md="3">
                <v-card class="pa-4" elevation="2">
                    <div class="d-flex align-center justify-space-between">
                        <div>
                            <p class="text-caption text-grey-darken-1 mb-1">Total Stok</p>
                            <h2 class="text-h4 font-weight-bold text-blue-darken-1">{{ formatNumber(totalStockValue) }}</h2>
                            <p class="text-caption text-grey mt-1">Unit tersedia</p>
                        </div>
                        <v-avatar color="blue-lighten-3" size="56">
                            <v-icon icon="mdi-archive" size="32" color="blue-darken-2"></v-icon>
                        </v-avatar>
                    </div>
                </v-card>
            </v-col>

            <!-- Low Stock Items -->
            <v-col cols="12" md="3">
                <v-card class="pa-4" elevation="2">
                    <div class="d-flex align-center justify-space-between">
                        <div>
                            <p class="text-caption text-grey-darken-1 mb-1">Stok Menipis</p>
                            <h2 class="text-h4 font-weight-bold text-blue">{{ formatNumber(lowStockItems) }}</h2>
                            <p class="text-caption text-grey mt-1">Perlu restock</p>
                        </div>
                        <v-avatar color="blue-lighten-2" size="56">
                            <v-icon icon="mdi-alert" size="32" color="blue-darken-3"></v-icon>
                        </v-avatar>
                    </div>
                </v-card>
            </v-col>

            <!-- Transactions This Month -->
            <v-col cols="12" md="3">
                <v-card class="pa-4" elevation="2">
                    <div class="d-flex align-center justify-space-between">
                        <div>
                            <p class="text-caption text-grey-darken-1 mb-1">Transaksi Bulan Ini</p>
                            <h2 class="text-h4 font-weight-bold text-blue-darken-3">{{ formatNumber(transactionsThisMonth) }}</h2>
                            <p class="text-caption text-grey mt-1">{{ formatNumber(transactionsToday) }} hari ini</p>
                        </div>
                        <v-avatar color="blue-lighten-1" size="56">
                            <v-icon icon="mdi-swap-horizontal" size="32" color="blue-darken-4"></v-icon>
                        </v-avatar>
                    </div>
                </v-card>
            </v-col>
        </v-row>

        <!-- Charts & Recent Activity -->
        <v-row class="mt-4">
            <!-- Monthly Transactions Chart -->
            <v-col cols="12" md="8">
                <v-card elevation="2">
                    <v-card-title class="pa-4 bg-blue-lighten-5">
                        <v-icon icon="mdi-chart-line" class="mr-2" color="blue-darken-2"></v-icon>
                        <span class="text-blue-darken-2">Transaksi Bulanan</span>
                    </v-card-title>
                    <v-card-text class="pa-6">
                        <v-row v-if="monthlyTransactions && monthlyTransactions.length > 0">
                            <v-col v-for="(item, index) in monthlyTransactions" :key="index" cols="12" md="4" class="text-center">
                                <div class="mb-2">
                                    <v-chip :color="item.type === 'in' ? 'blue' : 'blue-darken-3'" size="small">
                                        {{ item.type === 'in' ? 'Masuk' : 'Keluar' }}
                                    </v-chip>
                                </div>
                                <h3 class="text-h3 font-weight-bold text-blue-darken-2">{{ formatNumber(item.count) }}</h3>
                                <p class="text-caption text-grey">{{ item.month }}</p>
                            </v-col>
                        </v-row>
                        <v-row v-else>
                            <v-col class="text-center py-8">
                                <v-icon icon="mdi-chart-line-variant" size="64" color="blue-lighten-3"></v-icon>
                                <p class="text-grey mt-4">Belum ada data transaksi</p>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>
            </v-col>

            <!-- Stock Alerts -->
            <v-col cols="12" md="4">
                <v-card elevation="2" height="100%">
                    <v-card-title class="pa-4 bg-blue-lighten-5 d-flex align-center justify-space-between">
                        <div>
                            <v-icon icon="mdi-alert-circle" class="mr-2" color="blue-darken-2"></v-icon>
                            <span class="text-blue-darken-2">Peringatan Stok</span>
                        </div>
                        <v-btn size="small" variant="text" color="blue-darken-2" @click="viewStock">Lihat Semua</v-btn>
                    </v-card-title>
                    <v-card-text class="pa-0" style="max-height: 300px; overflow-y: auto;">
                        <v-list v-if="stockAlerts && stockAlerts.length > 0" density="compact">
                            <v-list-item 
                                v-for="alert in stockAlerts" 
                                :key="alert.id"
                                class="border-b"
                            >
                                <template v-slot:prepend>
                                    <v-avatar color="blue-lighten-4" size="40">
                                        <v-icon icon="mdi-package-variant" color="blue-darken-2"></v-icon>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-medium">
                                    {{ alert.item_name }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    Stok: {{ alert.quantity }} {{ alert.unit_name }}
                                </v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                        <div v-else class="text-center py-8">
                            <v-icon icon="mdi-check-circle" size="48" color="blue"></v-icon>
                            <p class="text-grey mt-2">Semua stok aman</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Recent Transactions & Top Items & Top Divisions -->
        <v-row class="mt-4">
            <!-- Recent Transactions -->
            <v-col cols="12" md="7">
                <v-card elevation="2">
                    <v-card-title class="pa-4 bg-blue-lighten-5 d-flex align-center justify-space-between">
                        <div>
                            <v-icon icon="mdi-history" class="mr-2" color="blue-darken-2"></v-icon>
                            <span class="text-blue-darken-2">Transaksi Terbaru</span>
                        </div>
                        <v-btn size="small" variant="text" color="blue-darken-2" @click="viewTransaction">Lihat Semua</v-btn>
                    </v-card-title>
                    <v-card-text class="pa-0">
                        <v-table v-if="recentTransactions && recentTransactions.length > 0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Pihak</th>
                                    <th class="text-right">Item</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="transaction in recentTransactions" :key="transaction.id">
                                    <td class="text-caption">{{ formatDate(transaction.transaction_date) }}</td>
                                    <td>
                                        <v-chip 
                                            :color="transaction.type === 'in' ? 'blue' : 'blue-darken-3'" 
                                            size="x-small"
                                        >
                                            {{ transaction.type === 'in' ? 'Masuk' : 'Keluar' }}
                                        </v-chip>
                                    </td>
                                    <td>{{ transaction.party || '-' }}</td>
                                    <td class="text-right">{{ transaction.total_items }} item</td>
                                </tr>
                            </tbody>
                        </v-table>
                        <div v-else class="text-center py-8">
                            <v-icon icon="mdi-inbox" size="48" color="blue-lighten-3"></v-icon>
                            <p class="text-grey mt-2">Belum ada transaksi</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <!-- Top Items -->
            <v-col cols="12" md="5">
                <v-card elevation="2">
                    <v-card-title class="pa-4 bg-blue-lighten-5">
                        <v-icon icon="mdi-trending-up" class="mr-2" color="blue-darken-2"></v-icon>
                        <span class="text-blue-darken-2">Barang Paling Aktif</span>
                    </v-card-title>
                    <v-card-text class="pa-0" style="max-height: 350px; overflow-y: auto;">
                        <v-list v-if="topItems && topItems.length > 0" density="compact">
                            <v-list-item 
                                v-for="(item, index) in topItems" 
                                :key="item.id"
                                class="border-b"
                            >
                                <template v-slot:prepend>
                                    <v-avatar :color="index === 0 ? 'blue-darken-3' : index === 1 ? 'blue-darken-1' : 'blue'" size="32">
                                        <span class="font-weight-bold text-white">{{ index + 1 }}</span>
                                    </v-avatar>
                                </template>
                                <v-list-item-title class="font-weight-medium">
                                    {{ item.name }}
                                </v-list-item-title>
                                <v-list-item-subtitle>
                                    {{ formatNumber(item.transaction_count) }} transaksi
                                </v-list-item-subtitle>
                            </v-list-item>
                        </v-list>
                        <div v-else class="text-center py-8">
                            <v-icon icon="mdi-package-variant" size="48" color="blue-lighten-3"></v-icon>
                            <p class="text-grey mt-2">Belum ada data</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Top Divisions (NEW) -->
        <v-row class="mt-4">
            <v-col cols="12">
                <v-card elevation="2">
                    <v-card-title class="pa-4 bg-blue-lighten-5">
                        <v-icon icon="mdi-office-building" class="mr-2" color="blue-darken-2"></v-icon>
                        <span class="text-blue-darken-2">Divisi Paling Aktif Mengambil Barang</span>
                        <span class="text-caption text-grey ml-2">(Bulan Ini)</span>
                    </v-card-title>
                    <v-card-text class="pa-0">
                        <v-list v-if="topDivisions && topDivisions.length > 0" class="py-0">
                            <v-list-item 
                                v-for="(division, index) in topDivisions" 
                                :key="index"
                                class="border-b"
                            >
                                <template v-slot:prepend>
                                    <v-avatar 
                                        :color="index === 0 ? 'blue-darken-3' : index === 1 ? 'blue-darken-1' : index === 2 ? 'blue' : 'blue-lighten-2'" 
                                        size="48"
                                    >
                                        <v-icon 
                                            icon="mdi-domain" 
                                            size="28"
                                            color="white"
                                        ></v-icon>
                                    </v-avatar>
                                </template>

                                <v-list-item-title class="font-weight-bold text-h6 mb-1">
                                    {{ division.division }}
                                </v-list-item-title>
                                
                                <v-list-item-subtitle class="mt-1">
                                    <v-chip color="blue-darken-2" variant="text" size="small" class="mr-2">
                                        {{ formatNumber(division.transaction_count) }} pengambilan
                                    </v-chip>
                                    <v-chip color="blue-darken-2" variant="text" size="small">
                                        {{ formatNumber(division.total_items) }} {{ division.top_item }} diambil
                                    </v-chip>
                                </v-list-item-subtitle>

                                <template v-slot:append>
                                    <div class="text-right" style="min-width: 200px;">
                                        <p class="text-caption text-grey-darken-1 mb-1">Barang Terbanyak:</p>
                                        <p class="font-weight-bold text-body-2">{{ division.top_item }}</p>
                                        <p class="text-caption text-grey">{{ formatNumber(division.top_item_quantity) }} unit</p>
                                    </div>
                                </template>
                            </v-list-item>
                        </v-list>
                        <div v-else class="text-center py-8">
                            <v-icon icon="mdi-office-building-outline" size="64" color="blue-lighten-3"></v-icon>
                            <p class="text-grey mt-4">Belum ada data transaksi keluar bulan ini</p>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>