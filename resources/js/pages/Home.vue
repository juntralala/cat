<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';

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

const startDate = ref(props.date_range.start_date);
const endDate = ref(props.date_range.end_date);
const menuStartDate = ref(false);
const menuEndDate = ref(false);

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value);
};

// Format date
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
};

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

// Apply filter
const applyFilter = () => {
  router.get(route('dashboard'), {
    start_date: startDate.value,
    end_date: endDate.value,
  }, {
    preserveState: true,
  });
};

// Reset filter
const resetFilter = () => {
  startDate.value = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
  endDate.value = new Date().toISOString().split('T')[0];
  applyFilter();
};
</script>

<template>
  <v-container fluid>
    <!-- Page Title -->
    <v-row>
      <v-col>
        <h1 class="text-h4 font-weight-bold mb-2">
          Dashboard
        </h1>
        <p class="text-body-2 text-medium-emphasis">
          Ringkasan aktivitas warehouse dan distribusi
        </p>
      </v-col>
    </v-row>

    <!-- Date Range Filter -->
    <v-row class="mb-4">
      <v-col cols="12" md="3">
        <v-menu v-model="menuStartDate" :close-on-content-click="false">
          <template v-slot:activator="{ props: menuProps }">
            <v-text-field
              v-model="startDate"
              density="comfortable"
              label="Tanggal Mulai"
              readonly
              v-bind="menuProps"
              prepend-inner-icon="mdi-calendar"
            />
          </template>
          <v-date-picker
            v-model="startDate"
            @update:model-value="menuStartDate = false"
            :max="endDate"
          />
        </v-menu>
      </v-col>
      <v-col cols="12" md="3">
        <v-menu v-model="menuEndDate" :close-on-content-click="false">
          <template v-slot:activator="{ props: menuProps }">
            <v-text-field
              v-model="endDate"
              density="comfortable"
              label="Tanggal Akhir"
              readonly
              v-bind="menuProps"
              prepend-inner-icon="mdi-calendar"
            />
          </template>
          <v-date-picker
            v-model="endDate"
            @update:model-value="menuEndDate = false"
            :min="startDate"
            :max="new Date().toISOString().split('T')[0]"
          />
        </v-menu>
      </v-col>
      <v-col cols="12" md="3" class="d-flex align-center gap-2 md:pb-11!">
        <v-btn color="primary" @click="applyFilter">
          <v-icon start>mdi-filter</v-icon>
          Terapkan
        </v-btn>
        <v-btn variant="outlined" @click="resetFilter">
          Reset
        </v-btn>
      </v-col>
    </v-row>

    <!-- Statistics Cards -->
    <v-row>
      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4" color="blue-lighten-5">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Total Item</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.total_items }}</h3>
            </div>
            <v-icon size="48" color="blue">mdi-package-variant</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4" color="green-lighten-5">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Total SKU</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.total_skus }}</h3>
            </div>
            <v-icon size="48" color="green">mdi-barcode</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-4" color="orange-lighten-5">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Stok Menipis</p>
              <h3 class="text-h4 font-weight-bold">{{ statistics.low_stock_count }}</h3>
            </div>
            <v-icon size="48" color="orange">mdi-alert</v-icon>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" sm="6" md="3">
        <v-card class="pa-5" color="purple-lighten-5">
          <div class="d-flex align-center justify-space-between">
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">Nilai Inventori</p>
              <h3 class="text-h5 font-weight-bold">{{ formatCurrency(statistics.inventory_value) }}</h3>
            </div>
            <v-icon size="48" color="purple">mdi-cash-multiple</v-icon>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Transaction Summary -->
    <v-row class="mt-4">
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title class="d-flex align-center">
            <v-icon class="mr-2" color="green">mdi-arrow-down-bold</v-icon>
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
              <span class="text-h6 font-weight-bold text-green">{{ formatCurrency(statistics.inbound_value) }}</span>
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

    <!-- Charts and Tables Row -->
    <v-row class="mt-4">
      <!-- Monthly Trend Chart -->
      <v-col cols="12" md="8">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-chart-bar</v-icon>
            Tren Transaksi Bulanan (6 Bulan Terakhir)
          </v-card-title>
          <v-card-text>
            <div class="chart-container" style="height: 300px;">
              <canvas ref="chartCanvas" />
            </div>
            <!-- Simple bar visualization -->
            <div class="mt-4">
              <div v-for="(data, month) in monthly_trend" :key="month" class="mb-3">
                <div class="text-caption mb-1">{{ month }}</div>
                <div class="d-flex gap-2">
                  <v-progress-linear
                    :model-value="(data.inbound / Math.max(...Object.values(monthly_trend).map(d => Math.max(d.inbound, d.outbound)))) * 100"
                    color="green"
                    height="20"
                    class="grow"
                  >
                    <template v-slot:default>
                      <small>Masuk: {{ data.inbound }}</small>
                    </template>
                  </v-progress-linear>
                  <v-progress-linear
                    :model-value="(data.outbound / Math.max(...Object.values(monthly_trend).map(d => Math.max(d.inbound, d.outbound)))) * 100"
                    color="blue"
                    height="20"
                    class="grow"
                  >
                    <template v-slot:default>
                      <small>Keluar: {{ data.outbound }}</small>
                    </template>
                  </v-progress-linear>
                </div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Top Items -->
      <v-col cols="12" md="4">
        <v-card>
          <v-card-title>
            <v-icon class="mr-2">mdi-trophy</v-icon>
            Item Teratas
          </v-card-title>
          <v-card-text>
            <v-list density="compact">
              <v-list-item
                v-for="(item, index) in top_items"
                :key="item.id"
                class="px-0"
              >
                <template v-slot:prepend>
                  <v-chip :color="index === 0 ? 'amber' : index === 1 ? 'grey' : index === 2 ? 'brown' : 'blue'" size="small" class="mr-2">
                    {{ index + 1 }}
                  </v-chip>
                </template>
                <v-list-item-title>{{ item.name }}</v-list-item-title>
                <v-list-item-subtitle>{{ item.total_quantity }} unit • {{ item.transaction_count }} transaksi</v-list-item-subtitle>
              </v-list-item>
              <v-list-item v-if="top_items.length === 0">
                <v-list-item-title class="text-center text-medium-emphasis">
                  Tidak ada data
                </v-list-item-title>
              </v-list-item>
            </v-list>
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
                    <v-chip
                      :color="transaction.type === 'in' ? 'green' : 'blue'"
                      size="small"
                      variant="flat"
                    >
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
              <v-list-item
                v-for="item in low_stock_items"
                :key="item.id"
                class="px-0"
              >
                <v-list-item-title>{{ item.item_name }}</v-list-item-title>
                <v-list-item-subtitle>
                  {{ item.sku }} • {{ item.specification }}
                </v-list-item-subtitle>
                <template v-slot:append>
                  <v-chip
                    :color="item.quantity <= 5 ? 'red' : 'orange'"
                    size="small"
                  >
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
              <v-list-item
                v-for="(recipient, index) in top_recipients"
                :key="recipient.id"
                class="px-0"
              >
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
</style>