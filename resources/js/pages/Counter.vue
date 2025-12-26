<script setup>
import { computed, ref } from 'vue';
// cuman buat ngetes cara kerja rerender di vue
const count = ref(0);
const count2 = 0;

const countValue = computed(() => count.value);
const count2Value = computed(() => count2);
</script>
<template>
  <h1 :value="count">{{ countValue }}<span><span>{{ count2 }}</span></span></h1>
  <h1>{{ count2Value }}</h1>
  <v-btn @click="count++">{{ count }} {{ count2 }}</v-btn>
  <v-btn @click="count2++">{{ count2 }}</v-btn>
</template>