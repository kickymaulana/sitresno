<script setup lang="ts">
import MasterIndex from "@/components/MasterIndex.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ masterMoulds: MasterRecord[] }>()

const fields: MasterField[] = [{ key: "code", label: "Kode mould" }, { key: "description", label: "Keterangan" }]
</script>

<template><MasterIndex title="Master Mould" description="Kelola referensi mould produksi." :records="masterMoulds" :fields="fields" route-name="master-moulds" /></template>
