<template>
  <div class="stats-page">
    <div class="page-header">
      <div class="page-header-inner">
        <h1 class="page-title">Statistiques de réussite</h1>
        <p class="page-sub">Taux de réussite publiés, classés par filière et diplôme</p>
        <div class="filter-row">
          <label class="filter-label">Filtrer par année</label>
          <select v-model="filtreAnnee" class="filter-select" @change="load">
            <option value="">Toutes les années</option>
            <option v-for="a in annees" :key="a" :value="a">{{ a }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="page-body">
      <div v-if="loading" class="loading-center">
        <div class="spinner-md"></div>
        <p>Chargement des statistiques...</p>
      </div>

      <div v-else-if="!stats.length" class="empty-state">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/></svg>
        <p>Aucun résultat publié pour le moment.</p>
      </div>

      <div v-else class="filieres-list">
        <div class="filiere-section" v-for="filiere in stats" :key="filiere.id">
          <h2 class="filiere-title">{{ filiere.nom }}</h2>
          <div class="diplomes-grid">
            <div class="diplome-card" v-for="diplome in filiere.diplomes" :key="diplome.id">
              <div class="diplome-header">{{ diplome.intitule }}</div>
              <div class="sessions-list">
                <div class="session-row" v-for="session in diplome.sessions" :key="session.id">
                  <div class="session-year">{{ session.annee }}</div>
                  <div class="session-bar-container">
                    <div class="session-bar" :style="{ width: session.tauxReussite + '%' }" :class="tauxClass(session.tauxReussite)"></div>
                  </div>
                  <div class="session-taux" :class="tauxTextClass(session.tauxReussite)">{{ session.tauxReussite }}%</div>
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

const stats = ref([]); const loading = ref(true); const filtreAnnee = ref('')
const annees = [2022, 2023, 2024, 2025, 2026]

async function load() {
  loading.value = true
  try { stats.value = (await publicService.getStatistiques(filtreAnnee.value || null)).data }
  finally { loading.value = false }
}

function tauxClass(t)     { return t >= 70 ? 'bar-green' : t >= 50 ? 'bar-yellow' : 'bar-red' }
function tauxTextClass(t) { return t >= 70 ? 'taux-green' : t >= 50 ? 'taux-yellow' : 'taux-red' }

onMounted(load)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

.stats-page { min-height: calc(100vh - 60px); background: #F8FAFC; }

.page-header { background: #0F172A; padding: 48px 24px 40px; }
.page-header-inner { max-width: 1000px; margin: 0 auto; }
.page-title { font-size: 30px; font-weight: 800; color: white; margin: 0 0 10px; letter-spacing: -0.5px; }
.page-sub { font-size: 15px; color: #64748B; margin: 0 0 28px; }

.filter-row { display: flex; align-items: center; gap: 14px; }
.filter-label { font-size: 13px; font-weight: 600; color: #94A3B8; white-space: nowrap; }
.filter-select { padding: 9px 14px; border: 1.5px solid rgba(255,255,255,0.1); border-radius: 8px; font-size: 13.5px; color: white; background: rgba(255,255,255,0.07); outline: none; font-family: 'DM Sans', sans-serif; cursor: pointer; }
.filter-select option { background: #1E293B; color: white; }

.page-body { max-width: 1000px; margin: 0 auto; padding: 32px 24px; }

.loading-center { text-align: center; padding: 64px; color: #94A3B8; }
.spinner-md { width: 32px; height: 32px; border: 3px solid #E2E8F0; border-top-color: #2563EB; border-radius: 50%; animation: spin 0.7s linear infinite; margin: 0 auto 16px; }
@keyframes spin { to { transform: rotate(360deg); } }

.empty-state { text-align: center; padding: 64px; color: #94A3B8; display: flex; flex-direction: column; align-items: center; gap: 12px; }

.filieres-list { display: flex; flex-direction: column; gap: 32px; }

.filiere-section {}

.filiere-title { font-size: 18px; font-weight: 800; color: #0F172A; margin: 0 0 16px; padding-bottom: 12px; border-bottom: 2px solid #E2E8F0; letter-spacing: -0.3px; }

.diplomes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }

.diplome-card { background: white; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; }

.diplome-header { padding: 16px 20px; font-size: 14px; font-weight: 700; color: #0F172A; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; }

.sessions-list { padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; }

.session-row { display: flex; align-items: center; gap: 12px; }
.session-year { font-size: 13px; font-weight: 700; color: #374151; width: 36px; flex-shrink: 0; }

.session-bar-container { flex: 1; height: 8px; background: #F1F5F9; border-radius: 20px; overflow: hidden; }
.session-bar { height: 100%; border-radius: 20px; transition: width 0.6s ease; }
.bar-green  { background: #059669; }
.bar-yellow { background: #D97706; }
.bar-red    { background: #DC2626; }

.session-taux { font-size: 13px; font-weight: 700; width: 36px; text-align: right; flex-shrink: 0; }
.taux-green  { color: #059669; }
.taux-yellow { color: #D97706; }
.taux-red    { color: #DC2626; }

@media (max-width: 640px) { .diplomes-grid { grid-template-columns: 1fr; } }
</style>
