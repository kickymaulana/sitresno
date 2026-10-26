<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"
defineProps<{ condition: MasterRecord }>()
const fields: MasterField[] = [
  { key: "code", label: "Kode", placeholder: "Kode tahap" },
  { key: "label", label: "Nama tahap", placeholder: "Nama tahap" },
  { key: "sort_order", label: "Urutan", placeholder: "Urutan", type: "number" },
]
</script>
<template><MasterForm title="Tahap Pengukuran" description="Perbarui tahap proses pengukuran." :fields="fields" route-name="measurement-conditions" :record="condition" /></template>
