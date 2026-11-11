<script setup lang="ts">
import { computed, ref } from "vue"
import { Link, useForm } from "@inertiajs/vue3"
import { Copy, Plus, Save, Trash2 } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Switch } from "@/components/ui/switch"
import type { SelectOption } from "@/lib/crud"

interface ConfigurationField {
  id?: number
  parameterName: string
  condition: string
  expectedValue: string | number | null
  description: string
  sortOrder: number
  isActive: boolean
}

interface ConfigurationRecord {
  id: number
  customerId: number
  modelSizeId: number
  isActive: boolean
  fields: ConfigurationField[]
}

interface CopyableConfiguration {
  id: number
  customer: string
  modelSize: string
  fields: ConfigurationField[]
}

const props = defineProps<{
  measurementConfiguration?: ConfigurationRecord
  customers: SelectOption[]
  modelSizes: SelectOption[]
  measurementConditions: (SelectOption & { code: string })[]
  copyableConfigurations?: CopyableConfiguration[]
}>()

function newField(sortOrder: number): ConfigurationField {
  return { parameterName: "", condition: props.measurementConditions[0]?.code ?? "", expectedValue: "", description: "", sortOrder, isActive: true }
}

const form = useForm({
  customerId: props.measurementConfiguration?.customerId ?? null,
  modelSizeId: props.measurementConfiguration?.modelSizeId ?? null,
  isActive: props.measurementConfiguration?.isActive ?? true,
  fields: props.measurementConfiguration?.fields.map(field => ({ ...field, parameterName: field.parameterName ?? "", description: field.description ?? "" })) ?? [newField(1)],
})

const selectedCopyId = ref<number | null>(null)
const selectedCopyFieldIds = ref<number[]>([])
const selectedCopyInsertAfter = ref<number>(form.fields.length - 1)
const selectedCopyConfiguration = computed(() => props.copyableConfigurations?.find(configuration => configuration.id === selectedCopyId.value))

function normalizeSortOrders(): void {
  form.fields.forEach((field, index) => { field.sortOrder = index + 1 })
}

function copyFields(): void {
  const source = props.copyableConfigurations?.find(configuration => configuration.id === selectedCopyId.value)

  if (!source) {
    return
  }

  form.fields = source.fields.map(field => ({
    parameterName: field.parameterName,
    condition: field.condition,
    expectedValue: field.expectedValue,
    description: field.description ?? "",
    sortOrder: field.sortOrder,
    isActive: field.isActive,
  }))
  normalizeSortOrders()
  selectedCopyInsertAfter.value = form.fields.length - 1
}

function copySelectedFields(): void {
  const source = selectedCopyConfiguration.value

  if (!source || selectedCopyFieldIds.value.length === 0) {
    return
  }

  const copiedFields = source.fields
    .filter(field => field.id !== undefined && selectedCopyFieldIds.value.includes(field.id))
    .map(field => ({
      parameterName: field.parameterName,
      condition: field.condition,
      expectedValue: field.expectedValue,
      description: field.description ?? "",
      sortOrder: form.fields.length + 1,
      isActive: field.isActive,
    }))

  if (form.fields.length === 1 && !form.fields[0].parameterName) {
    form.fields = []
  }

  const insertIndex = selectedCopyInsertAfter.value + 1
  form.fields.splice(insertIndex, 0, ...copiedFields)
  normalizeSortOrders()
  selectedCopyFieldIds.value = []
  selectedCopyInsertAfter.value = form.fields.length - 1
}

function addField(afterIndex?: number): void {
  const insertIndex = afterIndex === undefined ? form.fields.length : afterIndex + 1
  form.fields.splice(insertIndex, 0, newField(insertIndex + 1))
  normalizeSortOrders()
}

function removeField(index: number): void {
  form.fields.splice(index, 1)
  normalizeSortOrders()
}

function error(path: string): string | undefined {
  return form.errors[path]
}

function submit(): void {
  if (props.measurementConfiguration) {
    form.put(route("measurement-configurations.update", props.measurementConfiguration.id))
    return
  }

  form.post(route("measurement-configurations.store"))
}
</script>

