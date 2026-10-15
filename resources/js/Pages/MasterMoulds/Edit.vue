<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ masterMould: MasterRecord }>()

const fields: MasterField[] = [{ key: "code", label: "Kode mould", placeholder: "Contoh: MLD-001" }, { key: "description", label: "Keterangan", placeholder: "Keterangan mould" }]
</script>

<template><MasterForm title="Master Mould" description="Perbarui referensi mould." :fields="fields" route-name="master-moulds" :record="masterMould" /></template>
