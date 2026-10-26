<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3"
import { Pencil, Plus, Trash2 } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"

interface ConfigurationSummary {
  id: number
  customer: string
  modelSize: string
  fieldCount: number
  isActive: boolean
}

defineProps<{ measurementConfigurations: ConfigurationSummary[] }>()

function destroyConfiguration(configuration: ConfigurationSummary): void {
  if (window.confirm(`Hapus konfigurasi ${configuration.customer} - ${configuration.modelSize}?`)) {
    router.delete(route("measurement-configurations.destroy", configuration.id))
  }
}
</script>

<template>
  <CrudLayout title="Konfigurasi Pengukuran" description="Kelola field berdasarkan kombinasi Customer dan Model/Size.">
    <Card>
      <CardHeader class="flex-row items-center justify-between">
        <div class="flex flex-col gap-1.5">
          <CardTitle>Daftar Konfigurasi</CardTitle>
          <CardDescription>Setiap kombinasi dikelola satu per satu tanpa template atau salin konfigurasi.</CardDescription>
        </div>
        <Button as-child><Link :href="route('measurement-configurations.create')"><Plus data-icon="inline-start" />Tambah konfigurasi</Link></Button>
      </CardHeader>
      <CardContent>
        <div class="overflow-x-auto rounded-lg border">
          <Table>
            <TableHeader><TableRow><TableHead>Customer</TableHead><TableHead>Model/Size</TableHead><TableHead>Jumlah field</TableHead><TableHead>Status</TableHead><TableHead class="text-right">Aksi</TableHead></TableRow></TableHeader>
            <TableBody>
              <TableRow v-for="configuration in measurementConfigurations" :key="configuration.id">
                <TableCell class="font-medium">{{ configuration.customer }}</TableCell>
                <TableCell>{{ configuration.modelSize }}</TableCell>
                <TableCell>{{ configuration.fieldCount }}</TableCell>
                <TableCell><Badge :variant="configuration.isActive ? 'default' : 'secondary'">{{ configuration.isActive ? "Aktif" : "Nonaktif" }}</Badge></TableCell>
                <TableCell><div class="flex justify-end gap-2"><Button variant="outline" size="sm" as-child><Link :href="route('measurement-configurations.edit', configuration.id)"><Pencil data-icon="inline-start" />Ubah</Link></Button><Button variant="outline" size="sm" @click="destroyConfiguration(configuration)"><Trash2 data-icon="inline-start" />Hapus</Button></div></TableCell>
              </TableRow>
              <TableRow v-if="measurementConfigurations.length === 0"><TableCell colspan="5" class="h-24 text-center text-muted-foreground">Belum ada konfigurasi pengukuran.</TableCell></TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>
  </CrudLayout>
</template>
