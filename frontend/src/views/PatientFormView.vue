<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/client'

const route = useRoute()
const router = useRouter()

const isEdit = computed(() => route.name === 'patient-edit')
const patientId = computed(() => route.params.id)

const email = ref('')
const plainPassword = ref('')
const firstName = ref('')
const lastName = ref('')
const birthDate = ref('')

const loading = ref(false)
const saving = ref(false)
const loadError = ref('')
const saveError = ref('')
const formRef = ref(null)

const passwordRules = [(v) => (v && v.length >= 6) || 'Password required (min 6 characters)']

const emailRules = [
  (v) => !!v || 'Email required',
  (v) => /.+@.+\..+/.test(v) || 'Invalid email',
]

const nameRules = [(v) => !!v || 'Required']

async function loadPatient() {
  if (!isEdit.value || !patientId.value) {
    return
  }
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await api.get(`/api/patient_profiles/${patientId.value}`, {
      headers: { Accept: 'application/json' },
    })
    firstName.value = data.firstName || ''
    lastName.value = data.lastName || ''
    birthDate.value = data.birthDate || ''
    email.value = data.user?.email || ''
    plainPassword.value = ''
  } catch (e) {
    loadError.value =
      e.response?.data?.detail || e.response?.data?.message || e.message || 'Failed to load patient'
  } finally {
    loading.value = false
  }
}

onMounted(loadPatient)
watch(
  () => route.params.id,
  () => loadPatient(),
)

async function submit() {
  saveError.value = ''
  const { valid } = await formRef.value.validate()
  if (!valid) {
    return
  }

  saving.value = true
  try {
    if (isEdit.value) {
      await api.patch(
        `/api/patient_profiles/${patientId.value}`,
        {
          firstName: firstName.value,
          lastName: lastName.value,
          email: email.value,
          birthDate: birthDate.value || null,
        },
        { headers: { 'Content-Type': 'application/merge-patch+json' } },
      )
    } else {
      await api.post('/api/patient_profiles', {
        email: email.value,
        plainPassword: plainPassword.value,
        firstName: firstName.value,
        lastName: lastName.value,
        birthDate: birthDate.value || null,
      })
    }
    router.push({ name: 'patients' })
  } catch (e) {
    const d = e.response?.data
    saveError.value =
      d?.detail ||
      d?.message ||
      (typeof d === 'string' ? d : null) ||
      e.message ||
      'Save failed'
  } finally {
    saving.value = false
  }
}

function cancel() {
  router.push({ name: 'patients' })
}
</script>

<template>
  <v-container
    fluid
    class="py-6"
    style="max-width: 560px"
  >
    <h1 class="text-h5 font-weight-medium mb-1">
      <span class="text-primary">{{ isEdit ? 'Edit patient' : 'New patient' }}</span>
    </h1>
    <p class="text-medium-emphasis text-body-2 mb-6">
      {{ isEdit ? 'Update profile and login email.' : 'Creates a patient account (ROLE_PATIENT) and assigns you as treating doctor.' }}
    </p>

    <v-progress-linear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <v-alert
      v-else-if="loadError"
      type="error"
      class="mb-4"
      rounded="lg"
    >
      {{ loadError }}
    </v-alert>

    <v-card
      v-else
      variant="outlined"
      class="border-opacity-25 pa-2"
    >
      <v-card-text>
        <v-form
          ref="formRef"
          @submit.prevent="submit"
        >
          <v-text-field
            v-model="email"
            label="Email (login)"
            type="email"
            :autocomplete="isEdit ? 'email' : 'off'"
            :rules="emailRules"
            class="mb-2"
          />

          <v-text-field
            v-if="!isEdit"
            v-model="plainPassword"
            label="Initial password"
            type="password"
            autocomplete="new-password"
            :rules="passwordRules"
            hint="Patient will use this to sign in"
            persistent-hint
            class="mb-4"
          />

          <v-text-field
            v-model="firstName"
            label="First name"
            :rules="nameRules"
            class="mb-2"
          />
          <v-text-field
            v-model="lastName"
            label="Last name"
            :rules="nameRules"
            class="mb-2"
          />
          <v-text-field
            v-model="birthDate"
            label="Date of birth"
            type="date"
            clearable
            class="mb-4"
          />

          <v-alert
            v-if="saveError"
            type="error"
            density="compact"
            class="mb-4"
            rounded="lg"
          >
            {{ saveError }}
          </v-alert>

          <div class="d-flex flex-wrap gap-2">
            <v-btn
              type="submit"
              color="primary"
              :loading="saving"
            >
              {{ isEdit ? 'Save changes' : 'Create patient' }}
            </v-btn>
            <v-btn
              variant="text"
              @click="cancel"
            >
              Cancel
            </v-btn>
          </div>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>
