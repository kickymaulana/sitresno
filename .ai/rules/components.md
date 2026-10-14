---
paths:
  - 'resources/js/components/*.vue'
---

# Components

## Use TypeScript script setup in Vue components
Use <script setup lang="ts"> for Vue pages and authored components.

## Declare Vue component props with TypeScript
Declare component props with typed defineProps or withDefaults(defineProps(...)) rather than untyped runtime prop declarations.

## Use the @ alias for internal frontend imports
Use @/ imports for internal Vue components and frontend modules instead of relative parent-directory imports.

## Use PascalCase components and grouped Pages paths
Name reusable Vue components in PascalCase under resources/js/components; organize Inertia page entries under resources/js/Pages, using subdirectories for page groupings.
