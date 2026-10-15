<script setup lang="ts">
import { usePage } from "@inertiajs/vue3"
import { CheckCircle2 } from "@lucide/vue"
import AppSidebar from "@/components/AppSidebar.vue"
import ThemeToggle from "@/components/ThemeToggle.vue"
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"
import { Separator } from "@/components/ui/separator"
import { SidebarInset, SidebarProvider, SidebarTrigger } from "@/components/ui/sidebar"

interface SharedPageProps {
  [key: string]: unknown
  flash?: { status?: string }
}

defineProps<{
  title: string
  description: string
}>()

const page = usePage<SharedPageProps>()
</script>

<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header class="flex h-16 shrink-0 items-center gap-2 border-b bg-card/80 px-4 backdrop-blur md:px-6">
        <SidebarTrigger class="-ml-1" />
        <Separator orientation="vertical" class="mr-2 data-[orientation=vertical]:h-4" />
        <div class="min-w-0">
          <p class="truncate font-heading font-semibold">{{ title }}</p>
          <p class="truncate text-xs text-muted-foreground">{{ description }}</p>
        </div>
        <div class="ml-auto">
          <ThemeToggle />
        </div>
      </header>
      <main class="flex flex-1 flex-col gap-6 bg-background/70 p-4 md:p-6">
        <Alert v-if="page.props.flash?.status">
          <CheckCircle2 />
          <AlertTitle>Berhasil</AlertTitle>
          <AlertDescription>{{ page.props.flash.status }}</AlertDescription>
        </Alert>
        <slot />
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>
