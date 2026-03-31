<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const loadError = ref('')
const loading = ref(true)

const me = computed(() => auth.me)

onMounted(async () => {
  try {
    await auth.loadMe()
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
          <h1 class="text-h5 font-weight-medium">
            <span class="text-primary">Dashboard</span>
          </h1>
          <p
            v-if="me"
            class="text-medium-emphasis mb-0"
          >
            {{ me.email }}
          </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <v-btn
            v-if="auth.isDoctor"
            color="primary"
            variant="flat"
            prepend-icon="mdi-account-group"
            to="/patients"
          >
            Manage patients
          </v-btn>
          <v-btn
            variant="tonal"
            color="primary"
            @click="signOut"
          >
            Sign out
          </v-btn>
        </div>
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
          <v-card
            variant="outlined"
            class="border-opacity-25"
          >
            <v-card-title class="text-primary">
              Account
            </v-card-title>
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
          <v-card
            variant="outlined"
            class="border-opacity-25"
          >
            <v-card-title class="text-primary">
              Your profile
            </v-card-title>
            <v-card-text>
              <div>{{ me.patientProfile.firstName }} {{ me.patientProfile.lastName }}</div>
              <div
                v-if="me.patientProfile.birthDate"
                class="text-medium-emphasis text-body-2 mt-2"
              >
                Date of birth: {{ me.patientProfile.birthDate }}
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <v-row v-if="me.prescriptions?.length">
        <v-col cols="12">
          <v-card
            variant="outlined"
            class="border-opacity-25"
          >
            <v-card-title class="text-primary">
              Your prescriptions
            </v-card-title>
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
          <v-card
            variant="outlined"
            class="border-opacity-25"
          >
            <v-card-title class="text-primary d-flex align-center justify-space-between flex-wrap gap-2">
              <span>Your patients</span>
              <v-btn
                size="small"
                color="primary"
                variant="tonal"
                to="/patients"
              >
                Full CRUD
              </v-btn>
            </v-card-title>
            <v-data-table
              :items="me.patients"
              :headers="[
                { title: 'First name', key: 'firstName' },
                { title: 'Last name', key: 'lastName' },
                { title: 'Birth date', key: 'birthDate' },
                { title: 'Email', key: 'patientEmail' },
                { title: '', key: 'actions', sortable: false, align: 'end' },
              ]"
              item-value="id"
              class="elevation-0"
              density="comfortable"
            >
              <template #[`item.birthDate`]="{ item }">
                {{ item.birthDate || '—' }}
              </template>
              <template #[`item.actions`]="{ item }">
                <v-btn
                  :to="{ name: 'patient-edit', params: { id: String(item.id) } }"
                  icon="mdi-pencil"
                  variant="text"
                  size="small"
                  aria-label="Edit patient"
                />
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>
