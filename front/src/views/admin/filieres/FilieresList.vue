<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0"><i class="fas fa-layer-group me-2"></i>Filières</h2>
      <RouterLink to="/admin/filieres/new" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Nouvelle filière
      </RouterLink>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <div v-else-if="filieres.length === 0" class="text-center py-5 text-muted">
          <i class="fas fa-folder-open fa-3x mb-3"></i>
          <p>Aucune filière. Créez-en une !</p>
        </div>

        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">ID</th>
              <th>Nom</th>
              <th>Diplômes</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="f in filieres" :key="f.id">
              <td class="ps-4 text-muted">#{{ f.id }}</td>
              <td class="fw-bold">{{ f.nom }}</td>
              <td>
                <span class="badge bg-info text-dark">{{ f.nbDiplomes }} diplôme(s)</span>
              </td>
              <td class="text-end pe-4">
                <RouterLink :to="`/admin/filieres/${f.id}/edit`" class="btn btn-sm btn-outline-primary me-1">
                  <i class="fas fa-pen"></i>
                </RouterLink>
                <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(f)">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal confirmation suppression -->
    <div v-if="toDelete" class="modal d-block bg-dark bg-opacity-50" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title">Confirmer la suppression</h5>
            <button class="btn-close" @click="toDelete = null"></button>
          </div>
          <div class="modal-body">
            Supprimer la filière <strong>{{ toDelete.nom }}</strong> ? Cette action est irréversible.
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="toDelete = null">Annuler</button>
            <button class="btn btn-danger" :disabled="deleting" @click="handleDelete">
              <span v-if="deleting"><span class="spinner-border spinner-border-sm me-1"></span></span>
              Supprimer
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

const filieres = ref([])
const loading  = ref(true)
const toDelete = ref(null)
const deleting = ref(false)

async function load() {
  loading.value = true
  try {
    const res = await filiereService.getAll()
    filieres.value = res.data
  } finally {
    loading.value = false
  }
}

function confirmDelete(filiere) {
  toDelete.value = filiere
}

async function handleDelete() {
  deleting.value = true
  try {
    await filiereService.delete(toDelete.value.id)
    toDelete.value = null
    await load()
  } catch (err) {
    alert(err.response?.data?.error || 'Erreur lors de la suppression.')
    toDelete.value = null
  } finally {
    deleting.value = false
  }
}

onMounted(load)
</script>
