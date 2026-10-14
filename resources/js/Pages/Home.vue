<template>
  <div style="padding: 2rem; font-family: sans-serif;">
    <h1>Selamat Datang di Inertia v3 + Vue 3!</h1>
    <p>Pesan dari Laravel: {{ message }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps({
  message: String
})
</script>
