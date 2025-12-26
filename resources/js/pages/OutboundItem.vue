<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import SuccessDialog from '@/components/organisms/SuccessDialog.vue';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
import PageTitleHighlightPart from '@/components/atoms/PageTitleHighlightPart.vue';
import { formatDateIndonesia } from '@/lib/formatters';
import axios from 'axios';

defineOptions({
  layout: ApplicationLayout,
});

const props = defineProps({
  items: Array,
  measurementUnits: Array,
  recipients: Array,
});

const skuSelectionItems = props.items.reduce((accumulator, item) => {
  accumulator.push({ type: "subheader", title: item.name });
  item.skus.forEach(sku => {
    accumulator.push({ value: sku.id, title: sku.sku, raw: item });
  });
  return accumulator;
}, []);


const successDialog = ref(false);
const errorDialog = ref(false);
const errorMessage = ref('');
const menuDate = ref(false);

// Watch perubahan recipient_id untuk auto-fill division
function onRecipientChange(recipient) {
  if (!recipient) {
    console.warn('recive recipient null');
    return;
  }
  if (recipient instanceof String || typeof recipient === 'string') {
    console.log('recive recipient string: ' + recipient + a);
    return;
  }
  if (recipient.division) {
    form.division = recipient.division;
  }
}

const form = useForm({
  recipient_id: null,
  division: '',
  transaction_date: new Date(),
  notes: '',
  transaction_items: [
    {
      item_id: null,
      unit_id: null,
      quantity: 0,
      supportedUnits: []
    },
  ],
});

const formattedDate = computed(() => formatDateIndonesia(form.transaction_date));

function addItem() {
  form.transaction_items.push({
    item_id: null,
    unit_id: null,
    quantity: 1,
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
      // Ubah format tanggal ke YYYY-MM-DD sebelum dikirim
      if (data.transaction_date instanceof Date) {
        data.transaction_date = data.transaction_date.toISOString();
      }
      if(!(data.recipient_id instanceof String)) {
        data.recipient_id = data.recipient_id.id
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
    .post(route('items.outbound'), {
      onSuccess: () => {
        successDialog.value = true;
        form.reset();
      },
      onError: (errors) => {
        console.error('Validation errors:', errors);
        errorDialog.value = true;
        // Ambil pesan error pertama atau error umum
        if (errors.error) {
          errorMessage.value = errors.error;
        } else {
          const firstError = Object.values(errors)[0];
          errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
        }
      },
    });
}

function cancel() {
  form.reset();
  window.history.back();
}

const recipientItems = computed(() => {
  return props.recipients.map((item) => {
    item.name_nickname = item.nickname ? `${item.name} (${item.nickname})` : item.name;
    return { ...item };
  });
});

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
</script>

<template>
  <v-container fluid>
    <SuccessDialog v-model="successDialog" title="Berhasil!" message="Transaksi barang keluar berhasil disimpan." />
    <AlertDialog v-model="errorDialog" title="Gagal!" :message="errorMessage" />

    <v-row>
      <v-col>
        <PageTitleHighlightPart first-part-title="Barang" second-part-title="Keluar" />
      </v-col>
    </v-row>

    <v-row>
      <v-col>
        <form @submit.prevent="submitTransaction">
          <!-- Header Transaction Fields -->
          <v-row class="pt-4">
            <v-col cols="12" md="4">
              <v-combobox v-model="form.recipient_id" density="comfortable" :items="recipientItems"
                item-title="name_nickname" label="Penerima" :error-messages="form.errors.recipient_id"
                @update:model-value="onRecipientChange" />
            </v-col>
            <v-col cols="12" md="4">
              <v-text-field v-model="form.division" density="comfortable" label="Divisi (opsional)"
                :error-messages="form.errors.division" persistent-hint />
            </v-col>
            <v-col cols="12" md="4">
              <v-menu v-model="menuDate" :close-on-content-click="false" transition="scale-transition" min-width="auto">
                <template v-slot:activator="{ props: menuProps }">
                  <v-text-field v-model="formattedDate" density="comfortable" label="Tanggal Transaksi" readonly
                    v-bind="menuProps" prepend-inner-icon="mdi-calendar"
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
              <v-btn @click="addItem" variant="tonal" color="blue-accent-2" :disabled="form.processing">
                <v-icon icon="mdi-plus" start></v-icon>
                Tambah Barang
              </v-btn>
            </v-col>
          </v-row>

          <v-divider class="my-6"></v-divider>

          <v-row>
            <v-col class="flex justify-end gap-4">
              <v-btn @click="cancel" variant="outlined" color="grey" :disabled="form.processing"> Batal </v-btn>
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
