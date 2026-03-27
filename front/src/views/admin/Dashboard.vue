<template>
  <div class="page">
    <!-- Top bar -->
    <div class="topbar">
      <div>
        <h1 class="page-title">Tableau de bord</h1>
        <p class="page-sub">Bienvenue, {{ auth.user?.email }}</p>
      </div>
      <div class="topbar-date">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        {{ new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card" v-for="stat in stats" :key="stat.label">
        <div class="stat-icon" :style="{ background: stat.bg }">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-html="stat.svg"></svg>
        </div>
        <div class="stat-body">
          <div class="stat-value">
            <span v-if="loading" class="skeleton-val"></span>
            <span v-else>{{ stat.value }}</span>
          </div>
          <div class="stat-label">{{ stat.label }}</div>
        </div>
      </div>
    </div>

    <!-- Actions rapides -->
    <div class="grid-2">
      <div class="card">
        <div class="card-head">
          <h3 class="card-title">Gestion des examens</h3>
        </div>
        <div class="action-list">
          <router-link to="/admin/sessions" class="action-item">
            <div class="action-icon yellow">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="action-body">
              <div class="action-title">Sessions d'examen</div>
              <div class="action-sub">Gérer, valider et publier les sessions</div>
            </div>
            <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>

          <router-link to="/admin/etudiants" class="action-item">
            <div class="action-icon green">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="action-body">
              <div class="action-title">Étudiants & Résultats</div>
              <div class="action-sub">Inscrire et noter les étudiants</div>
            </div>
            <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>

          <router-link to="/admin/import-csv" class="action-item">
            <div class="action-icon blue">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </div>
            <div class="action-body">
              <div class="action-title">Import CSV</div>
              <div class="action-sub">Importer une liste d'étudiants</div>
            </div>
            <svg class="action-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <h3 class="card-title">Configuration</h3>
        </div>
        <div class="config-list">
          <router-link to="/admin/filieres" class="config-item">
            <span>Filières</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>
          <router-link to="/admin/diplomes" class="config-item">
            <span>Diplômes</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>
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

const stats = ref([
  { label: 'Filières',  value: 0, bg: '#EFF6FF', svg: '<path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>' },
  { label: 'Diplômes',  value: 0, bg: '#F0FDF4', svg: '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>' },
  { label: 'Sessions',  value: 0, bg: '#FFFBEB', svg: '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>' },
  { label: 'Étudiants', value: 0, bg: '#FFF1F2', svg: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>' },
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
  } finally { loading.value = false }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

.page { padding: 50px; max-width: 2000px; }

.topbar { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
.page-title { font-size: 22px; font-weight: 700; color: #0F172A; margin: 0 0 4px; }
.page-sub { font-size: 13.5px; color: #64748B; margin: 0; }
.topbar-date { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #94A3B8; background: white; padding: 8px 14px; border-radius: 8px; border: 1px solid #E2E8F0; }

/* ── Stats ── */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }

.stat-card {
  background: white;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0F172A;
  flex-shrink: 0;
}

.stat-value { font-size: 26px; font-weight: 700; color: #0F172A; line-height: 1; margin-bottom: 4px; }
.stat-label { font-size: 12.5px; color: #64748B; font-weight: 500; text-transform: uppercase; letter-spacing: 0.4px; }
.skeleton-val { display: block; width: 40px; height: 26px; background: #F1F5F9; border-radius: 6px; animation: pulse 1.5s infinite; }

@keyframes pulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.5 } }

/* ── Grid ── */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.card { background: white; border: 1px solid #E2E8F0; border-radius: 12px; overflow: hidden; }
.card-head { padding: 20px 20px 0; }
.card-title { font-size: 14px; font-weight: 700; color: #0F172A; margin: 0 0 16px; }

/* ── Actions ── */
.action-list { display: flex; flex-direction: column; }

.action-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 20px;
  text-decoration: none;
  border-top: 1px solid #F1F5F9;
  transition: background 0.15s;
}
.action-item:hover { background: #F8FAFC; }

.action-icon {
  width: 38px;
  height: 38px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.action-icon.yellow { background: #FFFBEB; color: #D97706; }
.action-icon.green  { background: #F0FDF4; color: #059669; }
.action-icon.blue   { background: #EFF6FF; color: #2563EB; }

.action-title { font-size: 14px; font-weight: 600; color: #0F172A; margin-bottom: 2px; }
.action-sub   { font-size: 12.5px; color: #94A3B8; }
.action-arrow { color: #CBD5E1; margin-left: auto; flex-shrink: 0; }

/* ── Config ── */
.config-list { display: flex; flex-direction: column; padding: 0 20px 20px; gap: 8px; }
.config-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  text-decoration: none;
  color: #374151;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.15s;
}
.config-item:hover { border-color: #2563EB; color: #2563EB; background: #EFF6FF; }
</style>
