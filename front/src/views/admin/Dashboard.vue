<template>
  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 fw-bold mb-0">Tableau de bord</h1>
        <p class="text-muted small mb-0">Bienvenue, {{ auth.user?.email }}</p>
      </div>
      <span class="badge bg-light text-dark border px-3 py-2">
        <i class="far fa-calendar-alt me-1"></i> {{ new Date().toLocaleDateString('fr-FR') }}
      </span>
    </div>

    <div class="row mb-4">
      <div class="col-md-3 mb-3" v-for="stat in stats" :key="stat.label">
        <div class="card border-0 shadow-sm h-100" :class="`border-start border-4 border-${stat.color}`">
          <div class="card-body">
            <div class="text-uppercase small fw-bold mb-1" :class="`text-${stat.color}`">{{ stat.label }}</div>
            <div class="h4 fw-bold mb-0">
              <span v-if="loading" class="spinner-border spinner-border-sm"></span>
              <span v-else>{{ stat.value }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-tasks text-primary me-2"></i> Gestion des examens
          </div>
          <div class="card-body d-grid gap-3">
            <router-link to="/admin/sessions" class="btn btn-outline-primary text-start p-3 d-flex justify-content-between align-items-center">
              <span><i class="fas fa-calendar-check text-warning fa-lg me-3"></i><strong>Sessions d'examen</strong></span>
              <i class="fas fa-chevron-right text-muted"></i>
            </router-link>
            <router-link to="/admin/etudiants" class="btn btn-outline-primary text-start p-3 d-flex justify-content-between align-items-center">
              <span><i class="fas fa-user-graduate text-success fa-lg me-3"></i><strong>Étudiants & Résultats</strong></span>
              <i class="fas fa-chevron-right text-muted"></i>
            </router-link>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-cogs me-2"></i> Configuration
          </div>
          <div class="list-group list-group-flush">
            <router-link to="/admin/filieres" class="list-group-item list-group-item-action d-flex justify-content-between">
              <span><i class="fas fa-layer-group text-secondary me-2"></i> Filières</span>
              <i class="fas fa-arrow-right text-muted"></i>
            </router-link>
            <router-link to="/admin/diplomes" class="list-group-item list-group-item-action d-flex justify-content-between">
              <span><i class="fas fa-graduation-cap text-info me-2"></i> Diplômes</span>
              <i class="fas fa-arrow-right text-muted"></i>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { filiereService, diplomeService, sessionService, etudiantService } from '@/services/index.js'

const auth    = useAuthStore()
const loading = ref(true)
const stats   = ref([
  { label: 'Filières',  value: 0, color: 'info'    },
  { label: 'Diplômes',  value: 0, color: 'primary' },
  { label: 'Sessions',  value: 0, color: 'warning' },
  { label: 'Étudiants', value: 0, color: 'success' },
])

onMounted(async () => {
  try {
    const [f, d, s, e] = await Promise.all([
      filiereService.getAll(), diplomeService.getAll(),
      sessionService.getAll(), etudiantService.getAll(),
    ])
    stats.value[0].value = f.data.length
    stats.value[1].value = d.data.length
    stats.value[2].value = s.data.length
    stats.value[3].value = e.data.length
  } finally {
    loading.value = false
  }
})
</script>
