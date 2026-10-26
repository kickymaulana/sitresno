<script setup lang="ts">
import { Link } from "@inertiajs/vue3"
import { Pencil, Printer } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import type { FormPengukuran, SelectOption } from "@/lib/crud"

const props = defineProps<{ formPengukuran: FormPengukuran; measurementConditions: (SelectOption & { code: string })[] }>()
const statusLabels = { draft: "Draft", menunggu_pemeriksaan: "Menunggu pemeriksaan", disetujui: "Disetujui", ditolak: "Ditolak" } as const
</script>

<template>
  <CrudLayout title="Detail Pengukuran" description="Rincian form, spesifikasi, dan aktual per model/size.">
    <Card><CardHeader><div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div><CardTitle>Form Pengukuran #{{ formPengukuran.id }}</CardTitle><CardDescription>{{ formPengukuran.customer?.name }} - {{ formPengukuran.tanggalPengukuran }}</CardDescription></div><div class="flex gap-2"><Button variant="outline" as-child><a :href="route('form-pengukurans.print', formPengukuran.id)" target="_blank"><Printer data-icon="inline-start" />Print / PDF</a></Button><Button as-child><Link :href="route('form-pengukurans.edit', formPengukuran.id)"><Pencil data-icon="inline-start" />Ubah</Link></Button></div></div></CardHeader><CardContent class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4"><div><span class="text-sm text-muted-foreground">Area</span><p class="font-medium">{{ formPengukuran.area?.name ?? '-' }}</p></div><div><span class="text-sm text-muted-foreground">Master mould</span><p class="font-medium">{{ formPengukuran.masterMould?.name ?? '-' }}</p></div><div><span class="text-sm text-muted-foreground">Mesin</span><p class="font-medium">{{ formPengukuran.mesin ?? '-' }}</p></div><div><span class="text-sm text-muted-foreground">Hari ke</span><p class="font-medium">{{ formPengukuran.hariKe ?? '-' }}</p></div><div><span class="text-sm text-muted-foreground">Output sample</span><p class="font-medium">{{ formPengukuran.tanggalOutputSample ?? '-' }}</p></div><div><span class="text-sm text-muted-foreground">Naik mould</span><p class="font-medium">{{ formPengukuran.tanggalNaikMould ?? '-' }}</p></div><div><span class="text-sm text-muted-foreground">Dibuat oleh</span><p class="font-medium">{{ formPengukuran.dibuatOleh ?? '-' }}</p></div><div><span class="text-sm text-muted-foreground">Status</span><p><Badge :variant="formPengukuran.status === 'disetujui' ? 'default' : 'secondary'">{{ statusLabels[formPengukuran.status] }}</Badge></p></div></CardContent><CardFooter><Button variant="outline" as-child><Link :href="route('form-pengukurans.index')">Kembali</Link></Button></CardFooter></Card>

    <Card v-for="item in props.formPengukuran.items" :key="item.id"><CardHeader><CardTitle>{{ item.modelSize?.name }}</CardTitle><CardDescription>{{ item.remarks || 'Tanpa catatan' }}</CardDescription></CardHeader><CardContent class="flex flex-col gap-6"><div v-for="condition in measurementConditions" :key="condition.id" class="flex flex-col gap-3"><h3 class="font-semibold">{{ condition.name }}</h3><div class="overflow-x-auto rounded-lg border"><Table><TableHeader><TableRow><TableHead>Parameter</TableHead><TableHead>Keterangan</TableHead><TableHead>Spec</TableHead><TableHead>Actual</TableHead></TableRow></TableHeader><TableBody><TableRow v-for="measurement in item.actuals.filter(value => value.condition === condition.code)" :key="measurement.configurationFieldId"><TableCell class="whitespace-normal font-medium">{{ measurement.label }}</TableCell><TableCell class="whitespace-normal text-muted-foreground">{{ measurement.description ?? '-' }}</TableCell><TableCell class="font-mono">{{ measurement.specification ?? '-' }}</TableCell><TableCell class="font-mono">{{ measurement.actual ?? '-' }}</TableCell></TableRow></TableBody></Table></div></div></CardContent></Card>
  </CrudLayout>
</template>
