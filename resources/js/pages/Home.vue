<script setup>
import { ref, computed, watch } from 'vue';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import DateTimePickerInput from '@/components/molecules/DateTimePickerInput.vue';
import axios from 'axios';
import { debounce } from 'lodash';
import { formatDateIndonesia } from '@/lib/formatters';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  statistics: Object,
  recent_transactions: Array,
  low_stock_items: Array,
  top_items: Array,
  monthly_trend: Object,
  top_recipients: Array,
  date_range: Object,
});

// Expenditure table state
const expenditureData = ref([]);
const expenditurePage = ref(1);
const expenditurePerPage = ref(10);
const expenditureLastPage = ref(1);
const expenditureTotal = ref(0);
const expenditureLoading = ref(false);
const searchOutbound = ref('');

// Date filter for expenditure table
const expenditureStartDate = ref(props.date_range.start_date);
const expenditureEndDate = ref(props.date_range.end_date);

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value);
};

// Format date
const formatDate = (date) => formatDateIndonesia(new Date(date));

// Fetch expenditure data from API
const fetchExpenditureData = async () => {
  expenditureLoading.value = true;
  try {
    const response = await axios.get(route('expenditures.skus'), {
      params: {
        p: "He",
        start: expenditureStartDate.value,
        end: expenditureEndDate.value,
        page: expenditurePage.value,
        search: searchOutbound.value || undefined,
      }
    });

    expenditureData.value = response.data.data;
    expenditurePage.value = response.data.currentPage;
    expenditurePerPage.value = response.data.perPage;
    expenditureLastPage.value = response.data.lastPage;
    expenditureTotal.value = response.data.total;
  } catch (error) {
    console.error('Error fetching expenditure data:', error);
  } finally {
    expenditureLoading.value = false;
  }
};

// Debounced search function
const debouncedSearch = debounce(() => {
  expenditurePage.value = 1; // Reset to first page on search
  fetchExpenditureData();
}, 500);

// Debounced date filter function
const debouncedDateFilter = debounce(() => {
  expenditurePage.value = 1; // Reset to first page on date change
  fetchExpenditureData();
}, 800);

// Watch for page changes
watch(expenditurePage, () => {
  fetchExpenditureData();
});

// Watch for search input changes with debounce
watch(searchOutbound, () => {
  debouncedSearch();
});

// Watch for date changes with debounce
watch([expenditureStartDate, expenditureEndDate], () => {
  debouncedDateFilter();
});

// Calculate total value of displayed data
const totalExpenditureValue = computed(() => {
  return expenditureData.value.reduce((sum, item) => sum + item.expenditure, 0);
});

// Chart data for monthly trend
const monthlyChartData = computed(() => {
  const months = Object.keys(props.monthly_trend);
  return {
    labels: months.map(m => {
      const [year, month] = m.split('-');
      return new Date(year, month - 1).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' });
    }),
    datasets: [
      {
        label: 'Barang Masuk',
        data: months.map(m => props.monthly_trend[m].inbound),
        backgroundColor: '#4CAF50',
      },
      {
        label: 'Barang Keluar',
        data: months.map(m => props.monthly_trend[m].outbound),
        backgroundColor: '#2196F3',
      },
    ],
  };
});

// Placeholder for CSV export - to be implemented later
const exportToCSV = () => {
  console.log('Export CSV - To be implemented');
};

// Load data on mount
fetchExpenditureData();
</script>

