import '../css/app.css'
import { createInertiaApp } from '@inertiajs/vue3'
import { ZiggyVue } from 'ziggy-js'

if (typeof window !== 'undefined') {
  const savedTheme = window.localStorage.getItem('sitresno-theme')
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  const shouldUseDarkTheme = savedTheme === 'dark' || (!savedTheme && prefersDark)

  document.documentElement.classList.toggle('dark', shouldUseDarkTheme)
}

createInertiaApp({
  withApp(app) {
    app.use(ZiggyVue)
  },
})
