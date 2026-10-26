<script setup lang="ts">
import MasterForm from "@/components/MasterForm.vue"
import type { MasterField, MasterRecord } from "@/lib/crud"

defineProps<{ modelSize: MasterRecord }>()

const fields: MasterField[] = [{ key: "name", label: "Model/Size", placeholder: "Masukkan model/size" }, { key: "description", label: "Keterangan", placeholder: "Keterangan model/size" }]
</script>

<template><MasterForm title="Ukuran Model" description="Perbarui ukuran model." :fields="fields" route-name="model-sizes" :record="modelSize" /></template>
