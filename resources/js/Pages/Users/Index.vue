<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3"
import { ShieldCheck } from "@lucide/vue"
import AppSidebar from "@/components/AppSidebar.vue"
import ThemeToggle from "@/components/ThemeToggle.vue"
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select"
import { Separator } from "@/components/ui/separator"
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from "@/components/ui/sidebar"
import { Switch } from "@/components/ui/switch"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

interface UserAccess {
  id: number
  nik: string
  name: string
  email: string
  isApproved: boolean
  role: string | null
  lastLoginAt: string | null
}

interface PageProps {
  [key: string]: unknown
  flash?: { status?: string }
}

const props = defineProps<{
  users: UserAccess[]
  roles: string[]
}>()

const page = usePage<PageProps>()

function save(user: UserAccess): void {
  router.patch(`/users/${user.id}/access`, {
    role: user.role,
    is_approved: user.isApproved,
  }, {
    preserveScroll: true,
  })
}
</script>

<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header class="flex h-16 shrink-0 items-center gap-2 border-b bg-card/80 px-4 backdrop-blur md:px-6">
        <SidebarTrigger class="-ml-1" />
        <Separator orientation="vertical" class="mr-2 data-[orientation=vertical]:h-4" />
        <div>
          <p class="font-heading font-semibold">Roles & Access</p>
          <p class="text-xs text-muted-foreground">Kelola aktivasi dan kewenangan pengguna</p>
        </div>
        <div class="ml-auto">
          <ThemeToggle />
        </div>
      </header>

      <main class="flex flex-1 flex-col gap-6 bg-background/70 p-4 md:p-6">
        <Alert v-if="page.props.flash?.status">
          <ShieldCheck />
          <AlertTitle>Akses diperbarui</AlertTitle>
          <AlertDescription>{{ page.props.flash.status }}</AlertDescription>
        </Alert>

        <Card class="border-border/70 shadow-sm">
          <CardHeader>
            <CardTitle>Pengguna SITRESNO</CardTitle>
            <CardDescription>Pengguna baru dari SSO tetap nonaktif sampai admin memilih role dan mengaktifkan aksesnya.</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto rounded-lg border">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Pengguna</TableHead>
                    <TableHead>NIK</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Role</TableHead>
                    <TableHead>Aktif</TableHead>
                    <TableHead class="text-right">Aksi</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="user in props.users" :key="user.id">
                    <TableCell>
                      <div class="flex flex-col gap-1">
                        <span class="font-medium">{{ user.name }}</span>
                        <span class="text-xs text-muted-foreground">{{ user.email }}</span>
                      </div>
                    </TableCell>
                    <TableCell class="font-mono text-sm">{{ user.nik }}</TableCell>
                    <TableCell>
                      <Badge :variant="user.isApproved ? 'default' : 'secondary'">
                        {{ user.isApproved ? 'Aktif' : 'Pending' }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <Select v-model="user.role">
                        <SelectTrigger class="w-36">
                          <SelectValue placeholder="Pilih role" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem v-for="role in props.roles" :key="role" :value="role">
                              {{ role }}
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                    </TableCell>
                    <TableCell>
                      <Switch v-model="user.isApproved" :aria-label="`Aktifkan ${user.name}`" />
                    </TableCell>
                    <TableCell class="text-right">
                      <Button size="sm" :disabled="!user.role" @click="save(user)">Simpan</Button>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </CardContent>
        </Card>
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>
