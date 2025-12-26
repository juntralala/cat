<script setup>
// komponent spesifik pada halaman transaksi barang keluar dan masuk 
const  transactionItems = defineModel({
  default: () => [{
    sku_id: null,
    unit_id: null,
    quantity: 1,
    supportedUnits: []
  }]
});
defineProps({
  skuSelectionItems: {
    type: Array
  },
  errors: {
    type: Object
  }
})
</script>
<template>
  <v-row v-for="(item, index) in transactionItems" :key="index" class="items-start!">
    <v-col class="py-0!">
      <v-autocomplete
        v-model="item.sku_id"
        density="comfortable"
        :items="skuSelectionItems"
        label="SKU"
        @update:model-value="() => {
          // Reset supportedUnits agar watcher bisa fetch ulang
          item.supportedUnits = [];
          item.unit_id = null;
        }" :error-messages="form.errors[`transaction_items.${index}.sku_id`]">
      </v-autocomplete>
    </v-col>
    <v-col class="py-0!" cols="2">
      <v-autocomplete
        v-model="item.unit_id"
        density="comfortable"
        :items="item.supportedUnits"
        item-title="name"
        item-value="id"
        label="Satuan"
        :disabled="!item.sku_id || item.supportedUnits.length === 0"
        :error-messages="form.errors[`transaction_items.${index}.unit_id`]" />
    </v-col>
    <v-col class="py-0!" cols="2">
      <v-number-input
        v-model="item.quantity"
        :min="1"
        control-variant="split"
        density="comfortable"
        label="Jumlah"
        :error-messages="form.errors[`transaction_items.${index}.quantity`]" />
    </v-col>
    <v-col cols="auto" class="mt-1 py-0!">
      <v-btn
        variant="tonal"
        color="red"
        icon
        size="small"
        :disabled="form.transaction_items.length === 1"
        @click="deleteItem(index)">
        <v-icon icon="mdi-minus"></v-icon>
      </v-btn>
    </v-col>
  </v-row>
</template>