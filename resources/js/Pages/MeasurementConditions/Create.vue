<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField } from "@/lib/crud"
const fields: MasterField[] = [
  { key: "code", label: "Kode", placeholder: "contoh: sebelum_cuci" },
  { key: "label", label: "Nama tahap", placeholder: "contoh: Sebelum Cuci" },
  { key: "sort_order", label: "Urutan", placeholder: "contoh: 1", type: "number" },
]
</script>
<template><MasterForm title="Tahap Pengukuran" description="Tambah tahap proses pengukuran." :fields="fields" route-name="measurement-conditions" /></template>
