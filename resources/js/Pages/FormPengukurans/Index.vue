<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3"
import { MoreHorizontal, Pencil, Plus, Trash2 } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import type { FormPengukuran } from "@/lib/crud"

const props = defineProps<{ formPengukurans: FormPengukuran[] }>()

const statusLabels = {
  draft: "Draft",
  menunggu_pemeriksaan: "Menunggu pemeriksaan",
  disetujui: "Disetujui",
  ditolak: "Ditolak",
} as const

function remove(formPengukuran: FormPengukuran): void {
  if (window.confirm(`Hapus form pengukuran #${formPengukuran.id}?`)) {
    router.delete(route("form-pengukurans.destroy", formPengukuran.id), { preserveScroll: true })
  }
}
</script>

<template>
  <CrudLayout title="Form Pengukuran" description="Kelola catatan hasil pengukuran produk.">
    <Card class="border-border/70 shadow-sm">
      <CardHeader><div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div class="flex flex-col gap-1"><CardTitle>Daftar Form Pengukuran</CardTitle><CardDescription>Riwayat pengukuran sebelum cuci, sesudah cuci, dan setelah bakar.</CardDescription></div><Button as-child><Link :href="route('form-pengukurans.create')"><Plus data-icon="inline-start" />Tambah form</Link></Button></div></CardHeader>
      <CardContent><div class="overflow-x-auto rounded-lg border"><Table><TableHeader><TableRow><TableHead>ID</TableHead><TableHead>Tanggal pengukuran</TableHead><TableHead>Area</TableHead><TableHead>Mould</TableHead><TableHead>Model / Ukuran</TableHead><TableHead>Pelanggan</TableHead><TableHead>Status</TableHead><TableHead class="text-right">Aksi</TableHead></TableRow></TableHeader><TableBody><TableRow v-for="formPengukuran in formPengukurans" :key="formPengukuran.id"><TableCell class="font-medium"><Link :href="route('form-pengukurans.show', formPengukuran.id)" class="hover:underline">#{{ formPengukuran.id }}</Link></TableCell><TableCell>{{ formPengukuran.tanggalPengukuran ?? '-' }}</TableCell><TableCell>{{ formPengukuran.area?.name ?? '-' }}</TableCell><TableCell>{{ formPengukuran.masterMould?.name ?? '-' }}</TableCell><TableCell>{{ formPengukuran.modelSize?.name ?? '-' }}</TableCell><TableCell>{{ formPengukuran.customer?.name ?? '-' }}</TableCell><TableCell><Badge :variant="formPengukuran.status === 'disetujui' ? 'default' : 'secondary'">{{ statusLabels[formPengukuran.status] }}</Badge></TableCell><TableCell class="text-right"><DropdownMenu><DropdownMenuTrigger as-child><Button variant="ghost" size="icon-sm" aria-label="Buka aksi"><MoreHorizontal /></Button></DropdownMenuTrigger><DropdownMenuContent align="end"><DropdownMenuGroup><DropdownMenuItem as-child><Link :href="route('form-pengukurans.show', formPengukuran.id)">Lihat detail</Link></DropdownMenuItem><DropdownMenuItem as-child><Link :href="route('form-pengukurans.edit', formPengukuran.id)"><Pencil />Ubah</Link></DropdownMenuItem><DropdownMenuItem variant="destructive" @click="remove(formPengukuran)"><Trash2 />Hapus</DropdownMenuItem></DropdownMenuGroup></DropdownMenuContent></DropdownMenu></TableCell></TableRow><TableRow v-if="formPengukurans.length === 0"><TableCell :colspan="8" class="h-28 text-center text-muted-foreground">Belum ada form pengukuran.</TableCell></TableRow></TableBody></Table></div></CardContent>
      <CardFooter class="text-sm text-muted-foreground">Total {{ formPengukurans.length }} form</CardFooter>
    </Card>
  </CrudLayout>
</template>
