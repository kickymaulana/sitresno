<script setup lang="ts">
import { Link, useForm } from "@inertiajs/vue3"
import { Save } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import type { FormPengukuran, FormPengukuranPayload, MeasurementCondition, MeasurementKey, MeasurementResultPayload, SelectOption } from "@/lib/crud"

const props = defineProps<{
  formPengukuran?: FormPengukuran
  areas: SelectOption[]
  masterMoulds: SelectOption[]
  modelSizes: SelectOption[]
  customers: SelectOption[]
}>()

const conditions: { value: MeasurementCondition; label: string }[] = [
  { value: "sebelum_cuci", label: "Sebelum Cuci" },
  { value: "sesudah_cuci", label: "Sesudah Cuci" },
  { value: "setelah_bakar", label: "Setelah Bakar" },
]

const measurementFields: { key: MeasurementKey; label: string }[] = [
  { key: "h", label: "H" }, { key: "l", label: "L" }, { key: "pc_10mm", label: "PC 10 mm" }, { key: "pc_middle", label: "PC Tengah" }, { key: "upper_palm_width", label: "Lebar Telapak Atas" }, { key: "lower_palm_width", label: "Lebar Telapak Bawah" }, { key: "wc", label: "WC" }, { key: "sl_titik", label: "SL Titik" }, { key: "fl_1", label: "FL 1" }, { key: "fl_2", label: "FL 2" }, { key: "fl_3", label: "FL 3" }, { key: "fl_4", label: "FL 4" }, { key: "fl_5", label: "FL 5" }, { key: "fc_1", label: "FC 1" }, { key: "fc_2", label: "FC 2" }, { key: "fc_3", label: "FC 3" }, { key: "fc_4", label: "FC 4" }, { key: "fc_5", label: "FC 5" }, { key: "fc_internal_1", label: "FC Internal 1" }, { key: "fc_internal_2", label: "FC Internal 2" }, { key: "fc_internal_3", label: "FC Internal 3" }, { key: "fc_internal_4", label: "FC Internal 4" }, { key: "fc_internal_5", label: "FC Internal 5" }, { key: "lt", label: "LT" }, { key: "d1", label: "D1" }, { key: "d2", label: "D2" }, { key: "pt", label: "PT" }, { key: "span", label: "Span" }, { key: "kl", label: "KL" }, { key: "tt", label: "TT" }, { key: "prbt_t", label: "PRBT T" }, { key: "m", label: "M" }, { key: "beading_267", label: "Beading 267" }, { key: "beading_222", label: "Beading 222" }, { key: "tb", label: "TB" }, { key: "w", label: "W" },
]

function emptyResult(condition: MeasurementCondition): MeasurementResultPayload {
  return {
    condition,
    remarks: "",
    ...Object.fromEntries(measurementFields.map(({ key }) => [key, ""])) as Record<MeasurementKey, string>,
  }
}

function resultFor(condition: MeasurementCondition): MeasurementResultPayload {
  const result = props.formPengukuran?.results?.find(item => item.condition === condition)

  if (!result) {
    return emptyResult(condition)
  }

  return {
    condition,
    remarks: result.remarks ?? "",
    ...Object.fromEntries(measurementFields.map(({ key }) => [key, result[key] ?? ""])) as Record<MeasurementKey, string | number>,
  }
}

const form = useForm<FormPengukuranPayload>({
  areaId: props.formPengukuran?.areaId ?? null,
  masterMouldId: props.formPengukuran?.masterMouldId ?? null,
  modelSizeId: props.formPengukuran?.modelSizeId ?? null,
  customerId: props.formPengukuran?.customerId ?? null,
  hariKe: props.formPengukuran?.hariKe ?? "",
  mesin: props.formPengukuran?.mesin ?? "",
  tanggalOutputSample: props.formPengukuran?.tanggalOutputSample ?? "",
  tanggalPengukuran: props.formPengukuran?.tanggalPengukuran ?? "",
  tanggalNaikMould: props.formPengukuran?.tanggalNaikMould ?? "",
  dibuatOleh: props.formPengukuran?.dibuatOleh ?? "",
  diperiksaOleh: props.formPengukuran?.diperiksaOleh ?? "",
  status: props.formPengukuran?.status ?? "draft",
  results: conditions.map(condition => resultFor(condition.value)),
})

