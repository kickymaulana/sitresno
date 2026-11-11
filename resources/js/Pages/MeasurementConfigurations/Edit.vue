<script setup lang="ts">
import MeasurementConfigurationForm from "@/components/MeasurementConfigurationForm.vue"
import type { SelectOption } from "@/lib/crud"

interface ConfigurationRecord {
  id: number
  customerId: number
  modelSizeId: number
  isActive: boolean
  fields: {
    id?: number
    parameterName: string
    condition: string
    expectedValue: string | null
    description: string
    sortOrder: number
    isActive: boolean
  }[]
}

defineProps<{
  measurementConfiguration: ConfigurationRecord
  customers: SelectOption[]
  modelSizes: SelectOption[]
  measurementConditions: (SelectOption & { code: string })[]
}>()
</script>

<template>
  <MeasurementConfigurationForm :measurement-configuration="measurementConfiguration" :customers="customers" :model-sizes="modelSizes" :measurement-conditions="measurementConditions" />
</template>