<template>
  <v-container fluid>
    <!-- Page Title -->
    <v-row>
      <v-col>
        <h1 class="text-h4 font-weight-bold mb-2">
          Dashboard
        </h1>
      </v-col>
    </v-row>

    <!-- Statistics Cards -->
    <v-row class="mt-4">
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Jenis Barang</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.total_items }}</h3>
            </div>
            <v-icon size="48" color="blue">mdi-package-variant</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Total SKU</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.total_skus }}</h3>
            </div>
            <v-icon size="48" color="blue-darken-1">mdi-barcode</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Stok Menipis</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.low_stock_count }}</h3>
            </div>
            <v-icon size="48" color="blue-darken-2">mdi-alert</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Nilai Inventori</p>
              <h3 class="text-h5 font-weight-bold">{{ formatCurrency(statistics.inventory_value) }}</h3>
            </div>
            <v-icon size="48" color="blue-darken-3">mdi-cash-multiple</v-icon>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Transaction Summary -->
    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="blue">mdi-arrow-down-bold</v-icon>
            Barang Masuk
          </v-card-title>
          <v-card-text>
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-body-2 text-medium-emphasis">Total Transaksi</span>
              <span class="text-h6 font-weight-bold">{{ statistics.inbound_count }}</span>
            </div>
            <v-divider class="my-2" />
            <div class="d-flex justify-space-between align-center">
              <span class="text-body-2 text-medium-emphasis">Total Nilai</span>
              <span class="text-h6 font-weight-bold text-blue">{{ formatCurrency(statistics.inbound_value) }}</span>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="blue">mdi-arrow-up-bold</v-icon>
            Barang Keluar
          </v-card-title>
          <v-card-text>
            <div class="d-flex justify-space-between align-center mb-2">
              <span class="text-body-2 text-medium-emphasis">Total Transaksi</span>
              <span class="text-h6 font-weight-bold">{{ statistics.outbound_count }}</span>
            </div>
            <v-divider class="my-2" />
            <div class="d-flex justify-space-between align-center">
              <span class="text-body-2 text-medium-emphasis">Total Nilai</span>
              <span class="text-h6 font-weight-bold text-blue">{{ formatCurrency(statistics.outbound_value) }}</span>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Pengeluaran Per-SKU Table -->
    <v-row class="mt-4">
      <v-col cols="12">
        <v-card>
          <v-card-title class="d-flex align-center justify-space-between">
            <div class="d-flex align-center">
              <!-- <v-icon class="mr-2" color="blue">mdi-file-table-box-multiple</v-icon> -->
              Pengeluaran Per SKU
            </div>
            <v-btn :href="route('expenditures.skus.export.xlsx')" color="grey" size="small" variant="plain" :disabled="expenditureLoading">
              <v-icon start>mdi-download</v-icon>
               SpreadSheet
            </v-btn>
          </v-card-title>

          <v-card-text>
            <!-- Search and Date Filter -->
            <v-row class="mb-3">
              <v-col cols="12" md="6">
                <v-text-field v-model="searchOutbound" density="compact" label="Cari"
                  hint="Cari berdasarkan Nama Barang, SKU dan Spesifikasi"
                  prepend-inner-icon="mdi-magnify" clearable :loading="expenditureLoading"
                  placeholder="Ketik untuk mencari..." />
              </v-col>
              <v-col cols="6" md="2">
                <DateTimePickerInput v-model="expenditureStartDate" label="Mulai" density="compact"
                  :loading="expenditureLoading" />
              </v-col>
              <v-col cols="6" md="2">
                <DateTimePickerInput v-model="expenditureEndDate" label="Sampai" density="compact"
                  :loading="expenditureLoading" />
              </v-col>
              <v-col cols="12" md="2" class="text-right">
                <div class="text-body-2 text-medium-emphasis">Total Nilai Pengeluaran</div>
                <div class="text-h6 font-weight-bold text-blue">{{ formatCurrency(totalExpenditureValue) }}</div>
              </v-col>
            </v-row>

            <!-- Table with Loading State -->
            <v-progress-linear v-if="expenditureLoading" indeterminate color="primary" class="mb-3" />

            <v-table density="comfortable" class="[&_td]:border-none!" striped="even" hover>
              <thead>
                <tr>
                  <th class="text-left">Nama Barang</th>
                  <th class="text-left">SKU</th>
                  <th class="text-left">Spesifikasi</th>
                  <th class="text-center">Jumlah</th>
                  <th class="text-right">Harga/Unit</th>
                  <th class="text-right">Total Pengeluaran</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in expenditureData" :key="item.sku">
                  <td class="font-weight-medium">{{ item.itemName }}</td>
                  <td>
                    <code class="text-caption bg-grey-lighten-3 pa-1 rounded">{{ item.sku }}</code>
                  </td>
                  <td>{{ item.spesificationName }}</td>
                  <td class="text-center font-weight-bold">
                    <v-chip size="small" :color="item.count > 0 ? 'blue' : 'grey'">
                      {{ item.count }} {{ item.measurementUnit }}
                    </v-chip>
                  </td>
                  <td class="text-right">{{ formatCurrency(item.pricePerUnit) }}</td>
                  <td class="text-right font-weight-bold" :class="item.expenditure > 0 ? 'text-blue' : 'text-grey'">
                    {{ formatCurrency(item.expenditure) }}
                  </td>
                </tr>
                <tr v-if="expenditureData.length === 0 && !expenditureLoading">
                  <td colspan="6" class="text-center text-medium-emphasis py-8">
                    <v-icon size="48" color="grey-lighten-1">mdi-database-off</v-icon>
                    <div class="mt-2">
                      {{ searchOutbound ? 'Tidak ada data yang sesuai dengan pencarian' : 'Tidak ada data pengeluaran'
                      }}
                    </div>
                    <div v-if="searchOutbound" class="text-caption mt-1">
                      Coba kata kunci lain atau hapus filter pencarian
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>

            <!-- Pagination -->
            <div v-if="expenditureData.length > 0" class="d-flex justify-end align-center mt-4">
              <v-pagination v-model="expenditurePage" :length="expenditureLastPage" :total-visible="7"
                density="comfortable" :disabled="expenditureLoading" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent Transactions and Low Stock -->
    <v-row class="mt-4">
      <!-- Recent Transactions -->
      <v-col cols="12" md="7">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-history</v-icon>
            Transaksi Terbaru
          </v-card-title>
          <v-card-text>
            <v-table density="comfortable">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Jenis</th>
                  <th>Penerima/Supplier</th>
                  <th class="text-right">Nilai</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="transaction in recent_transactions" :key="transaction.id">
                  <td>{{ formatDate(transaction.transaction_date) }}</td>
                  <td>
                    <v-chip :color="transaction.type === 'in' ? 'blue-darken-2' : 'blue-lighten-1'" size="small" variant="tonal">
                      <v-icon start size="small">
                        {{ transaction.type === 'in' ? 'mdi-arrow-down' : 'mdi-arrow-up' }}
                      </v-icon>
                      {{ transaction.type === 'in' ? 'Masuk' : 'Keluar' }}
                    </v-chip>
                  </td>
                  <td>{{ transaction.recipient || transaction.supplier || '-' }}</td>
                  <td class="text-right">{{ formatCurrency(transaction.total_value) }}</td>
                </tr>
                <tr v-if="recent_transactions.length === 0">
                  <td colspan="4" class="text-center text-medium-emphasis">
                    Tidak ada transaksi
                  </td>
                </tr>
              </tbody>
            </v-table>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Low Stock Items -->
      <v-col cols="12" md="5">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="orange">mdi-alert-circle</v-icon>
            Stok Menipis
          </v-card-title>
          <v-card-text>
            <v-list density="compact">
              <v-list-item v-for="item in low_stock_items" :key="item.id" class="px-0">
                <v-list-item-title>{{ item.item_name }}</v-list-item-title>
                <v-list-item-subtitle>
                  {{ item.sku }} • {{ item.specification }}
                </v-list-item-subtitle>
                <template v-slot:append>
                  <v-chip :color="item.quantity <= 5 ? 'red' : 'orange'" size="small">
                    {{ item.quantity }} {{ item.unit }}
                  </v-chip>
                </template>
              </v-list-item>
              <v-list-item v-if="low_stock_items.length === 0">
                <v-list-item-title class="text-center text-medium-emphasis">
                  Semua stok aman
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Top Recipients -->
    <v-row class="mt-4 mb-8">
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-account-group</v-icon>
            Penerima Teratas
          </v-card-title>
          <v-card-text>
            <v-list density="compact">
              <v-list-item v-for="(recipient, index) in top_recipients" :key="recipient.id" class="px-0">
                <template v-slot:prepend>
                  <v-avatar :color="'blue-' + ((index + 1) * 100)" size="32" class="mr-3">
                    <span class="text-white">{{ index + 1 }}</span>
                  </v-avatar>
                </template>
                <v-list-item-title>{{ recipient.name }}</v-list-item-title>
                <template v-slot:append>
                  <v-chip size="small">
                    {{ recipient.transaction_count }} transaksi
                  </v-chip>
                </template>
              </v-list-item>
              <v-list-item v-if="top_recipients.length === 0">
                <v-list-item-title class="text-center text-medium-emphasis">
                  Tidak ada data
                </v-list-item-title>
              </v-list-item>
            </v-list>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.chart-container {
  position: relative;
}

code {
  font-family: 'Courier New', monospace;
  font-size: 0.85em;
}
</style>