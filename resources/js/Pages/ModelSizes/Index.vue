<script setup lang="ts">
import MasterIndex from "@/components/MasterIndex.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ modelSizes: MasterRecord[] }>()

const fields: MasterField[] = [{ key: "model", label: "Model" }, { key: "size", label: "Ukuran" }, { key: "fullName", label: "Nama lengkap" }, { key: "description", label: "Keterangan" }]
</script>

<template><MasterIndex title="Ukuran Model" description="Kelola ukuran model yang digunakan." :records="modelSizes" :fields="fields" route-name="model-sizes" /></template>
