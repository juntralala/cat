<script setup>
defineProps({
  title: {
    type: String,
    default: 'Peringatan!',
  },
  message: {
    type: String,
    default: 'Ups, Terjadi kesalahan',
  },
});
const show = defineModel();
</script>

<template>
  <v-dialog v-slot="{ isActive }" v-model="show" max-width="400">
    <v-card>
      <v-card-text class="pa-8 text-center">
        <v-icon icon="mdi-alert-circle" size="64" color="error" class="mb-4"></v-icon>
        <h2 class="text-h5 font-weight-bold mb-2">{{ title }}</h2>
        <p class="text-body-1">{{ message }}</p>
      </v-card-text>
      <v-card-actions class="justify-center pb-6">
        <v-btn color="error" variant="flat" @click="isActive.value = false">Tutup</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
