<script setup lang="ts">
import MasterIndex from "@/components/MasterIndex.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"
defineProps<{ conditions: MasterRecord[] }>()
const fields: MasterField[] = [{ key: "code", label: "Kode" }, { key: "name", label: "Nama tahap" }, { key: "sort_order", label: "Urutan" }]
</script>
<template><MasterIndex title="Tahap Pengukuran" description="Kelola tahapan proses pengukuran." :records="conditions" :fields="fields" route-name="measurement-conditions" /></template>

