<template>
  <div>
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary shadow-sm">
      <div class="container">
        <RouterLink class="navbar-brand fw-bold" to="/">
          <i class="fas fa-graduation-cap me-2"></i>Vos Résultats
        </RouterLink>
        <RouterLink to="/" class="btn btn-outline-light btn-sm">
          <i class="fas fa-arrow-left me-1"></i> Accueil
        </RouterLink>
      </div>
    </nav>

    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-7">
          <h2 class="fw-bold mb-1 text-center">Consulter vos résultats</h2>
          <p class="text-muted text-center mb-4">Suivez les étapes pour accéder à votre résultat personnel.</p>

          <!-- ÉTAPE 1 : Filière -->
          <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
              <span class="badge bg-primary me-2">1</span>
              <strong>Choisissez votre filière</strong>
            </div>
            <div class="card-body">
              <div v-if="loadingFilieres" class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-primary"></div>
              </div>
              <div v-else class="d-flex flex-wrap gap-2">
                <button
                  v-for="f in filieres"
                  :key="f.id"
                  class="btn"
                  :class="selectedFiliere?.id === f.id ? 'btn-primary' : 'btn-outline-primary'"
                  @click="selectFiliere(f)"
                >
                  {{ f.nom }}
                </button>
                <div v-if="filieres.length === 0" class="text-muted small">
                  Aucun résultat publié pour le moment.
                </div>
              </div>
            </div>
          </div>

          <!-- ÉTAPE 2 : Diplôme -->
          <div class="card border-0 shadow-sm mb-3" :class="!selectedFiliere ? 'opacity-50' : ''">
            <div class="card-header bg-white">
              <span class="badge bg-primary me-2">2</span>
              <strong>Choisissez votre diplôme</strong>
            </div>
            <div class="card-body">
              <div v-if="loadingDiplomes" class="text-center py-2">
                <div class="spinner-border spinner-border-sm text-primary"></div>
              </div>
              <div v-else-if="diplomes.length > 0">
                <div v-for="d in diplomes" :key="d.id" class="mb-2">
                  <p class="fw-semibold mb-1">{{ d.intitule }}</p>
                  <div class="d-flex flex-wrap gap-2">
                    <button
                      v-for="s in d.sessions"
                      :key="s.id"
                      class="btn btn-sm"
                      :class="selectedSession?.id === s.id ? 'btn-success' : 'btn-outline-success'"
                      @click="selectSession(s, d)"
                    >
                      {{ s.annee }}
                    </button>
                  </div>
                </div>
              </div>
              <p v-else-if="selectedFiliere" class="text-muted small mb-0">
                Aucun diplôme disponible pour cette filière.
              </p>
              <p v-else class="text-muted small mb-0">Sélectionnez d'abord une filière.</p>
            </div>
          </div>

          <!-- ÉTAPE 3 : Email -->
          <div class="card border-0 shadow-sm mb-3" :class="!selectedSession ? 'opacity-50' : ''">
            <div class="card-header bg-white">
              <span class="badge bg-primary me-2">3</span>
              <strong>Entrez votre email</strong>
            </div>
            <div class="card-body">
              <form @submit.prevent="handleSearch" class="d-flex gap-2">
                <input
                  v-model="email"
                  type="email"
                  class="form-control"
                  placeholder="votre.email@example.com"
                  :disabled="!selectedSession"
                  required
                />
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="!selectedSession || searching"
                >
                  <span v-if="searching"><span class="spinner-border spinner-border-sm"></span></span>
                  <span v-else><i class="fas fa-search"></i></span>
                </button>
              </form>
            </div>
          </div>

          <!-- RÉSULTAT -->
          <div v-if="resultat" class="card border-0 shadow-sm">
            <div class="card-header text-white fw-bold" :class="resultatHeaderClass">
              <i :class="resultatIcon" class="me-2"></i>
              {{ resultat.resultat }}
            </div>
            <div class="card-body">
              <h5 class="fw-bold">{{ resultat.prenom }} {{ resultat.nom }}</h5>
              <p class="text-muted mb-1">
                <strong>Diplôme :</strong> {{ resultat.session?.diplome }} ({{ resultat.session?.annee }})
              </p>
              <p class="text-muted mb-1">
                <strong>Filière :</strong> {{ resultat.session?.filiere }}
              </p>
              <p v-if="resultat.moyenne" class="mb-0">
                <strong>Moyenne :</strong>
                <span class="fw-bold ms-1">{{ resultat.moyenne }}/20</span>
              </p>
            </div>
          </div>

          <div v-if="searchError" class="alert alert-danger mt-3">
            <i class="fas fa-exclamation-circle me-2"></i>{{ searchError }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { publicService } from '@/services/index.js'

const filieres        = ref([])
const diplomes        = ref([])
const selectedFiliere = ref(null)
const selectedSession = ref(null)
const email           = ref('')
const resultat        = ref(null)
const searchError     = ref(null)
const loadingFilieres = ref(true)
const loadingDiplomes = ref(false)
const searching       = ref(false)

const resultatHeaderClass = computed(() => {
  const r = resultat.value?.resultat
  if (r === 'Admis')     return 'bg-success'
  if (r === 'Rattrapage') return 'bg-warning'
  if (r === 'Refusé')    return 'bg-danger'
  return 'bg-secondary'
})

const resultatIcon = computed(() => {
  const r = resultat.value?.resultat
  if (r === 'Admis')     return 'fas fa-check-circle'
  if (r === 'Rattrapage') return 'fas fa-exclamation-triangle'
  return 'fas fa-times-circle'
})

async function loadFilieres() {
  loadingFilieres.value = true
  try {
    const res = await publicService.getFilieres()
    filieres.value = res.data
  } finally {
    loadingFilieres.value = false
  }
}

async function selectFiliere(f) {
  selectedFiliere.value = f
  selectedSession.value = null
  diplomes.value = []
  resultat.value = null
  searchError.value = null
  loadingDiplomes.value = true
  try {
    const res = await publicService.getDiplomesFiliere(f.id)
    diplomes.value = res.data
  } finally {
    loadingDiplomes.value = false
  }
}

function selectSession(s, diplome) {
  selectedSession.value = { ...s, diplome }
  resultat.value = null
  searchError.value = null
}

async function handleSearch() {
  resultat.value = null
  searchError.value = null
  searching.value = true
  try {
    const res = await publicService.getResultat(selectedSession.value.id, email.value)
    resultat.value = res.data
  } catch (err) {
    searchError.value = err.response?.data?.error || 'Erreur lors de la recherche.'
  } finally {
    searching.value = false
  }
}

loadFilieres()
</script>
