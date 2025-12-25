<script setup>
defineProps({
    size: {
        type: [Number, String],
        default: () => Math.floor(Math.random() * 100) % 40 + 10
    },
    duration: {
        type: String,
        default: () => `${Math.floor(Math.random() * 100) % 20 + 12}s`,
    },
    delay: {
        type: String,
        default: () => `${Math.floor(Math.random() * 10) % 12}s`,
    },
    left: {
        type: String,
        default: () => `${Math.floor(Math.random() * 100)}%`,
    },
    alpha: {
        type: Number,
        default: 0.2,
    }
});
</script>

<template>
    <div
class="rounded-2xl" :style="{
        position: 'absolute',
        opacity: 0,
        left: left,
        animation: `bubble-up ${duration} infinite`,
        animationDelay: delay,
        width: `calc(${size} * 0.25rem)`,
        height: `calc(${size} * 0.25rem)`,
        backgroundColor: `rgba(255, 255, 255, ${alpha})`
    }">
    </div>
</template>