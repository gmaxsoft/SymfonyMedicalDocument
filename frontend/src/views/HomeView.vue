<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const me = ref(null)
const loadError = ref('')
const loading = ref(true)

onMounted(async () => {
  try {
    me.value = await auth.fetchMe()
  } catch (e) {
    loadError.value = e.response?.data?.detail || e.message || 'Could not load profile'
  } finally {
    loading.value = false
  }
})

function signOut() {
  auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <v-container
    fluid
    class="py-6"
  >
    <v-row>
      <v-col
        cols="12"
        class="d-flex align-center justify-space-between flex-wrap gap-2"
      >
        <div>
          <h1 class="text-h5">
            Dashboard
          </h1>
          <p
            v-if="me"
            class="text-medium-emphasis mb-0"
          >
            {{ me.email }}
          </p>
        </div>
        <v-btn
          variant="tonal"
          color="secondary"
          @click="signOut"
        >
          Sign out
        </v-btn>
      </v-col>
    </v-row>

    <v-progress-linear
      v-if="loading"
      indeterminate
      class="my-4"
    />

    <v-alert
      v-else-if="loadError"
      type="error"
      class="my-4"
    >
      {{ loadError }}
    </v-alert>

    <template v-else-if="me">
      <v-row>
        <v-col
          cols="12"
          md="6"
        >
          <v-card>
            <v-card-title>Account</v-card-title>
            <v-card-text>
              <div><strong>Roles:</strong> {{ me.roles?.join(', ') }}</div>
            </v-card-text>
          </v-card>
        </v-col>

        <v-col
          v-if="me.patientProfile"
          cols="12"
          md="6"
        >
          <v-card>
            <v-card-title>Your profile</v-card-title>
            <v-card-text>
              {{ me.patientProfile.firstName }} {{ me.patientProfile.lastName }}
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row v-if="me.prescriptions?.length">
        <v-col cols="12">
          <v-card>
            <v-card-title>Your prescriptions</v-card-title>
            <v-data-table
              :items="me.prescriptions"
              :headers="[
                { title: 'Status', key: 'status' },
                { title: 'Issued', key: 'issuedAt' },
                { title: 'Valid until', key: 'validUntil' },
                { title: 'Verification token', key: 'verificationToken' },
              ]"
              item-value="verificationToken"
              class="elevation-0"
              density="comfortable"
            />
          </v-card>
        </v-col>
      </v-row>

      <v-row v-if="me.patients?.length">
        <v-col cols="12">
          <v-card>
            <v-card-title>Your patients</v-card-title>
            <v-data-table
              :items="me.patients"
              :headers="[
                { title: 'First name', key: 'firstName' },
                { title: 'Last name', key: 'lastName' },
                { title: 'Patient email', key: 'patientEmail' },
              ]"
              item-value="patientEmail"
              class="elevation-0"
              density="comfortable"
            />
          </v-card>
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>
