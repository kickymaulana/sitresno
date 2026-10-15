<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ customer: MasterRecord }>()

const fields: MasterField[] = [{ key: "name", label: "Nama pelanggan", placeholder: "Masukkan nama pelanggan" }, { key: "description", label: "Keterangan", placeholder: "Keterangan pelanggan" }]
</script>

<template><MasterForm title="Pelanggan" description="Perbarui data pelanggan." :fields="fields" route-name="customers" :record="customer" /></template>
