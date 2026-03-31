<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')

async function submit() {
  const ok = await auth.login(email.value, password.value)
  if (ok) {
    const dest = route.query.redirect || '/'
    router.push(typeof dest === 'string' ? dest : '/')
  }
}
</script>

<template>
  <v-container
    class="fill-height"
    fluid
  >
    <v-row
      align="center"
      justify="center"
    >
      <v-col
        cols="12"
        sm="8"
        md="5"
        lg="4"
      >
        <v-card
          class="pa-6"
          elevation="4"
        >
          <v-card-title class="text-h5 mb-2">
            Medical Document System
          </v-card-title>
          <v-card-subtitle>Sign in with your clinic account</v-card-subtitle>
          <v-card-text>
            <v-form @submit.prevent="submit">
              <v-text-field
                v-model="email"
                label="Email"
                type="email"
                autocomplete="username"
                variant="outlined"
                density="comfortable"
                class="mb-2"
              />
              <v-text-field
                v-model="password"
                label="Password"
                type="password"
                autocomplete="current-password"
                variant="outlined"
                density="comfortable"
                class="mb-4"
              />
              <v-alert
                v-if="auth.error"
                type="error"
                density="compact"
                class="mb-4"
                rounded="lg"
              >
                {{ auth.error }}
              </v-alert>
              <v-btn
                type="submit"
                color="primary"
                block
                size="large"
                :loading="auth.loading"
              >
                Sign in
              </v-btn>
            </v-form>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>
