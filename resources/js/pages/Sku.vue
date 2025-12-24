<script setup>
import ApplicationLayout from '@/layouts/ApplicationLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AlertDialog from '@/components/organisms/AlertDialog.vue';
defineOptions({
  layout: ApplicationLayout
});
const { skus, derivedMeasurementUnitPerSku } = defineProps({
  skus: {
    type: Object
  },
  derivedMeasurementUnitPerSku: {
    type: Array,
    default: []
  },
  items: {
    type: Array,
    default: []
  }
});
const showError = ref(false);
const page = ref(skus?.page || 1);
const addForm = useForm({
  item_id: null,
  sku: null,
  spesification_name: null,
  quantity: 0,
  price: 0,
  sku_measurement_unit_conversions: [
    {
      measurement_unit_id: null,
      conversion: 1
    }
  ]
});

const editForm = useForm({
  item_id: null,
  sku: null,
  spesification_name: null,
  quantity: 0,
  price: 0,
  sku_measurement_unit_conversions: [
    {
      measurement_unit_id: null,
      conversion: 1
    }
  ]
});

function getAvailableMeasurementUnits(currentConversion, form = addForm) {
  return derivedMeasurementUnitPerSku.filter(measurementUnit => {
    const isSelectedInOtherRow = form.sku_measurement_unit_conversions.some(conversion => {
      return conversion !== currentConversion &&
        conversion.measurement_unit_id === measurementUnit.id;
    });

    return !isSelectedInOtherRow;
  });
}
function removeItemFromConversionInputs(conversionIndex, form = addForm) {
  form.sku_measurement_unit_conversions.splice(conversionIndex, 1);
}
function disabledRemoveBtnForLastItem(index, form = addForm) {
  return (form.sku_measurement_unit_conversions.length - 1) == index && getAvailableMeasurementUnits(null, form).length !== 0;
}
function clearAddForm() {
  addForm.reset();
}

function clearEditForm() {
  editForm.reset();
}

function openEditDialog(sku) {
  editForm.item_id = sku.item_id;
  editForm.sku = sku.sku;
  editForm.spesification_name = sku.spesification_name;
  editForm.quantity = sku.quantity;
  editForm.price = sku.price;
  editForm.sku_measurement_unit_conversions = sku.sku_measurement_unit_conversions?.length > 0
    ? sku.sku_measurement_unit_conversions.map(c => ({
      measurement_unit_id: c.measurement_unit_id,
      conversion: c.conversion
    }))
    : [{
      measurement_unit_id: null,
      conversion: 1
    }];
}

watch(() => addForm.sku_measurement_unit_conversions, (newVal) => {
  const last = newVal.at(-1);
  if (last?.measurement_unit_id && last?.conversion && getAvailableMeasurementUnits().length !== 0) {
    addForm.sku_measurement_unit_conversions.push({
      measurement_unit_id: null,
      conversion: 1
    });
  }
}, { deep: true });

watch(() => editForm.sku_measurement_unit_conversions, (newVal) => {
  const last = newVal.at(-1);
  if (last?.measurement_unit_id && last?.conversion && getAvailableMeasurementUnits(null, editForm).length !== 0) {
    editForm.sku_measurement_unit_conversions.push({
      measurement_unit_id: null,
      conversion: 1
    });
  }
}, { deep: true });

function submitAddForm(isActive) {
  addForm.transform((data) => {
    return {
      ...data,
      sku_measurement_unit_conversions: data.sku_measurement_unit_conversions.filter(conversion => !!conversion.measurement_unit_id)
    }
  }).post(route('items.skus.create'), {
    onSuccess() {
      clearAddForm();
      isActive.value = false;
    },
    onError() {
      showError.value = true;
    }
  });
}

function submitEditForm(isActive, skuId) {
  editForm.transform((data) => {
    return {
      ...data,
      sku_measurement_unit_conversions: data.sku_measurement_unit_conversions.filter(conversion => !!conversion.measurement_unit_id)
    }
  }).put(route('items.skus.update', skuId), {
    onSuccess() {
      isActive.value = false;
    },
    onError() {
      showError.value = true;
    }
  });
}

function deleteSku(skuId) {
  router.delete(route('items.skus.delete', skuId));
}
</script>

