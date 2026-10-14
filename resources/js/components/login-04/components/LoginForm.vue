<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import placeholderImage from "@/assets/orang_ngukur.jpg"
import { cn } from "@/lib/utils"
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import {
  Field,
  FieldDescription,
  FieldGroup,
  FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input'

const props = defineProps<{
  class?: HTMLAttributes["class"]
}>()
</script>

<template>
  <div :class="cn('flex flex-col gap-6', props.class)">
    <Card class="overflow-hidden border-border/70 p-0 shadow-xl shadow-orange-950/5">
      <CardContent class="grid p-0 md:grid-cols-2">
        <form class="flex flex-col justify-center gap-6 p-6 md:p-10">
          <FieldGroup>
            <div class="flex flex-col items-center gap-4 text-center">
              <div class="flex size-12 items-center justify-center rounded-2xl bg-primary font-heading text-xl font-bold text-primary-foreground shadow-lg shadow-primary/20">S</div>
              <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-primary">SITRESNO</p>
                <h1 class="font-heading text-3xl font-bold tracking-tight">Selamat datang kembali</h1>
                <p class="mt-2 text-muted-foreground">Masuk untuk melanjutkan pekerjaan quality dan operational Anda.</p>
              </div>
            </div>
            <Field>
              <FieldLabel for="email">Email kerja</FieldLabel>
              <Input id="email" type="email" placeholder="nama@perusahaan.com" required />
            </Field>
            <Field>
              <div class="flex items-center">
                <FieldLabel for="password">Password</FieldLabel>
                <a href="#" class="ml-auto text-sm font-medium text-primary underline-offset-2 hover:underline">Lupa password?</a>
              </div>
              <Input id="password" type="password" required />
            </Field>
            <Field>
              <Button type="submit" class="w-full">Masuk ke SITRESNO</Button>
            </Field>
            <FieldDescription class="text-center">Gunakan akun yang diberikan oleh administrator sistem.</FieldDescription>
          </FieldGroup>
        </form>
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
    <FieldDescription class="px-6 text-center">Dengan melanjutkan, Anda menyetujui kebijakan penggunaan SITRESNO.</FieldDescription>
  </div>
</template>
