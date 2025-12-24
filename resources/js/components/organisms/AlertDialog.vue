<script setup>
    const {message} = defineProps({
        title: {
            type: String,
        default: "Peringatan!"
    },
    message: {
        type: String,
        default: "Ups, Terjadi kesalahan"
    }
});
const show = defineModel();

</script>

<template>
    <v-dialog v-model="show" v-slot="{ isActive }">
        <v-card>
            <v-card-title class="text-center! bg-red-darken-2">{{ title }}</v-card-title>
            <v-card-text class="text-center!">{{ message }}</v-card-text>
            <v-card-actions class="justify-center!">
                <v-btn @click="isActive.value = false">Tutup</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>