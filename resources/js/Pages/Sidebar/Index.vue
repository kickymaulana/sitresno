<script setup lang="ts">
import {
  Activity,
  AlertTriangle,
  ArrowUpRight,
  CheckCircle2,
  ClipboardCheck,
  Clock3,
  FileCheck2,
  ShieldCheck,
} from "@lucide/vue"
import AppSidebar from "@/components/AppSidebar.vue"
import ThemeToggle from "@/components/ThemeToggle.vue"
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbList,
  BreadcrumbPage,
} from "@/components/ui/breadcrumb"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Separator } from "@/components/ui/separator"
import {
  SidebarInset,
  SidebarProvider,
  SidebarTrigger,
} from "@/components/ui/sidebar"

const metrics = [
  { label: "Standards Active", value: "24", detail: "+3 this month", icon: ShieldCheck, tone: "text-primary" },
  { label: "Completed Checks", value: "186", detail: "+12.5% from last month", icon: ClipboardCheck, tone: "text-emerald-600" },
  { label: "Open Findings", value: "08", detail: "3 need attention", icon: AlertTriangle, tone: "text-amber-600" },
  { label: "Compliance Score", value: "94.8%", detail: "+2.4% improvement", icon: Activity, tone: "text-sky-600" },
]

const activities = [
  { title: "Monthly quality audit completed", area: "Production Area A", time: "Today, 09:42", status: "Completed", icon: CheckCircle2 },
  { title: "Corrective action assigned", area: "Safety & Compliance", time: "Today, 08:15", status: "In progress", icon: Clock3 },
  { title: "SOP document updated", area: "Quality Standards", time: "Yesterday, 16:30", status: "Reviewed", icon: FileCheck2 },
]
</script>

<template>
  <SidebarProvider>
    <AppSidebar />
    <SidebarInset>
      <header class="flex h-16 shrink-0 items-center gap-2 border-b bg-card/80 px-4 backdrop-blur md:px-6">
        <SidebarTrigger class="-ml-1" />
        <Separator orientation="vertical" class="mr-2 data-[orientation=vertical]:h-4" />
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbPage>Overview</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
        <div class="ml-auto">
          <ThemeToggle />
        </div>
      </header>

      <main class="flex flex-1 flex-col gap-6 bg-background/70 p-4 md:p-6">
        <section class="relative overflow-hidden rounded-2xl bg-primary p-6 text-primary-foreground shadow-lg md:p-8">
          <div class="relative z-10 max-w-2xl">
            <p class="mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-primary-foreground/70">Selamat datang di SITRESNO</p>
            <h1 class="font-heading text-3xl font-bold tracking-tight md:text-4xl">Bekerja dengan standar, bertumbuh dengan tresno.</h1>
            <p class="mt-3 max-w-xl text-sm leading-6 text-primary-foreground/80 md:text-base">Pantau kualitas, operasional, dan tindak lanjut tim dalam satu ruang kerja yang sederhana.</p>
          </div>
          <div class="absolute -right-12 -top-20 size-64 rounded-full border-[28px] border-primary-foreground/10" />
          <div class="absolute -bottom-32 right-24 size-72 rounded-full border-[36px] border-primary-foreground/10" />
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <Card v-for="metric in metrics" :key="metric.label" class="border-border/70 shadow-sm">
            <CardContent class="flex items-start justify-between p-5">
              <div>
                <p class="text-sm text-muted-foreground">{{ metric.label }}</p>
                <p class="mt-2 font-heading text-3xl font-bold tracking-tight">{{ metric.value }}</p>
                <p class="mt-2 text-xs text-muted-foreground">{{ metric.detail }}</p>
              </div>
              <div :class="['rounded-xl bg-secondary p-2.5', metric.tone]">
                <component :is="metric.icon" class="size-5" />
              </div>
            </CardContent>
          </Card>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.35fr_1fr]">
          <Card class="border-border/70 shadow-sm">
            <CardHeader class="flex flex-row items-center justify-between gap-4">
              <div>
                <CardTitle>Operational activity</CardTitle>
                <p class="mt-1 text-sm text-muted-foreground">Aktivitas terbaru dari seluruh area kerja.</p>
              </div>
              <button class="inline-flex items-center gap-1 text-sm font-semibold text-primary hover:underline">View all <ArrowUpRight class="size-4" /></button>
            </CardHeader>
            <CardContent class="flex flex-col gap-1">
              <div v-for="activity in activities" :key="activity.title" class="flex items-center gap-4 rounded-xl px-2 py-4 transition-colors hover:bg-muted/60">
                <div class="rounded-full bg-secondary p-2.5 text-primary">
                  <component :is="activity.icon" class="size-5" />
                </div>
                <div class="min-w-0 flex-1">
                  <p class="truncate font-semibold">{{ activity.title }}</p>
                  <p class="mt-1 text-sm text-muted-foreground">{{ activity.area }} · {{ activity.time }}</p>
                </div>
                <span class="hidden rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 sm:inline-flex">{{ activity.status }}</span>
              </div>
            </CardContent>
          </Card>

          <Card class="border-border/70 shadow-sm">
            <CardHeader>
              <CardTitle>Quality pulse</CardTitle>
              <p class="mt-1 text-sm text-muted-foreground">Ringkasan pencapaian bulan ini.</p>
            </CardHeader>
            <CardContent class="flex flex-col gap-5">
              <div>
                <div class="mb-2 flex justify-between text-sm"><span>Audit completion</span><span class="font-semibold">86%</span></div>
                <div class="h-2 rounded-full bg-secondary"><div class="h-2 w-[86%] rounded-full bg-primary" /></div>
              </div>
              <div>
                <div class="mb-2 flex justify-between text-sm"><span>Corrective actions</span><span class="font-semibold">72%</span></div>
                <div class="h-2 rounded-full bg-secondary"><div class="h-2 w-[72%] rounded-full bg-amber-500" /></div>
              </div>
              <div>
                <div class="mb-2 flex justify-between text-sm"><span>Document readiness</span><span class="font-semibold">96%</span></div>
                <div class="h-2 rounded-full bg-secondary"><div class="h-2 w-[96%] rounded-full bg-emerald-500" /></div>
              </div>
              <div class="rounded-xl bg-secondary/70 p-4 text-sm leading-6 text-secondary-foreground">Tim Anda berada di jalur yang tepat. Pertahankan konsistensi dan selesaikan 3 temuan prioritas minggu ini.</div>
            </CardContent>
          </Card>
        </section>
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>
