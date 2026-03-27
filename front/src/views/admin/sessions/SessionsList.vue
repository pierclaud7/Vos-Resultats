<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0"><i class="fas fa-calendar-alt me-2"></i>Sessions d'examen</h2>
      <RouterLink to="/admin/sessions/new" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> Nouvelle session
      </RouterLink>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <div v-else-if="sessions.length === 0" class="text-center py-5 text-muted">
          <i class="fas fa-calendar-times fa-3x mb-3"></i>
          <p>Aucune session créée.</p>
        </div>

        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Année</th>
              <th>Diplôme</th>
              <th>Filière</th>
              <th>Statut</th>
              <th>Taux réussite</th>
              <th>Étudiants</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in sessions" :key="s.id">
              <td class="ps-4 fw-bold">{{ s.annee }}</td>
              <td>{{ s.diplome?.intitule ?? '—' }}</td>
              <td><span class="badge bg-info text-dark">{{ s.diplome?.filiere?.nom ?? '—' }}</span></td>
              <td>
                <span class="badge" :class="statutClass(s.statut)">{{ s.statut }}</span>
              </td>
              <td>
                <span v-if="s.tauxReussite !== null" class="fw-bold text-primary">
                  {{ s.tauxReussite }}%
                </span>
                <span v-else class="text-muted">—</span>
              </td>
              <td>{{ s.nbEtudiants }}</td>
              <td class="text-end pe-4">
                <RouterLink :to="`/admin/sessions/${s.id}`" class="btn btn-sm btn-outline-secondary me-1" title="Voir">
                  <i class="fas fa-eye"></i>
                </RouterLink>
                <RouterLink :to="`/admin/sessions/${s.id}/edit`" class="btn btn-sm btn-outline-primary me-1" title="Modifier">
                  <i class="fas fa-pen"></i>
                </RouterLink>
                <button
                  v-if="s.statut !== 'Publié'"
                  class="btn btn-sm btn-outline-danger"
                  @click="confirmDelete(s)"
                  title="Supprimer"
                >
                  <i class="fas fa-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal suppression -->
    <div v-if="toDelete" class="modal d-block bg-dark bg-opacity-50">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title">Confirmer la suppression</h5>
            <button class="btn-close" @click="toDelete = null"></button>
          </div>
          <div class="modal-body">
            Supprimer la session <strong>{{ toDelete.diplome?.intitule }} {{ toDelete.annee }}</strong> ?
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
import { sessionService } from '@/services/index.js'

const sessions = ref([])
const loading  = ref(true)
const toDelete = ref(null)

function statutClass(statut) {
  return {
    'bg-success': statut === 'Publié',
    'bg-info text-dark': statut === 'Validé',
    'bg-secondary': statut === 'Brouillon',
  }
}

async function load() {
  loading.value = true
  const res = await sessionService.getAll()
  sessions.value = res.data
  loading.value = false
}

function confirmDelete(s) { toDelete.value = s }

async function handleDelete() {
  try {
    await sessionService.delete(toDelete.value.id)
    toDelete.value = null
    await load()
  } catch (err) {
    alert(err.response?.data?.error || 'Erreur.')
    toDelete.value = null
  }
}

onMounted(load)
</script>
