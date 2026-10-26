<script setup lang="ts">
import { Link, router, useForm } from "@inertiajs/vue3"
import { MoreHorizontal, Pencil, Plus, Trash2 } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { Field, FieldGroup, FieldLabel } from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import type { FormPengukuran } from "@/lib/crud"

const props = defineProps<{
  formPengukurans: FormPengukuran[]
  customers: { id: number; name: string }[]
  filters: { q: string; customerId: number | null; status: string; hariKe: string; tanggal: string }
}>()

const statusLabels = { draft: "Draft", menunggu_pemeriksaan: "Menunggu pemeriksaan", disetujui: "Disetujui", ditolak: "Ditolak" } as const
const filters = useForm({
  q: props.filters.q,
  customerId: props.filters.customerId ?? 0,
  status: props.filters.status || "all",
  hariKe: props.filters.hariKe,
  tanggal: props.filters.tanggal,
})

function applyFilters(): void {
  router.get(route("form-pengukurans.index"), filters.data(), { preserveState: true, replace: true })
}

function resetFilters(): void {
  filters.reset()
  filters.customerId = 0
  filters.status = "all"
  applyFilters()
}

function remove(formPengukuran: FormPengukuran): void {
  if (window.confirm(`Hapus form pengukuran #${formPengukuran.id}?`)) router.delete(route("form-pengukurans.destroy", formPengukuran.id), { preserveScroll: true })
}
</script>

<template>
  <CrudLayout title="Form Pengukuran" description="Kelola form harian customer dengan banyak model/size.">
    <Card>
      <CardHeader>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <CardTitle>Daftar Form Pengukuran</CardTitle>
            <CardDescription>Parameter, spesifikasi, dan aktual disimpan dinamis.</CardDescription>
          </div>
          <Button as-child><Link :href="route('form-pengukurans.create')"><Plus data-icon="inline-start" />Tambah form</Link></Button>
        </div>
      </CardHeader>
      <CardContent class="flex flex-col gap-5">
        <form class="rounded-lg border bg-muted/30 p-4" @submit.prevent="applyFilters">
          <FieldGroup class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <Field>
              <FieldLabel for="search-form">Cari</FieldLabel>
              <Input id="search-form" v-model="filters.q" placeholder="Customer, mould, model/size" />
            </Field>
            <Field>
              <FieldLabel>Customer</FieldLabel>
              <Select v-model="filters.customerId">
                <SelectTrigger><SelectValue placeholder="Semua customer" /></SelectTrigger>
                <SelectContent><SelectGroup><SelectItem :value="0">Semua customer</SelectItem><SelectItem v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</SelectItem></SelectGroup></SelectContent>
              </Select>
            </Field>
            <Field>
              <FieldLabel>Status</FieldLabel>
              <Select v-model="filters.status">
                <SelectTrigger><SelectValue placeholder="Semua status" /></SelectTrigger>
                <SelectContent><SelectGroup><SelectItem value="all">Semua status</SelectItem><SelectItem value="draft">Draft</SelectItem><SelectItem value="menunggu_pemeriksaan">Menunggu pemeriksaan</SelectItem><SelectItem value="disetujui">Disetujui</SelectItem><SelectItem value="ditolak">Ditolak</SelectItem></SelectGroup></SelectContent>
              </Select>
            </Field>
            <Field>
              <FieldLabel for="hari-ke-filter">Hari ke</FieldLabel>
              <Input id="hari-ke-filter" v-model="filters.hariKe" placeholder="Contoh: 1" />
            </Field>
            <Field>
              <FieldLabel for="tanggal-filter">Tanggal</FieldLabel>
              <Input id="tanggal-filter" v-model="filters.tanggal" type="date" />
            </Field>
          </FieldGroup>
          <div class="mt-4 flex gap-2"><Button type="submit">Terapkan filter</Button><Button type="button" variant="outline" @click="resetFilters">Reset</Button></div>
        </form>

        <div class="overflow-x-auto rounded-lg border">
          <Table>
            <TableHeader><TableRow><TableHead>ID</TableHead><TableHead>Tanggal</TableHead><TableHead>Hari ke</TableHead><TableHead>Area</TableHead><TableHead>Mould</TableHead><TableHead>Model/Size</TableHead><TableHead>Customer</TableHead><TableHead>Status</TableHead><TableHead class="text-right">Aksi</TableHead></TableRow></TableHeader>
            <TableBody>
              <TableRow v-for="record in formPengukurans" :key="record.id">
                <TableCell><Link :href="route('form-pengukurans.show', record.id)" class="font-medium hover:underline">#{{ record.id }}</Link></TableCell>
                <TableCell>{{ record.tanggalPengukuran }}</TableCell>
                <TableCell>{{ record.hariKe ?? '-' }}</TableCell>
                <TableCell>{{ record.area?.name ?? '-' }}</TableCell>
                <TableCell>{{ record.masterMould?.name ?? '-' }}</TableCell>
                <TableCell class="max-w-80 whitespace-normal">{{ record.modelSizes.map(item => item.name).join(', ') || '-' }}</TableCell>
                <TableCell>{{ record.customer?.name ?? '-' }}</TableCell>
                <TableCell><Badge :variant="record.status === 'disetujui' ? 'default' : 'secondary'">{{ statusLabels[record.status] }}</Badge></TableCell>
                <TableCell class="text-right"><DropdownMenu><DropdownMenuTrigger as-child><Button variant="ghost" size="icon-sm" aria-label="Buka aksi"><MoreHorizontal /></Button></DropdownMenuTrigger><DropdownMenuContent align="end"><DropdownMenuGroup><DropdownMenuItem as-child><Link :href="route('form-pengukurans.show', record.id)">Lihat detail</Link></DropdownMenuItem><DropdownMenuItem as-child><Link :href="route('form-pengukurans.edit', record.id)"><Pencil />Ubah</Link></DropdownMenuItem><DropdownMenuItem variant="destructive" @click="remove(record)"><Trash2 />Hapus</DropdownMenuItem></DropdownMenuGroup></DropdownMenuContent></DropdownMenu></TableCell>
              </TableRow>
              <TableRow v-if="formPengukurans.length === 0"><TableCell colspan="9" class="py-10 text-center text-muted-foreground">Tidak ada form yang sesuai dengan filter.</TableCell></TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
      <CardFooter>Total {{ formPengukurans.length }} form</CardFooter>
    </Card>
  </CrudLayout>
</template>
