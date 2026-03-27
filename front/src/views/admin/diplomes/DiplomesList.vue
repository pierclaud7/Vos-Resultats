<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0"><i class="fas fa-graduation-cap me-2"></i>Diplômes</h2>
      <RouterLink to="/admin/diplomes/new" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Nouveau diplôme
      </RouterLink>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <div v-else-if="diplomes.length === 0" class="text-center py-5 text-muted">
          <i class="fas fa-graduation-cap fa-3x mb-3"></i>
          <p>Aucun diplôme. Créez-en un !</p>
        </div>

        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">ID</th>
              <th>Intitulé</th>
              <th>Filière</th>
              <th>Sessions</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="d in diplomes" :key="d.id">
              <td class="ps-4 text-muted">#{{ d.id }}</td>
              <td class="fw-bold">{{ d.intitule }}</td>
              <td>
                <span class="badge bg-info text-dark">{{ d.filiere?.nom ?? '—' }}</span>
              </td>
              <td>
                <span class="badge bg-secondary">{{ d.nbSessions }} session(s)</span>
              </td>
              <td class="text-end pe-4">
                <RouterLink :to="`/admin/diplomes/${d.id}/edit`" class="btn btn-sm btn-outline-primary me-1">
                  <i class="fas fa-pen"></i>
                </RouterLink>
                <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(d)">
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="toDelete" class="modal d-block bg-dark bg-opacity-50">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title">Confirmer la suppression</h5>
            <button class="btn-close" @click="toDelete = null"></button>
          </div>
          <div class="modal-body">
            Supprimer le diplôme <strong>{{ toDelete.intitule }}</strong> ?
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="toDelete = null">Annuler</button>
            <button class="btn btn-danger" @click="handleDelete">Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { diplomeService } from '@/services/index.js'

const diplomes = ref([])
const loading  = ref(true)
const toDelete = ref(null)

async function load() {
  loading.value = true
  const res = await diplomeService.getAll()
  diplomes.value = res.data
  loading.value = false
}

function confirmDelete(d) { toDelete.value = d }

async function handleDelete() {
  try {
    await diplomeService.delete(toDelete.value.id)
    toDelete.value = null
    await load()
  } catch (err) {
    alert(err.response?.data?.error || 'Erreur.')
    toDelete.value = null
  }
}

onMounted(load)
</script>
