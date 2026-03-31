<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/client'
import { hydraMember } from '@/api/hydra'

const router = useRouter()
const items = ref([])
const loading = ref(true)
const listError = ref('')
const deleteDialog = ref(false)
const deleteTarget = ref(null)
const deleteLoading = ref(false)
const deleteError = ref('')

async function load() {
  loading.value = true
  listError.value = ''
  try {
    const res = await api.get('/api/patient_profiles', {
      headers: { Accept: 'application/ld+json' },
    })
    items.value = hydraMember(res)
  } catch (e) {
    listError.value =
      e.response?.data?.detail || e.response?.data?.message || e.message || 'Failed to load patients'
  } finally {
    loading.value = false
  }
}

onMounted(load)

function formatBirth(d) {
  if (!d) {
    return '—'
  }
  return d
}

function goNew() {
  router.push({ name: 'patient-new' })
}

function goEdit(row) {
  const id = row.id ?? row['@id']?.split('/').pop()
  if (id) {
    router.push({ name: 'patient-edit', params: { id: String(id) } })
  }
}

function askDelete(row) {
  deleteTarget.value = row
  deleteError.value = ''
  deleteDialog.value = true
}

function cancelDelete() {
  deleteDialog.value = false
  deleteTarget.value = null
}

async function confirmDelete() {
  if (!deleteTarget.value) {
    return
  }
  const id = deleteTarget.value.id
  if (!id) {
    return
  }
  deleteLoading.value = true
  deleteError.value = ''
  try {
    await api.delete(`/api/patient_profiles/${id}`)
    deleteDialog.value = false
    deleteTarget.value = null
    await load()
  } catch (e) {
    deleteError.value =
      e.response?.data?.detail || e.response?.data?.message || e.message || 'Delete failed'
  } finally {
    deleteLoading.value = false
  }
}

const headers = [
  { title: 'Last name', key: 'lastName' },
  { title: 'First name', key: 'firstName' },
  { title: 'Email', key: 'email' },
  { title: 'Birth date', key: 'birthDate' },
  { title: '', key: 'actions', sortable: false, align: 'end' },
]
</script>

<template>
  <v-container
    fluid
    class="py-6"
  >
    <v-row class="align-center mb-4">
      <v-col
        cols="12"
        md="8"
      >
        <h1 class="text-h5 font-weight-medium">
          <span class="text-primary">Patients</span>
        </h1>
        <p class="text-medium-emphasis mb-0 text-body-2">
          Manage clinic roster — create, edit, or remove patient accounts and profiles.
        </p>
      </v-col>
      <v-col
        cols="12"
        md="4"
        class="text-md-end"
      >
        <v-btn
          color="primary"
          prepend-icon="mdi-account-plus"
          @click="goNew"
        >
          New patient
        </v-btn>
      </v-col>
    </v-row>

    <v-progress-linear
      v-if="loading"
      indeterminate
      class="mb-4"
    />

    <v-alert
      v-else-if="listError"
      type="error"
      class="mb-4"
      rounded="lg"
    >
      {{ listError }}
    </v-alert>

    <v-card
      v-else
      variant="outlined"
      class="border-opacity-25"
    >
      <v-data-table
        :items="items"
        :headers="headers"
        item-value="id"
        class="elevation-0"
        density="comfortable"
      >
        <template #[`item.email`]="{ item }">
          {{ item.user?.email || '—' }}
        </template>
        <template #[`item.birthDate`]="{ item }">
          {{ formatBirth(item.birthDate) }}
        </template>
        <template #[`item.actions`]="{ item }">
          <v-btn
            icon="mdi-pencil"
            variant="text"
            size="small"
            aria-label="Edit"
            @click="goEdit(item)"
          />
          <v-btn
            icon="mdi-delete-outline"
            variant="text"
            size="small"
            color="error"
            aria-label="Delete"
            @click="askDelete(item)"
          />
        </template>
      </v-data-table>
    </v-card>

    <v-dialog
      v-model="deleteDialog"
      max-width="420"
      @click:outside="cancelDelete"
    >
      <v-card v-if="deleteTarget">
        <v-card-title>Delete patient</v-card-title>
        <v-card-text>
          <p class="mb-2">
            Permanently remove
            <strong>{{ deleteTarget.firstName }} {{ deleteTarget.lastName }}</strong>
            and their login? Prescriptions and history for this profile will be removed (cascade).
          </p>
          <v-alert
            v-if="deleteError"
            type="error"
            density="compact"
            class="mt-2"
            rounded="lg"
          >
            {{ deleteError }}
          </v-alert>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            variant="text"
            @click="cancelDelete"
          >
            Cancel
          </v-btn>
          <v-btn
            color="error"
            variant="flat"
            :loading="deleteLoading"
            @click="confirmDelete"
          >
            Delete
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>
