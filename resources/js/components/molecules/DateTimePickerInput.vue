<script setup>
import { formatDateIndonesia } from '@/lib/formatters';
import { computed } from 'vue';

defineProps({
  errorMessages: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: 'Pilih Tanggal',
  },
  density: {
    type: String,
    default: 'default'
  }
});

const pickedDate = defineModel({ type: String });
const formattedDate = computed(() => formatDateIndonesia(pickedDate.value));
</script>

<template>
  <v-text-field v-model="formattedDate" :density :label readonly prepend-inner-icon="mdi-calendar"
    :error-messages="errorMessages">
    <v-menu v-slot="{ isActive }" activator="parent" :close-on-content-click="false" transition="scale-transition"
      min-width="auto">
      <v-date-picker v-model="pickedDate" header="Pilih Tanggal" hide-header :max="new Date()"
        @update:model-value="isActive.value = false" />
    </v-menu>
  </v-text-field>
</template>
