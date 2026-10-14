<script setup lang="ts">
import type { SidebarProps } from '@/components/ui/sidebar'

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

const props = withDefaults(defineProps<SidebarProps>(), {
  variant: "inset",
})

const data = {
  user: {
    name: "Admin SITRESNO",
    email: "admin@sitresno.local",
    avatar: "",
  },
  navMain: [
    {
      title: "Overview",
      url: "/",
      icon: Gauge,
      isActive: true,
    },
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
    {
      title: "Team Management",
      url: "#",
      icon: Users,
      items: [
        { title: "Staff Directory", url: "#" },
        { title: "Roles & Access", url: "#" },
      ],
    },
  ],
  navSecondary: [
    { title: "Help Center", url: "#", icon: LifeBuoy },
    { title: "Send Feedback", url: "#", icon: MessageSquareText },
  ],
  projects: [
    { name: "ISO 9001:2015", url: "#", icon: FileCheck2 },
    { name: "Safety & Compliance", url: "#", icon: Workflow },
    { name: "System Settings", url: "#", icon: Settings2 },
  ],
}
</script>

<template>
  <Sidebar v-bind="props">
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <a href="/">
              <div class="flex aspect-square size-9 items-center justify-center rounded-xl bg-sidebar-primary text-lg font-bold text-sidebar-primary-foreground shadow-sm">
                S
              </div>
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
      <NavMain :items="data.navMain" />
      <NavProjects :projects="data.projects" />
      <NavSecondary :items="data.navSecondary" class="mt-auto" />
    </SidebarContent>
    <SidebarFooter>
      <NavUser :user="data.user" />
    </SidebarFooter>
  </Sidebar>
</template>
