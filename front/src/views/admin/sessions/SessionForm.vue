<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="d-flex align-items-center gap-2 mb-4">
        <RouterLink to="/admin/sessions" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-arrow-left"></i>
        </RouterLink>
        <h2 class="h4 fw-bold mb-0">
          {{ isEdit ? 'Modifier la session' : 'Nouvelle session' }}
        </h2>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>

          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label class="form-label fw-semibold">Année *</label>
              <input
                v-model.number="form.annee"
                type="number"
                class="form-control"
                placeholder="Ex: 2025"
                min="2000"
                max="2100"
                required
              />
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Diplôme *</label>
              <select v-model="form.diplome_id" class="form-select" required>
                <option value="">-- Choisir un diplôme --</option>
                <option v-for="d in diplomes" :key="d.id" :value="d.id">
                  {{ d.intitule }} ({{ d.filiere?.nom }})
                </option>
              </select>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary flex-grow-1" :disabled="saving">
                <i class="fas fa-save me-1"></i>
                {{ isEdit ? 'Mettre à jour' : 'Créer' }}
              </button>
              <RouterLink to="/admin/sessions" class="btn btn-outline-secondary">Annuler</RouterLink>
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
import { sessionService, diplomeService } from '@/services/index.js'

const router = useRouter()
const route  = useRoute()
const isEdit = computed(() => !!route.params.id)

const diplomes = ref([])
const form     = ref({ annee: new Date().getFullYear(), diplome_id: '' })
const error    = ref(null)
const saving   = ref(false)

onMounted(async () => {
  const res = await diplomeService.getAll()
  diplomes.value = res.data

  if (isEdit.value) {
    const s = await sessionService.getById(route.params.id)
    form.value.annee      = s.data.annee
    form.value.diplome_id = s.data.diplome?.id ?? ''
  }
})

async function handleSubmit() {
  error.value = null
  saving.value = true
  try {
    if (isEdit.value) {
      await sessionService.update(route.params.id, form.value)
    } else {
      await sessionService.create(form.value)
    }
    router.push('/admin/sessions')
  } catch (err) {
    error.value = err.response?.data?.error || 'Erreur.'
  } finally {
    saving.value = false
  }
}
</script>
