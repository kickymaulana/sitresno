<script setup lang="ts">
import MasterIndex from "@/components/MasterIndex.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ customers: MasterRecord[] }>()

const fields: MasterField[] = [{ key: "name", label: "Nama pelanggan" }, { key: "description", label: "Keterangan" }]
</script>

<template><MasterIndex title="Pelanggan" description="Kelola data pelanggan SITRESNO." :records="customers" :fields="fields" route-name="customers" /></template>
