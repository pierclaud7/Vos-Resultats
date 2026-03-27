<template>
  <div class="row justify-content-center">
    <div class="col-md-7">
      <div class="d-flex align-items-center gap-2 mb-4">
        <RouterLink to="/admin/etudiants" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-arrow-left"></i>
        </RouterLink>
        <h2 class="h4 fw-bold mb-0">
          {{ isEdit ? "Modifier l'étudiant" : 'Inscrire un étudiant' }}
        </h2>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>

          <form @submit.prevent="handleSubmit">
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nom *</label>
                <input v-model="form.nom" type="text" class="form-control" placeholder="DUPONT" required />
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Prénom *</label>
                <input v-model="form.prenom" type="text" class="form-control" placeholder="Jean" required />
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">Email *</label>
              <input v-model="form.email" type="email" class="form-control" placeholder="jean.dupont@example.com" required />
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Date de naissance</label>
                <input v-model="form.dateNaissance" type="date" class="form-control" />
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Session d'examen *</label>
                <select v-model="form.session_id" class="form-select" required>
                  <option value="">-- Choisir --</option>
                  <option v-for="s in sessions" :key="s.id" :value="s.id">
                    {{ s.diplome?.intitule }} ({{ s.annee }})
                  </option>
                </select>
              </div>
            </div>

            <hr />

            <div class="row g-3 align-items-center mb-4">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Moyenne (/20)</label>
                <input
                  v-model.number="form.moyenne"
                  type="number"
                  class="form-control"
                  placeholder="Ex: 14.5"
                  step="0.01"
                  min="0"
                  max="20"
                />
              </div>
              <div class="col-md-6 mt-4">
                <div class="form-check border rounded p-3 bg-light">
                  <input
                    v-model="form.estAdmis"
                    class="form-check-input"
                    type="checkbox"
                    id="estAdmis"
                  />
                  <label class="form-check-label fw-semibold text-success" for="estAdmis">
                    Déclarer ADMIS
                  </label>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-grow-1" :disabled="saving">
                <span v-if="saving"><span class="spinner-border spinner-border-sm me-1"></span></span>
                <i v-else class="fas fa-save me-1"></i>
                {{ isEdit ? 'Mettre à jour' : 'Inscrire' }}
              </button>
              <RouterLink to="/admin/etudiants" class="btn btn-outline-secondary">Annuler</RouterLink>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { etudiantService, sessionService } from '@/services/index.js'

const router = useRouter()
const route  = useRoute()
const isEdit = computed(() => !!route.params.id)

const sessions = ref([])
const error    = ref(null)
const saving   = ref(false)
const form     = ref({
  nom: '', prenom: '', email: '',
  dateNaissance: '', session_id: '',
  moyenne: null, estAdmis: false,
})

onMounted(async () => {
  const res = await sessionService.getAll()
  sessions.value = res.data

  // Pré-sélectionner la session si passée en query param
  if (route.query.session_id) {
    form.value.session_id = parseInt(route.query.session_id)
  }

  if (isEdit.value) {
    const e = await etudiantService.getById(route.params.id)
    const d = e.data
    form.value = {
      nom:           d.nom,
      prenom:        d.prenom,
      email:         d.email,
      dateNaissance: d.dateNaissance ?? '',
      session_id:    d.session?.id ?? '',
      moyenne:       d.moyenne,
      estAdmis:      d.estAdmis ?? false,
    }
  }
})

async function handleSubmit() {
  error.value = null
  saving.value = true
  try {
    const payload = {
      ...form.value,
      moyenne:  form.value.moyenne !== '' ? form.value.moyenne : null,
    }
    if (isEdit.value) {
      await etudiantService.update(route.params.id, payload)
    } else {
      await etudiantService.create(payload)
    }
    router.push('/admin/etudiants')
  } catch (err) {
    error.value = err.response?.data?.error || 'Erreur.'
  } finally {
    saving.value = false
  }
}
</script>