<template>
  <CrudLayout title="Konfigurasi Pengukuran" description="Atur field pengukuran untuk satu kombinasi Customer dan Model/Size.">
    <form class="flex flex-col gap-6" @submit.prevent="submit">
      <Card>
        <CardHeader>
          <CardTitle>Identitas Konfigurasi</CardTitle>
          <CardDescription>Satu kombinasi Customer dan Model/Size hanya dapat memiliki satu konfigurasi.</CardDescription>
        </CardHeader>
        <CardContent>
          <FieldGroup class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <Field :data-invalid="Boolean(form.errors.customerId)">
              <FieldLabel>Customer</FieldLabel>
              <Select v-model="form.customerId">
                <SelectTrigger :aria-invalid="Boolean(form.errors.customerId)"><SelectValue placeholder="Pilih customer" /></SelectTrigger>
                <SelectContent><SelectGroup><SelectItem v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</SelectItem></SelectGroup></SelectContent>
              </Select>
              <FieldError :errors="[form.errors.customerId]" />
            </Field>
            <Field :data-invalid="Boolean(form.errors.modelSizeId)">
              <FieldLabel>Model/Size</FieldLabel>
              <Select v-model="form.modelSizeId">
                <SelectTrigger :aria-invalid="Boolean(form.errors.modelSizeId)"><SelectValue placeholder="Pilih Model/Size" /></SelectTrigger>
                <SelectContent><SelectGroup><SelectItem v-for="modelSize in modelSizes" :key="modelSize.id" :value="modelSize.id">{{ modelSize.name }}</SelectItem></SelectGroup></SelectContent>
              </Select>
              <FieldError :errors="[form.errors.modelSizeId]" />
            </Field>
            <Field orientation="horizontal">
              <FieldLabel for="configuration-active">Konfigurasi aktif</FieldLabel>
              <Switch id="configuration-active" v-model="form.isActive" />
            </Field>
          </FieldGroup>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Field Pengukuran</CardTitle>
          <CardDescription>Ketik nama parameter sendiri. Parameter yang sama boleh digunakan kembali pada tahap berbeda.</CardDescription>
          <div v-if="!measurementConfiguration && copyableConfigurations?.length" class="flex flex-col gap-3 rounded-lg border bg-muted/30 p-4 sm:flex-row sm:items-end">
            <Field class="flex-1">
              <FieldLabel>Salin field dari konfigurasi lain</FieldLabel>
              <Select v-model="selectedCopyId">
                <SelectTrigger><SelectValue placeholder="Pilih konfigurasi sumber" /></SelectTrigger>
                <SelectContent><SelectGroup><SelectItem v-for="configuration in copyableConfigurations" :key="configuration.id" :value="configuration.id">{{ configuration.customer }} - {{ configuration.modelSize }} ({{ configuration.fields.length }} field)</SelectItem></SelectGroup></SelectContent>
              </Select>
            </Field>
            <Button type="button" variant="outline" :disabled="!selectedCopyId" @click="copyFields"><Copy data-icon="inline-start" />Salin semua field</Button>
          </div>
          <div v-if="selectedCopyConfiguration" class="flex flex-col gap-3 rounded-lg border bg-muted/30 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
              <Field class="flex-1">
                <FieldLabel>Posisi hasil salinan</FieldLabel>
                <Select v-model="selectedCopyInsertAfter">
                  <SelectTrigger><SelectValue /></SelectTrigger>
                  <SelectContent><SelectGroup><SelectItem :value="-1">Di awal daftar</SelectItem><SelectItem v-for="(field, index) in form.fields" :key="field.id ?? `insert-after-${index}`" :value="index">Setelah Field {{ index + 1 }}{{ field.parameterName ? `: ${field.parameterName}` : '' }}</SelectItem></SelectGroup></SelectContent>
                </Select>
              </Field>
              <Button type="button" variant="outline" size="sm" :disabled="selectedCopyFieldIds.length === 0" @click="copySelectedFields"><Copy data-icon="inline-start" />Salin yang dipilih</Button>
            </div>
            <p class="text-sm font-medium">Centang field yang ingin disalin</p>
            <label v-for="field in selectedCopyConfiguration.fields" :key="field.id" class="flex items-start gap-3 rounded-md border bg-background p-3 text-sm">
              <input v-model="selectedCopyFieldIds" type="checkbox" :value="field.id" class="mt-0.5 size-4 accent-primary" />
              <span><span class="font-medium">{{ field.parameterName }}</span><span class="ml-2 text-muted-foreground">{{ field.description || 'Tanpa keterangan' }} - {{ field.expectedValue ?? '-' }}</span></span>
            </label>
          </div>
        </CardHeader>
        <CardContent class="flex flex-col gap-5">
          <FieldError :errors="[form.errors.fields]" />
          <Card v-for="(field, index) in form.fields" :key="field.id ?? `new-${index}`">
            <CardHeader class="flex-row items-center justify-between">
              <CardTitle class="text-base">Field {{ index + 1 }}</CardTitle>
              <div class="flex gap-2"><Button type="button" variant="outline" size="sm" @click="addField(index)"><Plus data-icon="inline-start" />Tambah setelah ini</Button><Button type="button" variant="outline" size="sm" :disabled="form.fields.length === 1" @click="removeField(index)"><Trash2 data-icon="inline-start" />Hapus</Button></div>
            </CardHeader>
            <CardContent>
              <FieldGroup class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                <Field :data-invalid="Boolean(error(`fields.${index}.parameterName`))">
                  <FieldLabel :for="`parameter-${index}`">Nama Parameter</FieldLabel>
                  <Input :id="`parameter-${index}`" v-model="field.parameterName" placeholder="Contoh: BEADING" :aria-invalid="Boolean(error(`fields.${index}.parameterName`))" />
                  <FieldError :errors="[error(`fields.${index}.parameterName`)]" />
                </Field>
                <Field :data-invalid="Boolean(error(`fields.${index}.condition`))">
                  <FieldLabel>Tahap</FieldLabel>
                  <Select v-model="field.condition">
                    <SelectTrigger :aria-invalid="Boolean(error(`fields.${index}.condition`))"><SelectValue /></SelectTrigger>
                    <SelectContent><SelectGroup><SelectItem v-for="condition in measurementConditions" :key="condition.id" :value="condition.code">{{ condition.name }}</SelectItem></SelectGroup></SelectContent>
                  </Select>
                  <FieldError :errors="[error(`fields.${index}.condition`)]" />
                </Field>
                <Field :data-invalid="Boolean(error(`fields.${index}.expectedValue`))">
                  <FieldLabel :for="`expected-${index}`">Nilai harapan</FieldLabel>
                  <Input :id="`expected-${index}`" v-model="field.expectedValue as string" type="text" :aria-invalid="Boolean(error(`fields.${index}.expectedValue`))" />
                  <FieldError :errors="[error(`fields.${index}.expectedValue`)]" />
                </Field>
                <Field>
                  <FieldLabel :for="`description-${index}`">Keterangan</FieldLabel>
                  <Input :id="`description-${index}`" v-model="field.description" />
                </Field>
                <Field :data-invalid="Boolean(error(`fields.${index}.sortOrder`))">
                  <FieldLabel :for="`sort-${index}`">Urutan</FieldLabel>
                  <Input :id="`sort-${index}`" v-model.number="field.sortOrder" type="number" min="1" :aria-invalid="Boolean(error(`fields.${index}.sortOrder`))" />
                  <FieldError :errors="[error(`fields.${index}.sortOrder`)]" />
                </Field>
                <Field orientation="horizontal">
                  <FieldLabel :for="`active-${index}`">Aktif</FieldLabel>
                  <Switch :id="`active-${index}`" v-model="field.isActive" />
                </Field>
              </FieldGroup>
            </CardContent>
          </Card>
          <Button type="button" variant="outline" @click="addField"><Plus data-icon="inline-start" />Tambah field</Button>
        </CardContent>
        <CardFooter class="justify-end gap-3">
          <Button variant="outline" as-child><Link :href="route('measurement-configurations.index')">Batal</Link></Button>
          <Button type="submit" :disabled="form.processing"><Save data-icon="inline-start" />{{ form.processing ? "Menyimpan..." : "Simpan konfigurasi" }}</Button>
        </CardFooter>
      </Card>
    </form>
  </CrudLayout>
</template>
