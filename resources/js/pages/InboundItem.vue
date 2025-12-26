<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import DateTimePickerInput from '@/components/molecules/DateTimePickerInput.vue';
import axios from "axios";

defineOptions({
  layout: ApplicationLayout,
});
const { items } = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  measurementUnits: {
    type: Array,
    default: () => []
  },
});

const successDialog = ref(false);
const errorDialog = ref(false);
const errorMessage = ref('');

const skuSelectionItems = items.reduce((accumulator, item, index) => {
  accumulator.push({ type: 'subheader', title: item.name });
  item.skus.forEach(sku => {
    accumulator.push({
      title: sku.sku,
      value: sku.id,
      raw: item
    });
  });
  return accumulator;
}, []);

const form = useForm({
  type: 'in',
  supplier: '',
  transaction_date: new Date(),
  notes: '',
  transaction_items: [
    {
      sku_id: null,
      unit_id: null,
      quantity: 1,
      supportedUnits: []
    },
  ],
});

function addItem() {
  form.transaction_items.push({
    item_id: null,
    unit_id: null,
    quantity: 1,
    supportedUnits: []
  });
}

function deleteItem(index) {
  if (form.transaction_items.length > 1) {
    form.transaction_items.splice(index, 1);
  }
}

function submitTransaction() {
  form
    .transform((data) => {
      if (data.transaction_date instanceof Date) {
        data.transaction_date = data.transaction_date.toISOString();
      }
      data.transaction_items = data.transaction_items.map(item => {
        return {
          sku_id: item.sku_id,
          unit_id: item.unit_id,
          quantity: item.quantity,
        };
      });
      return data;
    })
    .post(route('items.inbound'), {
      onSuccess: () => {
        successDialog.value = true;
        form.reset();
      },
      onError: (errors) => {
        console.error('Validation errors:', errors);
        errorDialog.value = true;
        if (errors.error) {
          errorMessage.value = errors.error;
        } else {
          const firstError = Object.values(errors)[0];
          errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
        }
      },
    });
}

async function fetchSupportedMeasurementUnitBySkuId(skuId) {
  return (await axios.get(route('items.skus.units.by-sku-id', skuId))).data?.data;
}

watch(() => form.transaction_items, async (items) => {
  for (let i = 0; i < items.length; i++) {
    const item = items[i];

    // Jika sku_id berubah, fetch supported units
    if (item.sku_id && (!item.supportedUnits || item.supportedUnits.length === 0)) {
      try {
        const supportedUnits = await fetchSupportedMeasurementUnitBySkuId(item.sku_id);
        item.supportedUnits = supportedUnits;

        // Reset unit_id jika unit yang dipilih tidak ada di supported units
        if (item.unit_id && !supportedUnits.find(u => u.id === item.unit_id)) {
          item.unit_id = null;
        }
      } catch (error) {
        console.error('Error fetching supported units:', error);
      }
    }
  }
}, { deep: true });

function cancel() {
  form.reset();
}
</script>

<template>
  <v-container fluid>
    <SuccessDialog v-model="successDialog" message="Transaksi barang masuk berhasil disimpan." />
    <AlertDialog title="Gagal!" v-model="errorDialog" :message="errorMessage" />

    <v-row>
      <v-col>
        <PageTitleHighlightPart first-part-title="Barang" second-part-title="Masuk" />
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <form @submit.prevent="submitTransaction">
          <!-- Header Transaction Fields -->
          <v-row class="pt-4">
            <v-col cols="12" md="6">
              <v-text-field v-model="form.supplier" density="comfortable" label="Nama Pemasok (opsional)"
                :error-messages="form.errors.supplier" />
            </v-col>
            <v-col cols="12" md="6">
              <DateTimePickerInput v-model="form.transaction_date" :error-messages="form.errors.transaction_date" />
            </v-col>
          </v-row>

          <v-row>
            <v-col cols="12">
              <v-textarea v-model="form.notes" density="comfortable" label="Catatan (opsional)" rows="2"
                :error-messages="form.errors.notes" />
            </v-col>
          </v-row>

          <v-divider class="my-6"></v-divider>

          <!-- Transaction Items -->
          <v-row v-for="(item, index) in form.transaction_items" :key="index" class="items-start!">
            <v-col class="py-0!">
              <v-autocomplete v-model="item.sku_id" density="comfortable" :items="skuSelectionItems" label="SKU"
                @update:model-value="() => {
                  // Reset supportedUnits agar watcher bisa fetch ulang
                  item.supportedUnits = [];
                  item.unit_id = null;
                }" :error-messages="form.errors[`transaction_items.${index}.sku_id`]">
              </v-autocomplete>
            </v-col>
            <v-col class="py-0!" cols="2">
              <v-autocomplete v-model="item.unit_id" density="comfortable" :items="item.supportedUnits"
                item-title="name" item-value="id" label="Satuan"
                :disabled="!item.sku_id || item.supportedUnits.length === 0"
                :error-messages="form.errors[`transaction_items.${index}.unit_id`]" />
            </v-col>
            <v-col class="py-0!" cols="2">
              <v-number-input v-model="item.quantity" :min="1" control-variant="split" density="comfortable"
                label="Jumlah" :error-messages="form.errors[`transaction_items.${index}.quantity`]" />
            </v-col>
            <v-col cols="auto" class="mt-1 py-0!">
              <v-btn variant="tonal" color="red" icon size="small" :disabled="form.transaction_items.length === 1"
                @click="deleteItem(index)">
                <v-icon icon="mdi-minus"></v-icon>
              </v-btn>
            </v-col>
          </v-row>

          <v-row class="mt-2">
            <v-col class="flex items-center! justify-end! pt-0">
              <v-btn variant="tonal" color="blue-accent-2" :disabled="form.processing" @click="addItem">
                <v-icon icon="mdi-plus" start></v-icon>
                Tambah Barang
              </v-btn>
            </v-col>
          </v-row>

          <v-divider class="my-6"></v-divider>

          <v-row>
            <v-col class="flex justify-end gap-4">
              <v-btn variant="outlined" color="grey" :disabled="form.processing" @click="cancel">
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