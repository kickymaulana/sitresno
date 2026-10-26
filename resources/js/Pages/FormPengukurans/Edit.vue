<script setup lang="ts">
import FormPengukuranForm from "@/components/FormPengukuranForm.vue"
import type { FormPengukuran, MeasurementConfiguration, SelectOption } from "@/lib/crud"

defineProps<{
  formPengukuran: FormPengukuran
  areas: SelectOption[]
  masterMoulds: SelectOption[]
  modelSizes: SelectOption[]
  customers: SelectOption[]
  measurementConditions: (SelectOption & { code: string })[]
  measurementConfigurations: MeasurementConfiguration[]
}>()
</script>

<template>
  <FormPengukuranForm :form-pengukuran="formPengukuran" :areas="areas" :master-moulds="masterMoulds" :model-sizes="modelSizes" :customers="customers" :measurement-conditions="measurementConditions" :measurement-configurations="measurementConfigurations" />
</template>
