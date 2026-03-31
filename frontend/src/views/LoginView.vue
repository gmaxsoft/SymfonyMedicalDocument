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
  <div class="login-page">
    <div
      class="login-page__glow"
      aria-hidden="true"
    />
    <div class="login-page__panel">
      <v-card
        class="pa-6 login-page__card"
        color="surface"
        variant="flat"
      >
        <div class="login-page__logo-row mb-4">
          <span class="login-page__logo-vue">Vue</span>
          <span class="login-page__logo-rest">Clinic</span>
        </div>
        <v-card-title class="text-h5 mb-1 pa-0">
          Medical Document
        </v-card-title>
        <v-card-subtitle class="pa-0 text-medium-emphasis">
          Sign in with your clinic account
        </v-card-subtitle>
        <v-card-text>
          <v-form @submit.prevent="submit">
            <v-text-field
              v-model="email"
              label="Email"
              type="email"
              autocomplete="username"
              class="mb-2"
            />
            <v-text-field
              v-model="password"
              label="Password"
              type="password"
              autocomplete="current-password"
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
    </div>
  </div>
</template>

<style scoped>
.login-page {
  position: relative;
  overflow: hidden;
  min-height: 100vh;
  min-height: 100dvh;
  width: 100%;
  margin: 0;
  box-sizing: border-box;
  background: radial-gradient(120% 80% at 50% 0%, #1a2834 0%, #0c0f12 45%, #080a0c 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px 16px;
}

.login-page__glow {
  pointer-events: none;
  position: absolute;
  width: 140%;
  max-width: 900px;
  aspect-ratio: 1;
  left: 50%;
  top: 18%;
  transform: translate(-50%, -50%);
  background: radial-gradient(
    circle,
    rgba(66, 185, 131, 0.22) 0%,
    rgba(53, 73, 94, 0.12) 38%,
    transparent 68%
  );
  filter: blur(2px);
}

.login-page__panel {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 420px;
}

.login-page__card {
  border: 1px solid rgba(66, 185, 131, 0.18);
  box-shadow:
    0 0 0 1px rgba(53, 73, 94, 0.35),
    0 24px 48px rgba(0, 0, 0, 0.45),
    0 0 80px rgba(66, 185, 131, 0.06);
}

.login-page__logo-row {
  font-weight: 700;
  font-size: 1.35rem;
  letter-spacing: -0.03em;
  line-height: 1.2;
}

.login-page__logo-vue {
  color: #42b983;
}

.login-page__logo-rest {
  color: #35495e;
  margin-left: 2px;
}

</style>
