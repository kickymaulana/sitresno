<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'
import { computed } from "vue"
import { usePage } from "@inertiajs/vue3"
import {
  ClipboardCheck,
  FileCheck2,
  Gauge,
  LifeBuoy,
  MessageSquareText,
  Settings2,
  ShieldCheck,
  Users,
  Workflow,
} from "@lucide/vue"
import NavMain from '@/components/NavMain.vue'
import NavProjects from '@/components/NavProjects.vue'
import NavSecondary from '@/components/NavSecondary.vue'
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

const navMain = computed(() => {
  const items = [
    { title: "Overview", url: "/", icon: Gauge, isActive: true },
    {
      title: "Quality Standards",
      url: "#",
      icon: ShieldCheck,
      items: [
        { title: "Standard Register", url: "#" },
        { title: "Audit Checklist", url: "#" },
        { title: "Findings & Actions", url: "#" },
      ],
    },
    {
      title: "Operational Records",
      url: "#",
      icon: ClipboardCheck,
      items: [
        { title: "Daily Records", url: "#" },
        { title: "Inspections", url: "#" },
        { title: "Reports", url: "#" },
      ],
    },
  ]

  if (page.props.auth.user.permissions.includes('users.view')) {
    items.push({
      title: "Team Management",
      url: "/users/access",
      icon: Users,
      items: [
        { title: "Roles & Access", url: "/users/access" },
      ],
    })
  }

  return items
})

const navSecondary = [
  { title: "Help Center", url: "#", icon: LifeBuoy },
  { title: "Send Feedback", url: "#", icon: MessageSquareText },
]

const projects = [
  { name: "ISO 9001:2015", url: "#", icon: FileCheck2 },
  { name: "Safety & Compliance", url: "#", icon: Workflow },
  { name: "System Settings", url: "#", icon: Settings2 },
]
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <a :href="route('dashboard')">
              <div class="flex aspect-square size-9 items-center justify-center rounded-xl bg-sidebar-primary text-lg font-bold text-sidebar-primary-foreground shadow-sm">S</div>
              <div class="grid flex-1 text-left leading-tight">
                <span class="truncate font-heading text-base font-bold tracking-tight">SITRESNO</span>
                <span class="truncate text-[10px] font-medium tracking-wide text-sidebar-foreground/65">QUALITY & OPERATIONAL</span>
              </div>
            </a>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>
    <SidebarContent>
      <NavMain :items="navMain" />
      <NavProjects :projects="projects" />
      <NavSecondary :items="navSecondary" class="mt-auto" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="user" />
    </SidebarFooter>
  </Sidebar>
</template>
