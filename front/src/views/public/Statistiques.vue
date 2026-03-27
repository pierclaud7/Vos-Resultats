<template>
  <div class="container py-5">
    <h1 class="fw-bold mb-2 text-center"><i class="fas fa-chart-bar me-2 text-primary"></i>Statistiques de réussite</h1>
    <p class="text-center text-muted mb-4">Taux de réussite publiés, classés par filière et diplôme</p>

    <div class="d-flex justify-content-center mb-4">
      <select v-model="filtreAnnee" class="form-select w-auto" @change="load">
        <option value="">Toutes les années</option>
        <option v-for="a in annees" :key="a" :value="a">{{ a }}</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-5"><span class="spinner-border text-primary"></span></div>
    <div v-else-if="stats.length === 0" class="text-center text-muted py-5">Aucun résultat publié pour le moment.</div>

    <div v-for="filiere in stats" :key="filiere.id" class="mb-4">
      <h4 class="fw-bold border-bottom pb-2 mb-3">
        <i class="fas fa-layer-group text-secondary me-2"></i>{{ filiere.nom }}
      </h4>
      <div class="row g-3">
        <div class="col-md-6 col-lg-4" v-for="diplome in filiere.diplomes" :key="diplome.id">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold">{{ diplome.intitule }}</div>
            <div class="card-body">
              <div v-for="session in diplome.sessions" :key="session.id" class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                  <small class="text-muted">{{ session.annee }}</small>
                  <small class="fw-bold" :class="tauxColor(session.tauxReussite)">{{ session.tauxReussite }}%</small>
                </div>
                <div class="progress" style="height:8px">
                  <div class="progress-bar" :class="tauxBg(session.tauxReussite)"
                    :style="`width:${session.tauxReussite}%`"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { publicService } from '@/services/index.js'

const stats      = ref([])
const loading    = ref(true)
const filtreAnnee = ref('')
const annees     = ref([2022, 2023, 2024, 2025, 2026])

async function load() {
  loading.value = true
  try { const r = await publicService.getStatistiques(filtreAnnee.value || null); stats.value = r.data }
  finally { loading.value = false }
}

function tauxColor(t) { return t >= 70 ? 'text-success' : t >= 50 ? 'text-warning' : 'text-danger' }
function tauxBg(t)    { return t >= 70 ? 'bg-success'  : t >= 50 ? 'bg-warning'  : 'bg-danger' }

onMounted(load)
</script>