<template>
  <AlertDialog v-model="showError"/>
  <Head title="Unit Penyimpanan Stok"></Head>
  <v-container>
    <v-row>
      <v-col>
        <h1 class="font-medium text-4xl">Unit <span class="text-blue-darken-2">Penyimpanan Stok</span></h1>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-btn id="create-form" variant="tonal" color="blue">Tambah</v-btn>
        <v-dialog activator="#create-form" max-width="850" @after-leave="clearAddForm" v-slot="{ isActive }">
          <v-card>
            <v-card-title class="text-center">Tambah Unit Penyimpanan Stok</v-card-title>
            <div class="px-3"><v-divider /></div>
            <v-form @submit.prevent="submitAddForm(isActive)">
              <v-card-text>
                <v-row>
                  <v-col cols="12" md="6" class="pb-1">
                    <v-autocomplete label="Barang" v-model="addForm.item_id" :items="items" item-value="id"
                      item-title="name" density="comfortable" :error-messages="addForm?.errors?.item_id" />
                  </v-col>
                  <v-col cols="12" md="6" class="pb-1">
                    <v-text-field label="SKU" v-model="addForm.sku" density="comfortable"
                      :error-messages="addForm?.errors?.sku" />
                  </v-col>
                  <v-col cols="12" md="6" class="py-1">
                    <v-text-field label="Nama Spesifikasi" v-model="addForm.spesification_name" density="comfortable"
                      :error-messages="addForm?.errors?.spesification_name" />
                  </v-col>
                  <v-col cols="12" md="6" class="py-1">
                    <v-number-input label="Harga satuan terkecil" prefix="Rp." :precision="2" :min="0"
                      control-variant="hidden" density="comfortable" v-model="addForm.price"
                      :error-messages="addForm?.errors?.price" />
                  </v-col>
                  <v-col cols="12" md="6" class="py-1">
                    <v-number-input label="Stok" v-model="addForm.quantity" :min="0" control-variant="hidden"
                      density="comfortable" :error-messages="addForm?.errors?.quantity" />
                  </v-col>
                </v-row>
                <v-divider class="text-sm!">Konversi</v-divider>
                <v-row v-for="(conversion, index) in addForm.sku_measurement_unit_conversions" class="mt-2"
                  align="center">
                  <v-col class="ps-2! pe-1! md:px-3!">
                    <v-autocomplete v-model="conversion.measurement_unit_id"
                      :items="getAvailableMeasurementUnits(conversion)" item-title="name" item-value="id" label="satuan"
                      density="comfortable" />
                  </v-col>
                  <v-col md="5" class="px-1! md:px-3!">
                    <v-number-input v-model="conversion.conversion" :min="1" label="Konversi ke satuan terkecil"
                      control-variant="hidden" density="comfortable" />
                  </v-col>
                  <v-col cols="2" md="1" class="px-0! md:px-3!">
                    <v-btn @click="removeItemFromConversionInputs(index)"
                      :disabled="disabledRemoveBtnForLastItem(index)" icon="mdi-minus" variant="plain" class="mb-4" />
                  </v-col>
                </v-row>
              </v-card-text>
              <v-card-actions class="justify-end">
                <v-btn @click="isActive.value = false">Batal</v-btn>
                <v-btn type="submit">Simpan</v-btn>
              </v-card-actions>
            </v-form>
          </v-card>
        </v-dialog>
      </v-col>
    </v-row>
    <v-row>
      <v-col>
        <v-table>
          <thead class="bg-blue-darken-1">
            <tr>
              <th class="w-1/12">No</th>
              <th>SKU</th>
              <th>Barang</th>
              <th>Nama Spesifikasi</th>
              <th>Stok</th>
              <th class="w-1/12">Tindakan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="skus.data.length != 0" v-for="(sku, i) in skus.data">
              <td>{{ i + 1 }}</td>
              <td>{{ sku.sku }}</td>
              <td>{{ sku.item.name }}</td>
              <td>{{ sku.spesification_name }}</td>
              <td>{{ sku.quantity }}</td>
              <td>
                <v-menu>
                  <template v-slot:activator="{ props }">
                    <v-icon icon="mdi-dots-vertical" v-bind="props" />
                  </template>
                  <v-list class="py-0.5!">
                    <v-list-item class="mx-0! px-1!">
                      <v-btn @click="openEditDialog(sku)" variant="text" block>
                        <span>Edit</span>
                        <v-dialog activator="parent" max-width="850" @after-leave="clearEditForm">
                          <template v-slot:default="{ isActive }">
                            <v-card>
                              <v-card-title class="text-center">Edit Unit Penyimpanan Stok</v-card-title>
                              <div class="px-3"><v-divider /></div>
                              <v-form @submit.prevent="submitEditForm(isActive, sku.id)">
                                <v-card-text>
                                  <v-row>
                                    <v-col cols="12" md="6" class="pb-1">
                                      <v-autocomplete label="Barang" v-model="editForm.item_id" :items="items"
                                        item-value="id" item-title="name" density="comfortable"
                                        :error-messages="editForm?.errors?.item_id" />
                                    </v-col>
                                    <v-col cols="12" md="6" class="pb-1">
                                      <v-text-field label="SKU" v-model="editForm.sku" density="comfortable"
                                        :error-messages="editForm?.errors?.sku" />
                                    </v-col>
                                    <v-col cols="12" md="6" class="py-1">
                                      <v-text-field label="Nama Spesifikasi" v-model="editForm.spesification_name"
                                        density="comfortable" :error-messages="editForm?.errors?.spesification_name" />
                                    </v-col>
                                    <v-col cols="12" md="6" class="py-1">
                                      <v-number-input label="Harga satuan terkecil" prefix="Rp." :precision="2" :min="0"
                                        control-variant="hidden" density="comfortable" v-model="editForm.price"
                                        :error-messages="editForm?.errors?.price" />
                                    </v-col>
                                    <v-col cols="12" md="6" class="py-1">
                                      <v-number-input label="Stok" v-model="editForm.quantity" :min="0"
                                        control-variant="hidden" density="comfortable"
                                        :error-messages="editForm?.errors?.quantity" />
                                    </v-col>
                                  </v-row>
                                  <v-divider class="text-sm!">Konversi</v-divider>
                                  <v-row v-for="(conversion, index) in editForm.sku_measurement_unit_conversions"
                                    class="mt-2" align="center">
                                    <v-col class="ps-2! pe-1! md:px-3!">
                                      <v-autocomplete v-model="conversion.measurement_unit_id"
                                        :items="getAvailableMeasurementUnits(conversion, editForm)" item-title="name"
                                        item-value="id" label="satuan" density="comfortable" />
                                    </v-col>
                                    <v-col md="5" class="px-1! md:px-3!">
                                      <v-number-input v-model="conversion.conversion" :min="1"
                                        label="Konversi ke satuan terkecil" control-variant="hidden"
                                        density="comfortable" />
                                    </v-col>
                                    <v-col cols="2" md="1" class="px-0! md:px-3!">
                                      <v-btn @click="removeItemFromConversionInputs(index, editForm)"
                                        :disabled="disabledRemoveBtnForLastItem(index, editForm)" icon="mdi-minus"
                                        variant="plain" class="mb-4" />
                                    </v-col>
                                  </v-row>
                                </v-card-text>
                                <v-card-actions class="justify-end">
                                  <v-btn @click="isActive.value = false">Batal</v-btn>
                                  <v-btn type="submit">Simpan</v-btn>
                                </v-card-actions>
                              </v-form>
                            </v-card>
                          </template>
                        </v-dialog>
                      </v-btn>
                    </v-list-item>
                    <v-list-item class="mx-0! px-1!">
                      <v-btn variant="text" block>
                        <span>Hapus</span>
                        <v-dialog activator="parent" max-width="450" v-slot="{ isActive }">
                          <v-card title="Konfirmasi Penghapusan!" class="text-center">
                            <v-card-text>
                              <p class="mb-8">
                                Apakah anda untuk menghapus barang "<strong>{{ sku.item.name }}</strong>" dengan sku
                                "<strong>{{ sku.sku
                                  }}</strong>"?
                              </p>
                              <v-btn @click="isActive.value = false" color="blue" variant="tonal">Tidak</v-btn>
                              <v-btn @click="deleteSku(sku.id)" variant="flat">Iya</v-btn>
                            </v-card-text>
                          </v-card>
                        </v-dialog>
                      </v-btn>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </td>
            </tr>
            <tr v-else>
              <td colspan="5" class="text-center text-gray-400">
                Tidak ada Unit Satuan Stok yang ditemukan...
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr v-if="skus.total != 0">
              <td colspan="100">
                <div class="flex justify-center">
                  <v-pagination v-model="page" :length="skus.last_page"></v-pagination>
                </div>
              </td>
            </tr>
          </tfoot>
        </v-table>
      </v-col>
    </v-row>
  </v-container>
</template>