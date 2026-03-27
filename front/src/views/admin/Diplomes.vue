<template>
  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 fw-bold mb-0"><i class="fas fa-graduation-cap me-2 text-info"></i> Diplômes</h1>
      <button class="btn btn-primary" @click="openModal()">
        <i class="fas fa-plus me-1"></i> Nouveau diplôme
      </button>
    </div>

    <div v-if="message.text" class="alert" :class="`alert-${message.type}`">{{ message.text }}</div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th class="ps-3">ID</th>
              <th>Intitulé</th>
              <th>Filière</th>
              <th>Sessions</th>
              <th class="text-end pe-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="5" class="text-center py-4"><span class="spinner-border text-primary"></span></td></tr>
            <tr v-else-if="diplomes.length === 0"><td colspan="5" class="text-center py-4 text-muted">Aucun diplôme trouvé.</td></tr>
            <tr v-for="d in diplomes" :key="d.id">
              <td class="ps-3 text-muted fw-bold">#{{ d.id }}</td>
              <td class="fw-bold">{{ d.intitule }}</td>
              <td><span class="badge bg-light text-dark border">{{ d.filiere?.nom ?? '-' }}</span></td>
              <td><span class="badge bg-secondary">{{ d.nbSessions }} session(s)</span></td>
              <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-primary me-1" @click="openModal(d)"><i class="fas fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(d)"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal d-block" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">{{ editingId ? 'Modifier le diplôme' : 'Nouveau diplôme' }}</h5>
            <button class="btn-close btn-close-white" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Intitulé du diplôme</label>
              <input v-model="form.intitule" type="text" class="form-control" placeholder="Ex: BTS SIO" />
            </div>
            <div>
              <label class="form-label fw-semibold">Filière</label>
              <select v-model="form.filiere_id" class="form-select">
                <option value="">-- Choisir une filière --</option>
                <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
              </select>
            </div>
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
import { diplomeService, filiereService } from '@/services/index.js'

const diplomes  = ref([])
const filieres  = ref([])
const loading   = ref(true)
const saving    = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const form      = ref({ intitule: '', filiere_id: '' })
const message   = ref({ text: '', type: 'success' })

async function load() {
  loading.value = true
  try {
    const [d, f] = await Promise.all([diplomeService.getAll(), filiereService.getAll()])
    diplomes.value = d.data
    filieres.value = f.data
  } finally {
    loading.value = false
  }
}

function openModal(diplome = null) {
  editingId.value = diplome?.id ?? null
  form.value      = { intitule: diplome?.intitule ?? '', filiere_id: diplome?.filiere?.id ?? '' }
  showModal.value = true
}

function closeModal() { showModal.value = false; editingId.value = null; form.value = { intitule: '', filiere_id: '' } }

async function save() {
  if (!form.value.intitule.trim() || !form.value.filiere_id) return
  saving.value = true
  try {
    editingId.value
      ? await diplomeService.update(editingId.value, form.value)
      : await diplomeService.create(form.value)
    showMessage(editingId.value ? 'Diplôme mis à jour.' : 'Diplôme créé.', 'success')
    closeModal(); await load()
  } catch (err) {
    showMessage(err.response?.data?.error || 'Erreur.', 'danger')
  } finally { saving.value = false }
}

async function confirmDelete(d) {
  if (!confirm(`Supprimer "${d.intitule}" ?`)) return
  try { await diplomeService.delete(d.id); showMessage('Diplôme supprimé.', 'success'); await load() }
  catch (err) { showMessage(err.response?.data?.error || 'Erreur.', 'danger') }
}

function showMessage(text, type = 'success') {
  message.value = { text, type }
  setTimeout(() => (message.value = { text: '', type: 'success' }), 4000)
}

onMounted(load)
</script>
