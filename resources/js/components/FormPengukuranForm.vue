<script setup lang="ts">
import { computed, ref, watch } from "vue"
import { Link, useForm } from "@inertiajs/vue3"
import { CircleAlert, FileText, Save } from "@lucide/vue"
import { getDocument, GlobalWorkerOptions } from "pdfjs-dist"
import pdfWorker from "pdfjs-dist/build/pdf.worker.min.mjs?url"
import CrudLayout from "@/components/CrudLayout.vue"
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import type { FormPengukuran, FormPengukuranPayload, MeasurementConfiguration, MeasurementValue, SelectOption } from "@/lib/crud"

const props = defineProps<{
  formPengukuran?: FormPengukuran
  areas: SelectOption[]
  masterMoulds: SelectOption[]
  modelSizes: SelectOption[]
  customers: SelectOption[]
  measurementConditions: (SelectOption & { code: string })[]
  measurementConfigurations: MeasurementConfiguration[]
}>()


const form = useForm<FormPengukuranPayload>({
  areaId: props.formPengukuran?.areaId ?? null,
  masterMouldId: props.formPengukuran?.masterMouldId ?? null,
  customerId: props.formPengukuran?.customerId ?? null,
  hariKe: props.formPengukuran?.hariKe ?? "",
  mesin: props.formPengukuran?.mesin ?? "",
  tanggalOutputSample: props.formPengukuran?.tanggalOutputSample ?? "",
  tanggalPengukuran: props.formPengukuran?.tanggalPengukuran ?? "",
  tanggalNaikMould: props.formPengukuran?.tanggalNaikMould ?? "",
  dibuatOleh: props.formPengukuran?.dibuatOleh ?? "",
  diperiksaOleh: props.formPengukuran?.diperiksaOleh ?? "",
  status: props.formPengukuran?.status ?? "draft",
  items: [{
    modelSizeId: props.formPengukuran?.items?.[0]?.modelSizeId ?? null,
    remarks: props.formPengukuran?.items?.[0]?.remarks ?? "",
    actuals: props.formPengukuran?.items?.[0]?.actuals.map(actual => ({
      configurationFieldId: actual.configurationFieldId,
      parameterName: actual.parameterName,
      condition: actual.condition,
      actual: actual.actual,
    })) ?? [],
  }],
})

const selectedConfiguration = computed(() => props.measurementConfigurations.find(configuration => (
  configuration.customerId === form.customerId && configuration.modelSizeId === form.items[0].modelSizeId
)))

const selectedFields = computed(() => selectedConfiguration.value?.fields ?? [])
const groupedFields = computed(() => props.measurementConditions.map(condition => ({
  condition: condition.code,
  label: condition.name,
  fields: selectedFields.value.filter(field => field.condition === condition.code),
})).filter(group => group.fields.length > 0))

GlobalWorkerOptions.workerSrc = pdfWorker

interface PdfImportPreview {
  fieldId: number
  parameterName: string
  pdfParameterName: string
  value: string
  status: "matched" | "missing"
}

const pdfFile = ref<File | null>(null)
const pdfImporting = ref(false)
const pdfImportError = ref<string | null>(null)
const pdfImportPreview = ref<PdfImportPreview[]>([])
const pdfImportWarnings = ref<string[]>([])

const afterBakeFields = computed(() => selectedFields.value.filter(field => field.condition === "setelah_bakar"))

function normalizePdfText(value: string): string {
  return value.replace(/\s+/g, " ").trim().toLocaleLowerCase()
}

interface PdfTextItem {
  x: number
  text: string
}

interface PdfTextRow {
  items: PdfTextItem[]
}

function textItemsByLine(items: any[]): PdfTextRow[] {
  const lines = new Map<number, PdfTextItem[]>()

  items.forEach(item => {
    if (!("str" in item) || !item.str.trim()) {
      return
    }

    const y = Math.round(item.transform[5] / 3) * 3
    const line = lines.get(y) ?? []
    line.push({ x: item.transform[4], text: item.str.trim() })
    lines.set(y, line)
  })

  return [...lines.values()].map(items => ({ items: items.sort((a, b) => a.x - b.x) }))
}

async function extractPdfRows(file: File): Promise<PdfTextRow[]> {
  const loadingTask = getDocument({ data: new Uint8Array(await file.arrayBuffer()) })
  const pdf = await loadingTask.promise
  const rows: string[][] = []

  try {
    for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
      const page = await pdf.getPage(pageNumber)
      const content = await page.getTextContent()
      rows.push(...textItemsByLine(content.items))
    }

    return rows
  } finally {
    await loadingTask.destroy()
  }
}

