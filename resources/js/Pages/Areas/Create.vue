<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField } from "@/lib/crud"

const fields: MasterField[] = [{ key: "name", label: "Nama area", placeholder: "Masukkan nama area" }]
</script>

<template><MasterForm title="Area" description="Tambah area operasional baru." :fields="fields" route-name="areas" /></template>
