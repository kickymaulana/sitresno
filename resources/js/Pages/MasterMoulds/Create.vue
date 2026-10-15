<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField } from "@/lib/crud"

const fields: MasterField[] = [{ key: "code", label: "Kode mould", placeholder: "Contoh: MLD-001" }, { key: "description", label: "Keterangan", placeholder: "Keterangan mould" }]
</script>

<template><MasterForm title="Master Mould" description="Tambah referensi mould baru." :fields="fields" route-name="master-moulds" /></template>
