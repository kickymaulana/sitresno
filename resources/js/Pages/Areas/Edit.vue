<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ area: MasterRecord }>()

const fields: MasterField[] = [{ key: "name", label: "Nama area", placeholder: "Masukkan nama area" }]
</script>

<template><MasterForm title="Area" description="Perbarui informasi area operasional." :fields="fields" route-name="areas" :record="area" /></template>
