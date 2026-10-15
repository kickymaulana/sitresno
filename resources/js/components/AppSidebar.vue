<script setup lang="ts">
import type { LucideIcon } from "@lucide/vue"
import type { SidebarProps } from '@/components/ui/sidebar'
import { computed } from "vue"
import { Link, usePage } from "@inertiajs/vue3"
import {
  ClipboardCheck,
  Factory,
  Gauge,
  Users,
} from "@lucide/vue"
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'

interface AuthUser {
  name: string
  email: string
  roles: string[]
  permissions: string[]
}

interface SharedPageProps {
  [key: string]: unknown
  auth: { user: AuthUser }
}

const props = withDefaults(defineProps<SidebarProps>(), {
  variant: "inset",
})

const page = usePage<SharedPageProps>()
const user = computed(() => ({
  name: page.props.auth.user.name,
  email: page.props.auth.user.email,
  avatar: '',
  role: page.props.auth.user.roles[0],
}))

interface NavigationItem {
  title: string
  url: string
  icon: LucideIcon
  items?: { title: string; url: string }[]
}

const navMain = computed<NavigationItem[]>(() => {
  const items: NavigationItem[] = [
    { title: "Beranda", url: route("dashboard"), icon: Gauge },
  ]

  if (page.props.auth.user.permissions.includes("records.view")) {
    items.push({
      title: "Form Pengukuran",
      url: route("form-pengukurans.index"),
      icon: ClipboardCheck,
      items: [
        { title: "Daftar Pengukuran", url: route("form-pengukurans.index") },
        { title: "Tambah Pengukuran", url: route("form-pengukurans.create") },
      ],
    })
  }

  if (page.props.auth.user.permissions.includes("masters.view")) {
    items.push({
      title: "Data Master",
      url: route("areas.index"),
      icon: Factory,
      items: [
        { title: "Area", url: route("areas.index") },
        { title: "Master Mould", url: route("master-moulds.index") },
        { title: "Ukuran Model", url: route("model-sizes.index") },
        { title: "Pelanggan", url: route("customers.index") },
      ],
    })
  }

  if (page.props.auth.user.permissions.includes("users.view")) {
    items.push({
      title: "Pengguna",
      url: route("users.access.index"),
      icon: Users,
      items: [
        { title: "Role & Akses", url: route("users.access.index") },
      ],
    })
  }

  return items
})
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="route('dashboard')">
              <div class="flex aspect-square size-9 items-center justify-center rounded-xl bg-sidebar-primary text-lg font-bold text-sidebar-primary-foreground shadow-sm">S</div>
              <div class="grid flex-1 text-left leading-tight">
                <span class="truncate font-heading text-base font-bold tracking-tight">SITRESNO</span>
                <span class="truncate text-[10px] font-medium tracking-wide text-sidebar-foreground/65">PENGUKURAN PRODUK</span>
              </div>
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>
    <SidebarContent>
      <NavMain :items="navMain" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="user" />
    </SidebarFooter>
  </Sidebar>
</template>
