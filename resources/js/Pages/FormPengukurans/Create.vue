<script setup lang="ts">
import FormPengukuranForm from "@/components/FormPengukuranForm.vue"
import type { MeasurementConfiguration, SelectOption } from "@/lib/crud"

defineProps<{
  areas: SelectOption[]
  masterMoulds: SelectOption[]
  modelSizes: SelectOption[]
  customers: SelectOption[]
  measurementConditions: (SelectOption & { code: string })[]
  measurementConfigurations: MeasurementConfiguration[]
}>()
</script>

<template>
  <FormPengukuranForm :areas="areas" :master-moulds="masterMoulds" :model-sizes="modelSizes" :customers="customers" :measurement-conditions="measurementConditions" :measurement-configurations="measurementConfigurations" />
</template>
