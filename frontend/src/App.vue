<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
</script>

<template>
  <v-app>
    <v-app-bar
      v-if="route.name !== 'login'"
      color="primary"
      density="comfortable"
    >
      <v-app-bar-title class="text-subtitle-1">
        E-Prescription
      </v-app-bar-title>
    </v-app-bar>
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>
