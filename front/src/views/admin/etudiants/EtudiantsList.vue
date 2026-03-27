<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="h4 fw-bold mb-0"><i class="fas fa-users me-2"></i>Étudiants</h2>
      <RouterLink to="/admin/etudiants/new" class="btn btn-primary btn-sm">
        <i class="fas fa-user-plus me-1"></i> Inscrire
      </RouterLink>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <div v-else-if="etudiants.length === 0" class="text-center py-5 text-muted">
          <i class="fas fa-user-graduate fa-3x mb-3"></i>
          <p>Aucun étudiant inscrit.</p>
        </div>

        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Nom</th>
              <th>Prénom</th>
              <th>Session</th>
              <th>Moyenne</th>
              <th>Résultat</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in etudiants" :key="e.id">
              <td class="ps-4 fw-bold text-uppercase">{{ e.nom }}</td>
              <td>{{ e.prenom }}</td>
              <td>
                <span class="badge bg-light text-dark border" v-if="e.session">
                  {{ e.session.diplome?.intitule }} ({{ e.session.annee }})
                </span>
                <span v-else class="text-muted">—</span>
              </td>
              <td>
                <span v-if="e.moyenne !== null" class="fw-bold" :class="moyenneClass(e)">
                  {{ e.moyenne }}/20
                </span>
                <span v-else class="text-muted small">Non noté</span>
              </td>
              <td>
                <span class="badge" :class="resultatClass(e.resultat)">{{ e.resultat }}</span>
              </td>
              <td class="text-end pe-4">
                <RouterLink :to="`/admin/etudiants/${e.id}/edit`" class="btn btn-sm btn-outline-primary me-1">
                  <i class="fas fa-pen"></i>
                </RouterLink>
                <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(e)">
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
            Supprimer <strong>{{ toDelete.prenom }} {{ toDelete.nom }}</strong> ?
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
import { etudiantService } from '@/services/index.js'

const etudiants = ref([])
const loading   = ref(true)
const toDelete  = ref(null)

function moyenneClass(e) {
  if (e.estAdmis || e.moyenne >= 10) return 'text-success'
  if (e.moyenne >= 8) return 'text-warning'
  return 'text-danger'
}

function resultatClass(r) {
  return {
    'bg-success':           r === 'Admis',
    'bg-warning text-dark': r === 'Rattrapage',
    'bg-danger':            r === 'Refusé',
    'bg-secondary':         r === 'En attente',
  }
}

async function load() {
  loading.value = true
  const res = await etudiantService.getAll()
  etudiants.value = res.data
  loading.value = false
}

function confirmDelete(e) { toDelete.value = e }

async function handleDelete() {
  try {
    await etudiantService.delete(toDelete.value.id)
    toDelete.value = null
    await load()
  } catch (err) {
    alert(err.response?.data?.error || 'Erreur.')
    toDelete.value = null
  }
}

onMounted(load)
</script>