async function previewPdfImport(): Promise<void> {
  if (!pdfFile.value) {
    return
  }

  pdfImporting.value = true
  pdfImportError.value = null
  pdfImportWarnings.value = []
  pdfImportPreview.value = []

  try {
    const rows = await extractPdfRows(pdfFile.value)
    const headerRow = rows.find(row => row.items.some(item => normalizePdfText(item.text) === "meas"))

    if (!headerRow) {
      throw new Error("Kolom Meas tidak ditemukan di PDF.")
    }

    const measHeader = headerRow.items.find(item => normalizePdfText(item.text) === "meas")
    const measX = measHeader?.x

    if (measX === undefined) {
      throw new Error("Posisi kolom Meas tidak ditemukan di PDF.")
    }

    const headerIndex = rows.indexOf(headerRow)
    const preview = afterBakeFields.value.map(field => {
      const pdfName = field.pdfParameterName?.trim() ?? ""
      const normalizedPdfName = normalizePdfText(pdfName)
      const row = rows.slice(headerIndex + 1).find(candidate => normalizePdfText(candidate.items.map(item => item.text).join(" ")).includes(normalizedPdfName))
      const valueItem = row?.items.reduce<PdfTextItem | undefined>((closest, item) => {
        if (!closest || Math.abs(item.x - measX) < Math.abs(closest.x - measX)) {
          return item
        }

        return closest
      }, undefined)
      const value = valueItem?.text ?? ""

      return {
        fieldId: field.id,
        parameterName: field.parameterName,
        pdfParameterName: pdfName,
        value,
        status: pdfName && value ? "matched" : "missing",
      }
    })

    pdfImportPreview.value = preview
    pdfImportWarnings.value = preview.filter(item => item.status === "missing").map(item => item.pdfParameterName ? `${item.parameterName}: nama PDF "${item.pdfParameterName}" tidak ditemukan atau nilai Meas kosong.` : `${item.parameterName}: mapping nama PDF belum diisi.`)
  } catch (error) {
    pdfImportError.value = error instanceof Error ? error.message : "PDF tidak dapat dibaca."
  } finally {
    pdfImporting.value = false
  }
}

function applyPdfImport(): void {
  const values = new Map(pdfImportPreview.value.filter(item => item.status === "matched").map(item => [item.fieldId, item.value]))
  form.items[0].actuals.forEach(actual => {
    if (actual.condition === "setelah_bakar" && actual.configurationFieldId && values.has(actual.configurationFieldId)) {
      actual.actual = values.get(actual.configurationFieldId) ?? ""
    }
  })
}

function rebuildActuals(): void {
  const existingActuals = new Map(form.items[0].actuals.map(actual => [actual.configurationFieldId, actual.actual]))
  form.items[0].actuals = selectedFields.value.map((field): MeasurementValue => ({
    configurationFieldId: field.id,
    parameterName: field.parameterName,
    condition: field.condition,
    actual: existingActuals.get(field.id) ?? "",
  }))
}

watch(() => [form.customerId, form.items[0].modelSizeId], rebuildActuals)

function actualFor(configurationFieldId: number): MeasurementValue {
  return form.items[0].actuals.find(actual => actual.configurationFieldId === configurationFieldId)!
}

function fieldError(path: string): string | undefined {
  return form.errors[path]
}

function submit(): void {
  if (!selectedConfiguration.value) {
    return
  }

  if (props.formPengukuran) {
    form.put(route("form-pengukurans.update", props.formPengukuran.id))
    return
  }

  form.post(route("form-pengukurans.store"))
}
</script>

