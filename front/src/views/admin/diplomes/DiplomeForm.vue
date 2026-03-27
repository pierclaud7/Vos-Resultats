<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="d-flex align-items-center gap-2 mb-4">
        <RouterLink to="/admin/diplomes" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-arrow-left"></i>
        </RouterLink>
        <h2 class="h4 fw-bold mb-0">
          {{ isEdit ? 'Modifier le diplôme' : 'Nouveau diplôme' }}
        </h2>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>

          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label class="form-label fw-semibold">Intitulé du diplôme *</label>
              <input
                v-model="form.intitule"
                type="text"
                class="form-control"
                placeholder="Ex: BTS SIO, Licence Pro, Doctorat..."
                required
              />
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Filière *</label>
              <select v-model="form.filiere_id" class="form-select" required>
                <option value="">-- Choisir une filière --</option>
                <option v-for="f in filieres" :key="f.id" :value="f.id">
                  {{ f.nom }}
                </option>
              </select>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-grow-1" :disabled="saving">
                <i class="fas fa-save me-1"></i>
                {{ isEdit ? 'Mettre à jour' : 'Créer' }}
              </button>
              <RouterLink to="/admin/diplomes" class="btn btn-outline-secondary">Annuler</RouterLink>
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
import { diplomeService, filiereService } from '@/services/index.js'

const router = useRouter()
const route  = useRoute()
const isEdit = computed(() => !!route.params.id)

const filieres = ref([])
const form     = ref({ intitule: '', filiere_id: '' })
const error    = ref(null)
const saving   = ref(false)

onMounted(async () => {
  const res = await filiereService.getAll()
  filieres.value = res.data

  if (isEdit.value) {
    const d = await diplomeService.getById(route.params.id)
    form.value.intitule  = d.data.intitule
    form.value.filiere_id = d.data.filiere?.id ?? ''
  }
})

async function handleSubmit() {
  error.value = null
  saving.value = true
  try {
    if (isEdit.value) {
      await diplomeService.update(route.params.id, form.value)
    } else {
      await diplomeService.create(form.value)
    }
    router.push('/admin/diplomes')
  } catch (err) {
    error.value = err.response?.data?.error || 'Erreur.'
  } finally {
    saving.value = false
  }
}
</script>
