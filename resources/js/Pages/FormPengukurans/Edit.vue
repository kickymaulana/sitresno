<script setup lang="ts">
import FormPengukuranForm from "@/components/FormPengukuranForm.vue"
import type { FormPengukuran, SelectOption } from "@/lib/crud"

defineProps<{ formPengukuran: FormPengukuran; areas: SelectOption[]; masterMoulds: SelectOption[]; modelSizes: SelectOption[]; customers: SelectOption[] }>()
</script>

<template><FormPengukuranForm :form-pengukuran="formPengukuran" :areas="areas" :master-moulds="masterMoulds" :model-sizes="modelSizes" :customers="customers" /></template>
