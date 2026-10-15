<script setup lang="ts">
import { Link, router } from "@inertiajs/vue3"
import { MoreHorizontal, Pencil, Plus, Trash2 } from "@lucide/vue"
import CrudLayout from "@/components/CrudLayout.vue"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import type { MasterField, MasterRecord } from "@/lib/crud"

const props = defineProps<{
  title: string
  description: string
  records: MasterRecord[]
  fields: MasterField[]
  routeName: string
}>()

function remove(record: MasterRecord): void {
  if (window.confirm(`Hapus ${record.name ?? record.code ?? record.fullName ?? `data #${record.id}`}?`)) {
    router.delete(route(`${props.routeName}.destroy`, record.id), { preserveScroll: true })
  }
}
</script>

<template>
  <CrudLayout :title="title" :description="description">
    <Card class="border-border/70 shadow-sm">
      <CardHeader>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div class="flex flex-col gap-1">
            <CardTitle>{{ title }}</CardTitle>
            <CardDescription>{{ description }}</CardDescription>
          </div>
          <Button as-child>
            <Link :href="route(`${routeName}.create`)">
              <Plus data-icon="inline-start" />
              Tambah data
            </Link>
          </Button>
        </div>
      </CardHeader>
      <CardContent>
        <div class="overflow-x-auto rounded-lg border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead v-for="field in fields" :key="field.key">{{ field.label }}</TableHead>
                <TableHead class="text-right">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="record in records" :key="record.id">
                <TableCell v-for="field in fields" :key="field.key" class="font-medium">
                  {{ record[field.key] ?? "-" }}
                </TableCell>
                <TableCell class="text-right">
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="icon-sm" aria-label="Buka aksi">
                        <MoreHorizontal />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuGroup>
                        <DropdownMenuItem as-child>
                          <Link :href="route(`${routeName}.edit`, record.id)">
                            <Pencil />
                            Ubah
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem variant="destructive" @click="remove(record)">
                          <Trash2 />
                          Hapus
                        </DropdownMenuItem>
                      </DropdownMenuGroup>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
              <TableRow v-if="records.length === 0">
                <TableCell :colspan="fields.length + 1" class="h-28 text-center text-muted-foreground">
                  Belum ada data.
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
      <CardFooter class="text-sm text-muted-foreground">Total {{ records.length }} data</CardFooter>
    </Card>
  </CrudLayout>
</template>
