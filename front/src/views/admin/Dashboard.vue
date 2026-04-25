<template>
  <div class="page">
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

    <div class="grid-2">
      <div class="dash-card">
        <div class="dash-card-head">
          <h3 class="dash-card-title">Gestion des examens</h3>
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
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
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

      <div class="dash-card">
        <div class="dash-card-head">
          <h3 class="dash-card-title">Configuration</h3>
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
  { label: 'Filières',  value: 0, bg: 'rgba(37,99,235,0.12)',   svg: '<path d="M4 6h16M4 10h16M4 14h16M4 18h16"/>' },
  { label: 'Diplômes',  value: 0, bg: 'rgba(5,150,105,0.12)',   svg: '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>' },
  { label: 'Sessions',  value: 0, bg: 'rgba(217,119,6,0.12)',   svg: '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>' },
  { label: 'Étudiants', value: 0, bg: 'rgba(124,58,237,0.12)',  svg: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>' },
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

.page-sub { font-size: 13.5px; color: #475569; margin: 4px 0 0; }
.topbar-date { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #334155; background: #1E293B; padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.06); }

.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }

.stat-card {
  background: #1E293B;
  border: 1px solid rgba(255,255,255,0.06);
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
  color: white;
  flex-shrink: 0;
}

.stat-value { font-size: 26px; font-weight: 700; color: white; line-height: 1; margin-bottom: 4px; }
.stat-label { font-size: 12px; color: #475569; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
.skeleton-val { display: block; width: 40px; height: 26px; background: rgba(255,255,255,0.06); border-radius: 6px; animation: pulse 1.5s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1 } 50% { opacity: 0.4 } }

.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

.dash-card { background: #1E293B; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; overflow: hidden; }
.dash-card-head { padding: 20px 20px 0; }
.dash-card-title { font-size: 14px; font-weight: 700; color: white; margin: 0 0 16px; }

.action-list { display: flex; flex-direction: column; }
.action-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 20px;
  text-decoration: none;
  border-top: 1px solid rgba(255,255,255,0.04);
  transition: background 0.15s;
}
.action-item:hover { background: rgba(255,255,255,0.03); }

.action-icon {
  width: 38px;
  height: 38px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.action-icon.yellow { background: rgba(217,119,6,0.12); color: #F59E0B; }
.action-icon.green  { background: rgba(5,150,105,0.12); color: #10B981; }
.action-icon.blue   { background: rgba(37,99,235,0.12); color: #60A5FA; }

.action-title { font-size: 14px; font-weight: 600; color: white; margin-bottom: 2px; }
.action-sub   { font-size: 12.5px; color: #334155; }
.action-arrow { color: #1E293B; margin-left: auto; flex-shrink: 0; }
.action-item:hover .action-arrow { color: #334155; }

.config-list { display: flex; flex-direction: column; padding: 0 20px 20px; gap: 8px; }
.config-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 8px;
  text-decoration: none;
  color: #64748B;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.15s;
}
.config-item:hover { border-color: rgba(37,99,235,0.3); color: #60A5FA; background: rgba(37,99,235,0.05); }
</style>