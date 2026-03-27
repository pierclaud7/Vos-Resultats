<template>
  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 fw-bold mb-0"><i class="fas fa-layer-group me-2 text-secondary"></i> Filières</h1>
      <button class="btn btn-primary" @click="openModal()">
        <i class="fas fa-plus me-1"></i> Nouvelle filière
      </button>
    </div>

    <!-- Alerte erreur/succès -->
    <div v-if="message.text" class="alert" :class="`alert-${message.type}`" role="alert">
      {{ message.text }}
    </div>

    <!-- Tableau -->
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="ps-3">ID</th>
                <th>Nom</th>
                <th>Diplômes</th>
                <th class="text-end pe-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="4" class="text-center py-4">
                  <span class="spinner-border text-primary"></span>
                </td>
              </tr>
              <tr v-else-if="filieres.length === 0">
                <td colspan="4" class="text-center py-4 text-muted">Aucune filière trouvée.</td>
              </tr>
              <tr v-for="f in filieres" :key="f.id">
                <td class="ps-3 text-muted fw-bold">#{{ f.id }}</td>
                <td class="fw-bold">{{ f.nom }}</td>
                <td><span class="badge bg-info text-dark">{{ f.nbDiplomes }} diplôme(s)</span></td>
                <td class="text-end pe-3">
                  <button class="btn btn-sm btn-outline-primary me-1" @click="openModal(f)">
                    <i class="fas fa-pen"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(f)">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Modal création/édition -->
    <div v-if="showModal" class="modal d-block" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">{{ editingId ? 'Modifier la filière' : 'Nouvelle filière' }}</h5>
            <button class="btn-close btn-close-white" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <label class="form-label fw-semibold">Nom de la filière</label>
            <input v-model="form.nom" type="text" class="form-control" placeholder="Ex: Informatique" required />
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">Annuler</button>
            <button class="btn btn-primary" @click="save" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              {{ editingId ? 'Mettre à jour' : 'Créer' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { filiereService } from '@/services/index.js'

const filieres  = ref([])
const loading   = ref(true)
const saving    = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const form      = ref({ nom: '' })
const message   = ref({ text: '', type: 'success' })

async function load() {
  loading.value = true
  try {
    const res  = await filiereService.getAll()
    filieres.value = res.data
  } finally {
    loading.value = false
  }
}

function openModal(filiere = null) {
  editingId.value = filiere?.id ?? null
  form.value      = { nom: filiere?.nom ?? '' }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
  form.value      = { nom: '' }
}

async function save() {
  if (!form.value.nom.trim()) return
  saving.value = true
  try {
    if (editingId.value) {
      await filiereService.update(editingId.value, form.value)
      showMessage('Filière mise à jour avec succès.', 'success')
    } else {
      await filiereService.create(form.value)
      showMessage('Filière créée avec succès.', 'success')
    }
    closeModal()
    await load()
  } catch (err) {
    showMessage(err.response?.data?.error || 'Une erreur est survenue.', 'danger')
  } finally {
    saving.value = false
  }
}

async function confirmDelete(filiere) {
  if (!confirm(`Supprimer la filière "${filiere.nom}" ?`)) return
  try {
    await filiereService.delete(filiere.id)
    showMessage('Filière supprimée.', 'success')
    await load()
  } catch (err) {
    showMessage(err.response?.data?.error || 'Erreur lors de la suppression.', 'danger')
  }
}

function showMessage(text, type = 'success') {
  message.value = { text, type }
  setTimeout(() => (message.value = { text: '', type: 'success' }), 4000)
}

onMounted(load)
</script>
