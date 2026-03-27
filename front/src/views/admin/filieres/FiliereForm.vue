<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="d-flex align-items-center gap-2 mb-4">
        <RouterLink to="/admin/filieres" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-arrow-left"></i>
        </RouterLink>
        <h2 class="h4 fw-bold mb-0">
          {{ isEdit ? 'Modifier la filière' : 'Nouvelle filière' }}
        </h2>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>

          <form @submit.prevent="handleSubmit">
            <div class="mb-4">
              <label class="form-label fw-semibold">Nom de la filière *</label>
              <input
                v-model="form.nom"
                type="text"
                class="form-control form-control-lg"
                placeholder="Ex: Informatique, Gestion, Pharmacie..."
                required
                autofocus
              />
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-grow-1" :disabled="saving">
                <span v-if="saving"><span class="spinner-border spinner-border-sm me-1"></span></span>
                <i v-else class="fas fa-save me-1"></i>
                {{ isEdit ? 'Mettre à jour' : 'Créer' }}
              </button>
              <RouterLink to="/admin/filieres" class="btn btn-outline-secondary">
                Annuler
              </RouterLink>
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
import { filiereService } from '@/services/index.js'

const router = useRouter()
const route  = useRoute()

const isEdit = computed(() => !!route.params.id)
const form   = ref({ nom: '' })
const error  = ref(null)
const saving = ref(false)

onMounted(async () => {
  if (isEdit.value) {
    const res = await filiereService.getById(route.params.id)
    form.value.nom = res.data.nom
  }
})

async function handleSubmit() {
  error.value = null
  saving.value = true
  try {
    if (isEdit.value) {
      await filiereService.update(route.params.id, form.value)
    } else {
      await filiereService.create(form.value)
    }
    router.push('/admin/filieres')
  } catch (err) {
    error.value = err.response?.data?.error || 'Une erreur est survenue.'
  } finally {
    saving.value = false
  }
}
</script>