<template>
  <CrudLayout title="Form Pengukuran" :description="formPengukuran ? 'Perbarui actual pengukuran.' : 'Catat actual pengukuran berdasarkan konfigurasi admin.'">
    <form class="flex flex-col gap-6" @submit.prevent="submit">
      <Card>
        <CardHeader><CardTitle>Informasi Form Harian</CardTitle><CardDescription>Pilih Customer dan Model/Size untuk memuat field konfigurasi admin.</CardDescription></CardHeader>
        <CardContent>
          <FieldGroup class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
            <Field :data-invalid="Boolean(form.errors.areaId)"><FieldLabel>Area</FieldLabel><Select v-model="form.areaId"><SelectTrigger :aria-invalid="Boolean(form.errors.areaId)"><SelectValue placeholder="Pilih area" /></SelectTrigger><SelectContent><SelectGroup><SelectItem v-for="option in areas" :key="option.id" :value="option.id">{{ option.name }}</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[form.errors.areaId]" /></Field>
            <Field :data-invalid="Boolean(form.errors.masterMouldId)"><FieldLabel>Master mould</FieldLabel><Select v-model="form.masterMouldId"><SelectTrigger :aria-invalid="Boolean(form.errors.masterMouldId)"><SelectValue placeholder="Pilih mould" /></SelectTrigger><SelectContent><SelectGroup><SelectItem v-for="option in masterMoulds" :key="option.id" :value="option.id">{{ option.name }}</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[form.errors.masterMouldId]" /></Field>
            <Field :data-invalid="Boolean(form.errors.customerId)"><FieldLabel>Customer</FieldLabel><Select v-model="form.customerId"><SelectTrigger :aria-invalid="Boolean(form.errors.customerId)"><SelectValue placeholder="Pilih customer" /></SelectTrigger><SelectContent><SelectGroup><SelectItem v-for="option in customers" :key="option.id" :value="option.id">{{ option.name }}</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[form.errors.customerId]" /></Field>
            <Field :data-invalid="Boolean(form.errors.tanggalPengukuran)"><FieldLabel for="tanggal-pengukuran">Tanggal pengukuran</FieldLabel><Input id="tanggal-pengukuran" v-model="form.tanggalPengukuran" type="date" :aria-invalid="Boolean(form.errors.tanggalPengukuran)" /><FieldError :errors="[form.errors.tanggalPengukuran]" /></Field>
            <Field><FieldLabel>Status</FieldLabel><Select v-model="form.status"><SelectTrigger><SelectValue /></SelectTrigger><SelectContent><SelectGroup><SelectItem value="draft">Draft</SelectItem><SelectItem value="menunggu_pemeriksaan">Menunggu pemeriksaan</SelectItem><SelectItem value="disetujui">Disetujui</SelectItem><SelectItem value="ditolak">Ditolak</SelectItem></SelectGroup></SelectContent></Select></Field>
            <Field><FieldLabel for="hari-ke">Hari ke</FieldLabel><Input id="hari-ke" v-model="form.hariKe" /></Field>
            <Field><FieldLabel for="mesin">Mesin</FieldLabel><Input id="mesin" v-model="form.mesin" /></Field>
            <Field><FieldLabel for="tanggal-output">Tanggal output sample</FieldLabel><Input id="tanggal-output" v-model="form.tanggalOutputSample" type="date" /></Field>
            <Field><FieldLabel for="tanggal-naik">Tanggal naik mould</FieldLabel><Input id="tanggal-naik" v-model="form.tanggalNaikMould" type="date" /></Field>
            <Field><FieldLabel for="dibuat-oleh">Dibuat oleh</FieldLabel><Input id="dibuat-oleh" v-model="form.dibuatOleh" /></Field>
            <Field><FieldLabel for="diperiksa-oleh">Diperiksa oleh</FieldLabel><Input id="diperiksa-oleh" v-model="form.diperiksaOleh" /></Field>
            <Field :data-invalid="Boolean(fieldError('items.0.modelSizeId'))"><FieldLabel>Model/Size</FieldLabel><Select v-model="form.items[0].modelSizeId"><SelectTrigger :aria-invalid="Boolean(fieldError('items.0.modelSizeId'))"><SelectValue placeholder="Pilih Model/Size" /></SelectTrigger><SelectContent><SelectGroup><SelectItem v-for="option in modelSizes" :key="option.id" :value="option.id">{{ option.name }}</SelectItem></SelectGroup></SelectContent></Select><FieldError :errors="[fieldError('items.0.modelSizeId')]" /></Field>
          </FieldGroup>
        </CardContent>
      </Card>

      <Alert v-if="form.customerId && form.items[0].modelSizeId && !selectedConfiguration" variant="destructive"><CircleAlert /><AlertTitle>Konfigurasi belum tersedia</AlertTitle><AlertDescription>Admin belum membuat konfigurasi aktif untuk kombinasi Customer dan Model/Size ini. Form tidak dapat disimpan.</AlertDescription></Alert>

      <Card>
        <CardHeader><CardTitle>Hasil Pengukuran</CardTitle><CardDescription>Parameter, tahap, nilai harapan, dan keterangan berasal dari konfigurasi admin. Petugas hanya mengisi actual.</CardDescription></CardHeader>
        <CardContent class="flex flex-col gap-6">
          <Field><FieldLabel for="remarks">Catatan form (opsional)</FieldLabel><Input id="remarks" v-model="form.items[0].remarks as string" /></Field>
          <div v-if="afterBakeFields.length > 0" class="flex flex-col gap-4 rounded-lg border bg-muted/30 p-4">
            <div class="flex items-center gap-2"><FileText class="size-4" /><div><h3 class="font-semibold">Import hasil Setelah Bakar dari PDF</h3><p class="text-sm text-muted-foreground">PDF dibaca di browser. Pastikan mapping nama PDF sudah diatur pada konfigurasi.</p></div></div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end"><Field class="flex-1"><FieldLabel for="hasil-bakar-pdf">File PDF</FieldLabel><Input id="hasil-bakar-pdf" type="file" accept="application/pdf" @change="pdfFile = ($event.target as HTMLInputElement).files?.[0] ?? null; pdfImportPreview = []; pdfImportWarnings = []; pdfImportError = null" /></Field><Button type="button" variant="outline" :disabled="!pdfFile || pdfImporting" @click="previewPdfImport">{{ pdfImporting ? 'Membaca PDF...' : 'Preview PDF' }}</Button></div>
            <p v-if="pdfImportError" class="text-sm text-destructive">{{ pdfImportError }}</p>
            <div v-if="pdfImportPreview.length > 0" class="flex flex-col gap-3"><div class="overflow-x-auto rounded-lg border"><Table><TableHeader><TableRow><TableHead>Parameter</TableHead><TableHead>Nama PDF</TableHead><TableHead>Meas</TableHead><TableHead>Status</TableHead></TableRow></TableHeader><TableBody><TableRow v-for="item in pdfImportPreview" :key="item.fieldId"><TableCell>{{ item.parameterName }}</TableCell><TableCell>{{ item.pdfParameterName || '-' }}</TableCell><TableCell>{{ item.value || '-' }}</TableCell><TableCell :class="item.status === 'matched' ? 'text-green-600' : 'text-destructive'">{{ item.status === 'matched' ? 'Ditemukan' : 'Tidak ditemukan' }}</TableCell></TableRow></TableBody></Table></div><ul v-if="pdfImportWarnings.length > 0" class="list-disc pl-5 text-sm text-destructive"><li v-for="warning in pdfImportWarnings" :key="warning">{{ warning }}</li></ul><Button type="button" :disabled="pdfImportPreview.every(item => item.status === 'missing')" @click="applyPdfImport">Terapkan hasil yang ditemukan</Button></div>
          </div>
          <div v-for="group in groupedFields" :key="group.condition" class="flex flex-col gap-3">
            <h3 class="font-semibold">{{ group.label }}</h3>
            <div class="overflow-x-auto rounded-lg border"><Table><TableHeader><TableRow><TableHead class="min-w-56">Parameter</TableHead><TableHead>Condition</TableHead><TableHead>Keterangan</TableHead><TableHead class="w-40">Nilai harapan</TableHead><TableHead class="w-40">Actual</TableHead></TableRow></TableHeader><TableBody><TableRow v-for="field in group.fields" :key="field.id"><TableCell class="whitespace-normal font-medium">{{ field.label }}</TableCell><TableCell class="whitespace-normal">{{ field.condition }}</TableCell><TableCell class="whitespace-normal text-muted-foreground">{{ field.description ?? '-' }}</TableCell><TableCell class="font-mono">{{ field.expectedValue ?? '-' }}</TableCell><TableCell><Input v-model="actualFor(field.id).actual as string" type="number" step="any" inputmode="decimal" :aria-invalid="Boolean(fieldError(`items.0.actuals.${form.items[0].actuals.findIndex(actual => actual.configurationFieldId === field.id)}.actual`))" /><FieldError :errors="[fieldError(`items.0.actuals.${form.items[0].actuals.findIndex(actual => actual.configurationFieldId === field.id)}.actual`)]" /></TableCell></TableRow></TableBody></Table></div>
          </div>
          <FieldError :errors="[fieldError('items.0.actuals')]" />
        </CardContent>
      </Card>

      <Card><CardHeader><CardTitle>Simpan Form</CardTitle><CardDescription>Konfigurasi admin akan disalin sebagai snapshot pada actual yang tersimpan.</CardDescription></CardHeader><CardContent v-if="form.errors.items" class="text-sm text-destructive">{{ form.errors.items }}</CardContent><CardFooter class="justify-end gap-3"><Button variant="outline" as-child><Link :href="route('form-pengukurans.index')">Batal</Link></Button><Button type="submit" :disabled="form.processing || !selectedConfiguration"><Save data-icon="inline-start" />{{ form.processing ? 'Menyimpan...' : 'Simpan form' }}</Button></CardFooter></Card>
    </form>
  </CrudLayout>
</template>