function resultError(index: number, key: MeasurementKey | "remarks"): string | undefined {
  return form.errors[`results.${index}.${key}`]
}

function submit(): void {
  if (props.formPengukuran) {
    form.put(route("form-pengukurans.update", props.formPengukuran.id))
    return
  }

  form.post(route("form-pengukurans.store"))
}
</script>

<template>
  <CrudLayout title="Form Pengukuran" :description="formPengukuran ? 'Perbarui hasil pengukuran produk.' : 'Catat hasil pengukuran produk.'">
    <form class="flex flex-col gap-6" @submit.prevent="submit">
      <Card class="border-border/70 shadow-sm">
        <CardHeader><CardTitle>Informasi Form</CardTitle><CardDescription>Lengkapi metadata, referensi produksi, dan status pemeriksaan.</CardDescription></CardHeader>
        <CardContent>
          <FieldGroup class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            <Field :data-invalid="Boolean(form.errors.areaId)"><FieldLabel>Area</FieldLabel><Select v-model="form.areaId"><SelectTrigger aria-invalid="Boolean(form.errors.areaId)"><SelectValue placeholder="Pilih area" /></SelectTrigger><SelectContent><SelectGroup><SelectItem v-for="area in areas" :key="area.id" :value="area.id">{{ area.name }}</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[form.errors.areaId]" /></Field>
            <Field :data-invalid="Boolean(form.errors.masterMouldId)"><FieldLabel>Master mould</FieldLabel><Select v-model="form.masterMouldId"><SelectTrigger aria-invalid="Boolean(form.errors.masterMouldId)"><SelectValue placeholder="Pilih mould" /></SelectTrigger><SelectContent><SelectGroup><SelectItem v-for="mould in masterMoulds" :key="mould.id" :value="mould.id">{{ mould.name }}</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[form.errors.masterMouldId]" /></Field>
            <Field :data-invalid="Boolean(form.errors.modelSizeId)"><FieldLabel>Ukuran model</FieldLabel><Select v-model="form.modelSizeId"><SelectTrigger aria-invalid="Boolean(form.errors.modelSizeId)"><SelectValue placeholder="Pilih ukuran" /></SelectTrigger><SelectContent><SelectGroup><SelectItem v-for="modelSize in modelSizes" :key="modelSize.id" :value="modelSize.id">{{ modelSize.name }}</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[form.errors.modelSizeId]" /></Field>
            <Field :data-invalid="Boolean(form.errors.customerId)"><FieldLabel>Pelanggan</FieldLabel><Select v-model="form.customerId"><SelectTrigger aria-invalid="Boolean(form.errors.customerId)"><SelectValue placeholder="Pilih pelanggan" /></SelectTrigger><SelectContent><SelectGroup><SelectItem v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[form.errors.customerId]" /></Field>
            <Field :data-invalid="Boolean(form.errors.status)"><FieldLabel>Status</FieldLabel><Select v-model="form.status"><SelectTrigger aria-invalid="Boolean(form.errors.status)"><SelectValue placeholder="Pilih status" /></SelectTrigger><SelectContent><SelectGroup><SelectItem value="draft">Draft</SelectItem><SelectItem value="menunggu_pemeriksaan">Menunggu pemeriksaan</SelectItem><SelectItem value="disetujui">Disetujui</SelectItem><SelectItem value="ditolak">Ditolak</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[form.errors.status]" /></Field>
            <Field :data-invalid="Boolean(form.errors.hariKe)"><FieldLabel for="hari-ke">Hari ke</FieldLabel><Input id="hari-ke" v-model="form.hariKe" aria-invalid="Boolean(form.errors.hariKe)" /><FieldError :errors="[form.errors.hariKe]" /></Field>
            <Field :data-invalid="Boolean(form.errors.mesin)"><FieldLabel for="mesin">Mesin</FieldLabel><Input id="mesin" v-model="form.mesin" aria-invalid="Boolean(form.errors.mesin)" /><FieldError :errors="[form.errors.mesin]" /></Field>
            <Field :data-invalid="Boolean(form.errors.tanggalOutputSample)"><FieldLabel for="tanggal-output-sample">Tanggal output sample</FieldLabel><Input id="tanggal-output-sample" v-model="form.tanggalOutputSample" type="date" aria-invalid="Boolean(form.errors.tanggalOutputSample)" /><FieldError :errors="[form.errors.tanggalOutputSample]" /></Field>
            <Field :data-invalid="Boolean(form.errors.tanggalPengukuran)"><FieldLabel for="tanggal-pengukuran">Tanggal pengukuran</FieldLabel><Input id="tanggal-pengukuran" v-model="form.tanggalPengukuran" type="date" aria-invalid="Boolean(form.errors.tanggalPengukuran)" /><FieldError :errors="[form.errors.tanggalPengukuran]" /></Field>
            <Field :data-invalid="Boolean(form.errors.tanggalNaikMould)"><FieldLabel for="tanggal-naik-mould">Tanggal naik mould</FieldLabel><Input id="tanggal-naik-mould" v-model="form.tanggalNaikMould" type="date" aria-invalid="Boolean(form.errors.tanggalNaikMould)" /><FieldError :errors="[form.errors.tanggalNaikMould]" /></Field>
            <Field :data-invalid="Boolean(form.errors.dibuatOleh)"><FieldLabel for="dibuat-oleh">Dibuat oleh</FieldLabel><Input id="dibuat-oleh" v-model="form.dibuatOleh" aria-invalid="Boolean(form.errors.dibuatOleh)" /><FieldError :errors="[form.errors.dibuatOleh]" /></Field>
            <Field :data-invalid="Boolean(form.errors.diperiksaOleh)"><FieldLabel for="diperiksa-oleh">Diperiksa oleh</FieldLabel><Input id="diperiksa-oleh" v-model="form.diperiksaOleh" aria-invalid="Boolean(form.errors.diperiksaOleh)" /><FieldError :errors="[form.errors.diperiksaOleh]" /></Field>
          </FieldGroup>
        </CardContent>
        <CardFooter class="text-sm text-muted-foreground">Tanggal dan keterangan metadata dapat dikosongkan bila belum tersedia.</CardFooter>
      </Card>
      <Card v-for="(condition, index) in conditions" :key="condition.value" class="border-border/70 shadow-sm">
        <CardHeader><CardTitle>{{ condition.label }}</CardTitle><CardDescription>Hasil pengukuran untuk kondisi {{ condition.label.toLowerCase() }}.</CardDescription></CardHeader>
        <CardContent><FieldGroup class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"><Field v-for="item in measurementFields" :key="item.key" :data-invalid="Boolean(resultError(index, item.key))"><FieldLabel :for="`${condition.value}-${item.key}`">{{ item.label }}</FieldLabel><Input :id="`${condition.value}-${item.key}`" v-model="form.results[index][item.key]" type="number" step="any" inputmode="decimal" :aria-invalid="Boolean(resultError(index, item.key))" /><FieldError :errors="[resultError(index, item.key)]" /></Field><Field class="sm:col-span-2 lg:col-span-3 xl:col-span-4" :data-invalid="Boolean(resultError(index, 'remarks'))"><FieldLabel :for="`${condition.value}-remarks`">Catatan</FieldLabel><Input :id="`${condition.value}-remarks`" v-model="form.results[index].remarks" placeholder="Catatan hasil pengukuran" :aria-invalid="Boolean(resultError(index, 'remarks'))" /><FieldError :errors="[resultError(index, 'remarks')]" /></Field></FieldGroup></CardContent>
        <CardFooter class="text-sm text-muted-foreground">Kosongkan nilai pengukuran yang tidak digunakan.</CardFooter>
      </Card>
      <Card class="border-border/70 shadow-sm"><CardHeader><CardTitle>Simpan Form</CardTitle><CardDescription>Periksa kembali tiga kondisi hasil pengukuran sebelum menyimpan.</CardDescription></CardHeader><CardContent v-if="form.errors.results" class="text-sm text-destructive">{{ form.errors.results }}</CardContent><CardFooter class="flex justify-end gap-3"><Button variant="outline" as-child><Link :href="route('form-pengukurans.index')">Batal</Link></Button><Button type="submit" :disabled="form.processing"><Save data-icon="inline-start" />{{ form.processing ? "Menyimpan..." : "Simpan form" }}</Button></CardFooter></Card>
    </form>
  </CrudLayout>
</template>
