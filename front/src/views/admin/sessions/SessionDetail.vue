<template>
  <div v-if="loading" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
  </div>

  <div v-else-if="session">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center gap-2">
        <RouterLink to="/admin/sessions" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-arrow-left"></i>
        </RouterLink>
        <div>
          <h2 class="h4 fw-bold mb-0">
            {{ session.diplome?.intitule }} — {{ session.annee }}
          </h2>
          <span class="badge" :class="statutClass(session.statut)">{{ session.statut }}</span>
        </div>
      </div>
      <RouterLink :to="`/admin/sessions/${session.id}/edit`" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-pen me-1"></i> Modifier
      </RouterLink>
    </div>

    <div class="row g-3 mb-4">
      <!-- Infos session -->
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="fw-bold text-muted mb-3">Informations</h6>
            <p class="mb-1"><strong>Filière :</strong> {{ session.diplome?.filiere?.nom ?? '—' }}</p>
            <p class="mb-1"><strong>Diplôme :</strong> {{ session.diplome?.intitule ?? '—' }}</p>
            <p class="mb-1"><strong>Année :</strong> {{ session.annee }}</p>
            <p class="mb-0"><strong>Étudiants :</strong> {{ etudiants.length }}</p>
          </div>
        </div>
      </div>

      <!-- Taux de réussite -->
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body text-center">
            <h6 class="fw-bold text-muted mb-3">Taux de réussite</h6>
            <div v-if="session.tauxReussite !== null" class="display-4 fw-bold text-primary">
              {{ session.tauxReussite }}%
            </div>
            <div v-else class="text-muted">Non calculé</div>
            <small class="text-muted">
              {{ nbAdmis }} admis sur {{ etudiants.length }} étudiant(s)
            </small>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="fw-bold text-muted mb-3">Actions</h6>
            <div class="d-grid gap-2">
              <!-- Étape 1 : Valider -->
              <button
                class="btn btn-warning"
                :disabled="session.statut === 'Publié' || actionLoading"
                @click="handleValider"
              >
                <i class="fas fa-check-circle me-1"></i> Valider & calculer
              </button>

              <!-- Étape 2 : Publier -->
              <button
                class="btn btn-success"
                :disabled="session.statut !== 'Validé' || actionLoading"
                @click="handlePublier"
              >
                <i class="fas fa-globe me-1"></i> Publier les résultats
              </button>

              <!-- Partage réseaux sociaux simulé -->
              <button
                class="btn btn-outline-primary"
                :disabled="session.tauxReussite === null"
                @click="showShareModal = true"
              >
                <i class="fas fa-share-alt me-1"></i> Partager le taux
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Liste des étudiants de la session -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="fas fa-users me-2"></i>Étudiants de cette session</h6>
        <RouterLink :to="`/admin/etudiants/new?session_id=${session.id}`" class="btn btn-sm btn-primary">
          <i class="fas fa-user-plus me-1"></i> Ajouter
        </RouterLink>
      </div>
      <div class="card-body p-0">
        <div v-if="loadingEtudiants" class="text-center py-4">
          <div class="spinner-border spinner-border-sm text-primary"></div>
        </div>
        <table v-else-if="etudiants.length > 0" class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Nom</th>
              <th>Prénom</th>
              <th>Email</th>
              <th>Moyenne</th>
              <th>Résultat</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in etudiants" :key="e.id">
              <td class="ps-4 fw-bold text-uppercase">{{ e.nom }}</td>
              <td>{{ e.prenom }}</td>
              <td class="text-muted small">{{ e.email }}</td>
              <td>
                <span v-if="e.moyenne !== null" class="fw-bold">{{ e.moyenne }}/20</span>
                <span v-else class="text-muted">—</span>
              </td>
              <td>
                <span class="badge" :class="resultatClass(e.resultat)">{{ e.resultat }}</span>
              </td>
              <td class="text-end pe-4">
                <RouterLink :to="`/admin/etudiants/${e.id}/edit`" class="btn btn-sm btn-outline-primary">
                  <i class="fas fa-pen"></i>
                </RouterLink>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-else class="text-center py-4 text-muted">
          Aucun étudiant dans cette session.
        </div>
      </div>
    </div>

    <!-- MODAL PARTAGE SOCIAL (simulation) -->
    <div v-if="showShareModal" class="modal d-block bg-dark bg-opacity-50">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i>Partager sur les réseaux</h5>
            <button class="btn-close btn-close-white" @click="showShareModal = false"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small mb-3">Texte à publier :</p>
            <div class="bg-light rounded p-3 font-monospace small">
              {{ shareText }}
            </div>
            <p class="text-muted small mt-3 mb-2">URL factice :</p>
            <div class="input-group">
              <input type="text" class="form-control form-control-sm" :value="shareUrl" readonly />
              <button class="btn btn-outline-secondary btn-sm" @click="copyUrl">
                <i class="fas fa-copy"></i>
              </button>
            </div>
            <div v-if="copied" class="text-success small mt-1">
              <i class="fas fa-check me-1"></i> Copié !
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary w-100" @click="showShareModal = false">
              <i class="fas fa-check me-1"></i> Fermer
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { sessionService, etudiantService } from '@/services/index.js'

const route = useRoute()
const id    = route.params.id

const session        = ref(null)
const etudiants      = ref([])
const loading        = ref(true)
const loadingEtudiants = ref(true)
const actionLoading  = ref(false)
const showShareModal = ref(false)
const copied         = ref(false)

const nbAdmis = computed(() => etudiants.value.filter(e => e.estAdmis).length)

const shareText = computed(() => {
  if (!session.value) return ''
  return `🎓 Résultats ${session.value.diplome?.intitule} ${session.value.annee} — Taux de réussite : ${session.value.tauxReussite}% ! Félicitations à tous les admis ! #VosResultats #${session.value.diplome?.filiere?.nom?.replace(/\s/g, '')}`
})

const shareUrl = computed(() => {
  if (!session.value) return ''
  return `https://vos-resultats.fr/resultats/${session.value.diplome?.filiere?.id}/${session.value.diplome?.id}?annee=${session.value.annee}`
})

function statutClass(statut) {
  return {
    'bg-success': statut === 'Publié',
    'bg-info text-dark': statut === 'Validé',
    'bg-secondary': statut === 'Brouillon',
  }
}

function resultatClass(r) {
  return {
    'bg-success':          r === 'Admis',
    'bg-warning text-dark': r === 'Rattrapage',
    'bg-danger':           r === 'Refusé',
    'bg-secondary':        r === 'En attente',
  }
}

async function loadSession() {
  const res = await sessionService.getById(id)
  session.value = res.data
}

async function loadEtudiants() {
  loadingEtudiants.value = true
  const res = await etudiantService.getAll(id)
  etudiants.value = res.data
  loadingEtudiants.value = false
}

async function handleValider() {
  actionLoading.value = true
  try {
    await sessionService.valider(id)
    await loadSession()
    await loadEtudiants()
  } catch (err) {
    alert(err.response?.data?.error || 'Erreur lors de la validation.')
  } finally {
    actionLoading.value = false
  }
}

async function handlePublier() {
  actionLoading.value = true
  try {
    await sessionService.publier(id)
    await loadSession()
  } catch (err) {
    alert(err.response?.data?.error || 'Erreur lors de la publication.')
  } finally {
    actionLoading.value = false
  }
}

function copyUrl() {
  navigator.clipboard.writeText(shareUrl.value)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

onMounted(async () => {
  await Promise.all([loadSession(), loadEtudiants()])
  loading.value = false
})
</script>
