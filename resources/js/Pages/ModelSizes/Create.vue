<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField } from "@/lib/crud"

const fields: MasterField[] = [{ key: "name", label: "Model/Size", placeholder: "Masukkan model/size" }, { key: "description", label: "Keterangan", placeholder: "Keterangan model/size" }]
</script>

<template><MasterForm title="Ukuran Model" description="Tambah ukuran model baru." :fields="fields" route-name="model-sizes" /></template>
