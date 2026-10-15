<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField } from "@/lib/crud"

const fields: MasterField[] = [{ key: "model", label: "Model", placeholder: "Masukkan model" }, { key: "size", label: "Ukuran", placeholder: "Masukkan ukuran" }, { key: "fullName", label: "Nama lengkap", placeholder: "Masukkan nama lengkap" }, { key: "description", label: "Keterangan", placeholder: "Keterangan ukuran" }]
</script>

<template><MasterForm title="Ukuran Model" description="Tambah ukuran model baru." :fields="fields" route-name="model-sizes" /></template>
