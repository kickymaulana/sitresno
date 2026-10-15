<script setup lang="ts">
import FormPengukuranForm from "@/components/FormPengukuranForm.vue"
import type { SelectOption } from "@/lib/crud"

defineProps<{ areas: SelectOption[]; masterMoulds: SelectOption[]; modelSizes: SelectOption[]; customers: SelectOption[] }>()
</script>

<template><FormPengukuranForm :areas="areas" :master-moulds="masterMoulds" :model-sizes="modelSizes" :customers="customers" /></template>
