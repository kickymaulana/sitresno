<script setup lang="ts">
import { Moon, Sun } from "@lucide/vue"
import { Button } from "@/components/ui/button"

function toggleTheme(): void {
  const isDark = document.documentElement.classList.toggle("dark")
  window.localStorage.setItem("sitresno-theme", isDark ? "dark" : "light")
}
</script>

<template>
  <Button
    variant="outline"
    size="icon"
    class="rounded-full border-border/70 bg-card/80 shadow-sm backdrop-blur hover:bg-accent"
    aria-label="Ganti tema"
    title="Ganti tema"
    @click="toggleTheme"
  >
    <Sun class="size-4 dark:hidden" />
    <Moon class="hidden size-4 dark:block" />
  </Button>
</template>
