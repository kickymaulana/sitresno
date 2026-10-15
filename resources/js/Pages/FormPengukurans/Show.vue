<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { Pencil } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Separator } from "@/components/ui/separator"
import type { FormPengukuran, MeasurementCondition, MeasurementKey, MeasurementResult } from "@/lib/crud"

const props = defineProps<{ formPengukuran: FormPengukuran }>()

const conditions: { value: MeasurementCondition; label: string }[] = [
  { value: "sebelum_cuci", label: "Sebelum Cuci" },
  { value: "sesudah_cuci", label: "Sesudah Cuci" },
  { value: "setelah_bakar", label: "Setelah Bakar" },
]

const measurementFields: { key: MeasurementKey; label: string }[] = [
  { key: "h", label: "H" }, { key: "l", label: "L" }, { key: "pc_10mm", label: "PC 10 mm" }, { key: "pc_middle", label: "PC Tengah" }, { key: "upper_palm_width", label: "Lebar Telapak Atas" }, { key: "lower_palm_width", label: "Lebar Telapak Bawah" }, { key: "wc", label: "WC" }, { key: "sl_titik", label: "SL Titik" }, { key: "fl_1", label: "FL 1" }, { key: "fl_2", label: "FL 2" }, { key: "fl_3", label: "FL 3" }, { key: "fl_4", label: "FL 4" }, { key: "fl_5", label: "FL 5" }, { key: "fc_1", label: "FC 1" }, { key: "fc_2", label: "FC 2" }, { key: "fc_3", label: "FC 3" }, { key: "fc_4", label: "FC 4" }, { key: "fc_5", label: "FC 5" }, { key: "fc_internal_1", label: "FC Internal 1" }, { key: "fc_internal_2", label: "FC Internal 2" }, { key: "fc_internal_3", label: "FC Internal 3" }, { key: "fc_internal_4", label: "FC Internal 4" }, { key: "fc_internal_5", label: "FC Internal 5" }, { key: "lt", label: "LT" }, { key: "d1", label: "D1" }, { key: "d2", label: "D2" }, { key: "pt", label: "PT" }, { key: "span", label: "Span" }, { key: "kl", label: "KL" }, { key: "tt", label: "TT" }, { key: "prbt_t", label: "PRBT T" }, { key: "m", label: "M" }, { key: "beading_267", label: "Beading 267" }, { key: "beading_222", label: "Beading 222" }, { key: "tb", label: "TB" }, { key: "w", label: "W" },
]

const statusLabels = {
  draft: "Draft",
  menunggu_pemeriksaan: "Menunggu pemeriksaan",
  disetujui: "Disetujui",
  ditolak: "Ditolak",
} as const

function resultFor(condition: MeasurementCondition): MeasurementResult | undefined {
  return props.formPengukuran.results?.find(result => result.condition === condition)
}
</script>

<template>
  <CrudLayout title="Detail Pengukuran" description="Rincian metadata dan hasil pengukuran.">
    <Card class="border-border/70 shadow-sm">
      <CardHeader><div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div class="flex flex-col gap-1"><CardTitle>Form Pengukuran #{{ formPengukuran.id }}</CardTitle><CardDescription>Tanggal pengukuran: {{ formPengukuran.tanggalPengukuran ?? '-' }}</CardDescription></div><Button as-child><Link :href="route('form-pengukurans.edit', formPengukuran.id)"><Pencil data-icon="inline-start" />Ubah form</Link></Button></div></CardHeader>
      <CardContent class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Area</span><span class="font-medium">{{ formPengukuran.area?.name ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Master mould</span><span class="font-medium">{{ formPengukuran.masterMould?.name ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Ukuran model</span><span class="font-medium">{{ formPengukuran.modelSize?.name ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Pelanggan</span><span class="font-medium">{{ formPengukuran.customer?.name ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Hari ke</span><span class="font-medium">{{ formPengukuran.hariKe ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Mesin</span><span class="font-medium">{{ formPengukuran.mesin ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Tanggal output sample</span><span class="font-medium">{{ formPengukuran.tanggalOutputSample ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Tanggal naik mould</span><span class="font-medium">{{ formPengukuran.tanggalNaikMould ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Dibuat oleh</span><span class="font-medium">{{ formPengukuran.dibuatOleh ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Diperiksa oleh</span><span class="font-medium">{{ formPengukuran.diperiksaOleh ?? '-' }}</span></div><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Status</span><Badge :variant="formPengukuran.status === 'disetujui' ? 'default' : 'secondary'" class="w-fit">{{ statusLabels[formPengukuran.status] }}</Badge></div></CardContent>
      <CardFooter><Button variant="outline" as-child><Link :href="route('form-pengukurans.index')">Kembali ke daftar</Link></Button></CardFooter>
    </Card>
    <div class="grid grid-cols-1 gap-6">
      <Card v-for="condition in conditions" :key="condition.value" class="border-border/70 shadow-sm">
        <CardHeader><CardTitle>{{ condition.label }}</CardTitle><CardDescription>Hasil kondisi {{ condition.label.toLowerCase() }}.</CardDescription></CardHeader>
        <CardContent><div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"><div v-for="field in measurementFields" :key="field.key" class="flex items-center justify-between gap-4"><span class="text-sm text-muted-foreground">{{ field.label }}</span><span class="font-mono font-medium">{{ resultFor(condition.value)?.[field.key] ?? '-' }}</span></div></div><Separator class="my-5" /><div class="flex flex-col gap-1"><span class="text-sm text-muted-foreground">Catatan</span><span class="text-sm font-medium">{{ resultFor(condition.value)?.remarks ?? '-' }}</span></div></CardContent>
        <CardFooter class="text-xs text-muted-foreground">Data hasil pengukuran</CardFooter>
      </Card>
    </div>
  </CrudLayout>
</template>
