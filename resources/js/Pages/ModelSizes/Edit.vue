<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ modelSize: MasterRecord }>()

const fields: MasterField[] = [{ key: "model", label: "Model", placeholder: "Masukkan model" }, { key: "size", label: "Ukuran", placeholder: "Masukkan ukuran" }, { key: "fullName", label: "Nama lengkap", placeholder: "Masukkan nama lengkap" }, { key: "description", label: "Keterangan", placeholder: "Keterangan ukuran" }]
</script>

<template><MasterForm title="Ukuran Model" description="Perbarui ukuran model." :fields="fields" route-name="model-sizes" :record="modelSize" /></template>
