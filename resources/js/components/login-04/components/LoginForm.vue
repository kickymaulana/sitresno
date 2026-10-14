<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { usePage } from "@inertiajs/vue3"
import { LogIn } from "@lucide/vue"
import placeholderImage from "@/assets/orang_ngukur.jpg"
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Field, FieldDescription, FieldGroup } from '@/components/ui/field'
import { cn } from "@/lib/utils"

interface LoginPageProps {
  [key: string]: unknown
  errors?: { sso?: string }
  flash?: { status?: string }
}

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()

const page = usePage<LoginPageProps>()
</script>

<template>
  <div :class="cn('flex flex-col gap-6', props.class)">
    <Card class="overflow-hidden border-border/70 p-0 shadow-xl shadow-orange-950/5">
      <CardContent class="grid p-0 md:grid-cols-2">
        <div class="flex flex-col justify-center gap-6 p-6 md:p-10">
          <FieldGroup>
            <div class="flex flex-col items-center gap-4 text-center">
              <div class="flex size-12 items-center justify-center rounded-2xl bg-primary font-heading text-xl font-bold text-primary-foreground shadow-lg shadow-primary/20">S</div>
              <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-primary">SITRESNO</p>
                <h1 class="font-heading text-3xl font-bold tracking-tight">Selamat datang kembali</h1>
                <p class="mt-2 text-muted-foreground">Masuk menggunakan akun perusahaan untuk melanjutkan pekerjaan quality dan operational Anda.</p>
              </div>
            </div>
            <Alert v-if="page.props.flash?.status">
              <AlertTitle>Status akun</AlertTitle>
              <AlertDescription>{{ page.props.flash.status }}</AlertDescription>
            </Alert>
            <Alert v-if="page.props.errors?.sso" variant="destructive">
              <AlertTitle>Login gagal</AlertTitle>
              <AlertDescription>{{ page.props.errors.sso }}</AlertDescription>
            </Alert>
            <Field>
              <Button as-child class="w-full">
                <a :href="route('sso.redirect')">
                  <LogIn data-icon="inline-start" />
                  Masuk dengan SSO
                </a>
              </Button>
            </Field>
            <FieldDescription class="text-center">Akun baru memerlukan aktivasi dan role dari admin SITRESNO.</FieldDescription>
          </FieldGroup>
        </div>
        <div class="relative hidden min-h-[520px] overflow-hidden bg-primary md:block">
          <img :src="placeholderImage" alt="Staf sedang melakukan pengukuran operasional" class="absolute inset-0 h-full w-full object-cover opacity-75 mix-blend-multiply" />
          <div class="absolute inset-0 bg-gradient-to-t from-stone-950/80 via-primary/20 to-transparent" />
          <div class="absolute bottom-8 left-8 right-8 text-primary-foreground">
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-primary-foreground/70">Sistem Terpadu Rekam Standar</p>
            <p class="font-heading text-2xl font-bold leading-tight">Standar yang rapi. Operasional yang berarti.</p>
          </div>
        </div>
      </CardContent>
    </Card>
    <FieldDescription class="px-6 text-center">Akses SITRESNO dilindungi oleh autentikasi SSO perusahaan.</FieldDescription>
  </div>
</template>
