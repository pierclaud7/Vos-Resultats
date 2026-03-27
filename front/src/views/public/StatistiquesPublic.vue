<template>
  <div>
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
      <h2 class="fw-bold text-center mb-1">Statistiques de réussite</h2>
      <p class="text-muted text-center mb-4">Taux de réussite publiés, classés par filière et diplôme.</p>

      <!-- Filtre par année -->
      <div class="row justify-content-center mb-4">
        <div class="col-md-4">
          <select v-model="annee" class="form-select" @change="load">
            <option value="">Toutes les années</option>
            <option v-for="a in annees" :key="a" :value="a">{{ a }}</option>
          </select>
        </div>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary"></div>
      </div>

      <div v-else-if="stats.length === 0" class="text-center text-muted py-5">
        <i class="fas fa-chart-bar fa-3x mb-3"></i>
        <p>Aucune statistique disponible pour le moment.</p>
      </div>

      <div v-else class="row g-4">
        <div v-for="filiere in stats" :key="filiere.id" class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white fw-bold">
              <i class="fas fa-layer-group me-2"></i>{{ filiere.nom }}
            </div>
            <div class="card-body">
              <div v-for="diplome in filiere.diplomes" :key="diplome.id" class="mb-4">
                <h6 class="fw-bold text-dark mb-2">{{ diplome.intitule }}</h6>
                <div v-for="session in diplome.sessions" :key="session.id" class="mb-2">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted">{{ session.annee }}</small>
                    <strong class="text-primary">{{ session.tauxReussite }}%</strong>
                  </div>
                  <!-- Barre de progression -->
                  <div class="progress" style="height: 10px;">
                    <div
                      class="progress-bar"
                      :class="barClass(session.tauxReussite)"
                      :style="`width: ${session.tauxReussite}%`"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="bg-white border-top py-4 text-center text-muted small">
      <p class="mb-0">Vos Résultats &copy; {{ new Date().getFullYear() }}</p>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { publicService } from '@/services/index.js'

const stats   = ref([])
const loading = ref(true)
const annee   = ref('')

// Génère les années de 2020 à l'année courante
const currentYear = new Date().getFullYear()
const annees = Array.from({ length: currentYear - 2019 }, (_, i) => currentYear - i)

function barClass(taux) {
  if (taux >= 70) return 'bg-success'
  if (taux >= 40) return 'bg-warning'
  return 'bg-danger'
}

async function load() {
  loading.value = true
  try {
    const res = await publicService.getStatistiques(annee.value || null)
    stats.value = res.data
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>
