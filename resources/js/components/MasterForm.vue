<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3"
import { Save } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import type { MasterField, MasterRecord } from "@/lib/crud"

const props = defineProps<{
  title: string
  description: string
  fields: MasterField[]
  routeName: string
  record?: MasterRecord
}>()

const form = useForm<Record<string, string | number | boolean>>({
  ...Object.fromEntries(props.fields.map(field => [field.key, props.record?.[field.key] ?? ""])),
})

function submit(): void {
  if (props.record) {
    form.put(route(`${props.routeName}.update`, props.record.id))
    return
  }

  form.post(route(`${props.routeName}.store`))
}
</script>

<template>
  <CrudLayout :title="title" :description="description">
    <Card class="border-border/70 shadow-sm">
      <CardHeader>
        <CardTitle>{{ record ? `Ubah ${title}` : `Tambah ${title}` }}</CardTitle>
        <CardDescription>Lengkapi informasi berikut lalu simpan perubahan.</CardDescription>
      </CardHeader>
      <form @submit.prevent="submit">
        <CardContent>
          <FieldGroup class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <Field v-for="field in fields" :key="field.key" :data-invalid="Boolean(form.errors[field.key])">
              <FieldLabel :for="field.key">{{ field.label }}</FieldLabel>
              <Input :id="field.key" v-model="form[field.key] as string" :type="field.type ?? 'text'" :placeholder="field.placeholder" :aria-invalid="Boolean(form.errors[field.key])" />
              <FieldError :errors="[form.errors[field.key]]" />
            </Field>
          </FieldGroup>
        </CardContent>
        <CardFooter class="flex justify-end gap-3">
          <Button variant="outline" as-child><Link :href="route(`${routeName}.index`)">Batal</Link></Button>
          <Button type="submit" :disabled="form.processing"><Save data-icon="inline-start" />{{ form.processing ? "Menyimpan..." : "Simpan" }}</Button>
        </CardFooter>
      </form>
    </Card>
  </CrudLayout>
</template>
