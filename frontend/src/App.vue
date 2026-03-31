<script setup>
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const auth = useAuthStore()

onMounted(() => {
  if (auth.token && !auth.me) {
    auth.loadMe().catch(() => {})
  }
})
</script>

<template>
  <v-app class="app-shell">
    <v-app-bar
      v-if="route.name !== 'login'"
      color="secondary"
      elevation="0"
      density="comfortable"
      class="border-b-thin"
    >
      <template #prepend>
        <div
          class="app-shell__brand-dot"
          aria-hidden="true"
        />
      </template>
      <v-app-bar-title class="text-subtitle-1 font-weight-medium">
        <span class="text-primary">E‑Prescription</span>
        <span class="text-medium-emphasis text-caption d-none d-sm-inline ms-2">Clinic</span>
      </v-app-bar-title>
      <v-spacer />
      <v-btn
        v-if="auth.isDoctor"
        to="/patients"
        variant="text"
        class="text-none"
        prepend-icon="mdi-account-group"
      >
        Patients
      </v-btn>
      <v-btn
        to="/"
        variant="text"
        class="text-none"
        prepend-icon="mdi-view-dashboard-outline"
      >
        Home
      </v-btn>
    </v-app-bar>
    <v-main
      class="app-shell__main"
      :class="{ 'pa-0': route.name === 'login' }"
    >
      <router-view />
    </v-main>
  </v-app>
</template>

<style scoped>
.border-b-thin {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.app-shell__brand-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgb(var(--v-theme-primary));
  box-shadow: 0 0 12px rgba(66, 185, 131, 0.65);
  margin-inline-end: 4px;
}

.app-shell__main {
  background: rgb(var(--v-theme-background));
}
</style>
