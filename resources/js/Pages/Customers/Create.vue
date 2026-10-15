<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField } from "@/lib/crud"

const fields: MasterField[] = [{ key: "name", label: "Nama pelanggan", placeholder: "Masukkan nama pelanggan" }, { key: "description", label: "Keterangan", placeholder: "Keterangan pelanggan" }]
</script>

<template><MasterForm title="Pelanggan" description="Tambah pelanggan baru." :fields="fields" route-name="customers" /></template>
