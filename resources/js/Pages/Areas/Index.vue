<script setup lang="ts">
import MasterIndex from "@/components/MasterIndex.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ areas: MasterRecord[] }>()

const fields: MasterField[] = [{ key: "name", label: "Nama area" }]
</script>

<template><MasterIndex title="Area" description="Kelola area operasional pengukuran." :records="areas" :fields="fields" route-name="areas" /></template>
