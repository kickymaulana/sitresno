<script lang="ts">
export const description = "A login page with form and image."
</script>

<script setup lang="ts">
import LoginForm from "@/components/login-04/components/LoginForm.vue"
import ThemeToggle from "@/components/ThemeToggle.vue"
</script>

<template>
  <div class="relative flex min-h-svh flex-col items-center justify-center bg-[radial-gradient(circle_at_top_right,oklch(0.93_0.08_70),transparent_35%),var(--background)] p-6 md:p-10">
    <div class="absolute right-5 top-5 md:right-8 md:top-8">
      <ThemeToggle />
    </div>
    <div class="w-full max-w-sm md:max-w-4xl">
      <LoginForm />
    </div>
  </div>
</template>
