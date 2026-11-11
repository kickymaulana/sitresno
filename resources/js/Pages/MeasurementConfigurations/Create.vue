<script setup lang="ts">
import MeasurementConfigurationForm from "@/components/MeasurementConfigurationForm.vue"
import type { SelectOption } from "@/lib/crud"

defineProps<{
  customers: SelectOption[]
  modelSizes: SelectOption[]
  measurementConditions: (SelectOption & { code: string })[]
  copyableConfigurations: {
    id: number
    customer: string
    modelSize: string
    fields: {
      parameterName: string
      condition: string
      expectedValue: string | null
      description: string
      sortOrder: number
      isActive: boolean
    }[]
  }[]
}>()
</script>

<template>
  <MeasurementConfigurationForm :customers="customers" :model-sizes="modelSizes" :measurement-conditions="measurementConditions" :copyable-configurations="copyableConfigurations" />
</template>
