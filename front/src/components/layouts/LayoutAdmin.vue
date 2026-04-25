<template>
  <div class="app-shell" :class="{ collapsed: !sidebarOpen }">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <div class="brand-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
          </svg>
        </div>
        <span class="brand-name" v-if="sidebarOpen">Vos Résultats</span>
      </div>

      <div class="sidebar-section-label" v-if="sidebarOpen">Tableau de bord</div>
      <nav class="sidebar-nav">
        <router-link to="/admin" class="nav-item" exact-active-class="active" :title="!sidebarOpen ? 'Tableau de bord' : ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          <span v-if="sidebarOpen">Tableau de bord</span>
        </router-link>
      </nav>

      <div class="sidebar-section-label" v-if="sidebarOpen">Structure</div>
      <nav class="sidebar-nav">
        <router-link to="/admin/filieres" class="nav-item" active-class="active" :title="!sidebarOpen ? 'Filières' : ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
          <span v-if="sidebarOpen">Filières</span>
        </router-link>
        <router-link to="/admin/diplomes" class="nav-item" active-class="active" :title="!sidebarOpen ? 'Diplômes' : ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          <span v-if="sidebarOpen">Diplômes</span>
        </router-link>
      </nav>

      <div class="sidebar-section-label" v-if="sidebarOpen">Examens</div>
      <nav class="sidebar-nav">
        <router-link to="/admin/sessions" class="nav-item" active-class="active" :title="!sidebarOpen ? 'Sessions' : ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <span v-if="sidebarOpen">Sessions</span>
        </router-link>
        <router-link to="/admin/etudiants" class="nav-item" active-class="active" :title="!sidebarOpen ? 'Étudiants' : ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          <span v-if="sidebarOpen">Étudiants</span>
        </router-link>
        <router-link to="/admin/import-csv" class="nav-item" active-class="active" :title="!sidebarOpen ? 'Import CSV' : ''">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <span v-if="sidebarOpen">Import CSV</span>
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <router-link to="/" class="footer-btn secondary" :title="!sidebarOpen ? 'Site public' : ''">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          <span v-if="sidebarOpen">Site public</span>
        </router-link>
        <button @click="handleLogout" class="footer-btn danger" :title="!sidebarOpen ? 'Déconnexion' : ''">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          <span v-if="sidebarOpen">Déconnexion</span>
        </button>
        <div class="user-info" v-if="sidebarOpen">
          <div class="user-avatar">{{ userInitial }}</div>
          <span class="user-email">{{ auth.user?.email }}</span>
        </div>
        <div class="user-avatar solo" v-else>{{ userInitial }}</div>
      </div>
    </aside>

    <!-- Toggle -->
    <button class="toggle-btn" @click="sidebarOpen = !sidebarOpen">
      <svg v-if="sidebarOpen" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
    </button>

    <!-- Contenu -->
    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth.js'
import { useRouter } from 'vue-router'

const auth        = useAuthStore()
const router      = useRouter()
const sidebarOpen = ref(true)
const userInitial = computed(() => auth.user?.email?.[0]?.toUpperCase() || 'A')

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');
* { font-family: 'DM Sans', sans-serif; }

.app-shell {
  display: flex;
  min-height: 100vh;
  background: #0F172A;
}

.sidebar {
  width: 240px;
  min-width: 240px;
  background: #080F1E;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  overflow-y: auto;
  overflow-x: hidden;
  z-index: 100;
  transition: width 0.25s ease, min-width 0.25s ease;
  border-right: 1px solid rgba(255,255,255,0.04);
}

.app-shell.collapsed .sidebar {
  width: 60px;
  min-width: 60px;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 12px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.04);
  overflow: hidden;
  white-space: nowrap;
}

.brand-icon {
  width: 34px;
  height: 34px;
  background: #2563EB;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.brand-name { color: white; font-weight: 700; font-size: 15px; }

.sidebar-section-label {
  color: #1E293B;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 20px 16px 6px;
  white-space: nowrap;
  overflow: hidden;
  color: #334155;
}

.sidebar-nav { padding: 0 8px; }

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 9px 10px;
  border-radius: 8px;
  color: #475569;
  text-decoration: none;
  font-size: 13.5px;
  font-weight: 500;
  transition: all 0.15s;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
}

.nav-item:hover { background: rgba(255,255,255,0.04); color: #94A3B8; }
.nav-item.active { background: rgba(37,99,235,0.15); color: #60A5FA; }

.sidebar-footer {
  margin-top: auto;
  padding: 16px 8px;
  border-top: 1px solid rgba(255,255,255,0.04);
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.footer-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  text-decoration: none;
  transition: all 0.15s;
  width: 100%;
  white-space: nowrap;
  overflow: hidden;
  font-family: 'DM Sans', sans-serif;
}

.footer-btn.secondary { background: rgba(255,255,255,0.04); color: #475569; }
.footer-btn.secondary:hover { background: rgba(255,255,255,0.07); color: #94A3B8; }
.footer-btn.danger { background: rgba(239,68,68,0.08); color: #64748B; }
.footer-btn.danger:hover { background: rgba(239,68,68,0.15); color: #EF4444; }

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 10px;
}

.user-avatar {
  width: 28px;
  height: 28px;
  background: #1E3A8A;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #60A5FA;
  font-size: 12px;
  font-weight: 700;
  flex-shrink: 0;
}

.user-avatar.solo { margin: 4px auto 0; }

.user-email {
  color: #334155;
  font-size: 12px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.toggle-btn {
  position: fixed;
  top: 20px;
  left: 228px;
  width: 24px;
  height: 24px;
  background: #1E293B;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475569;
  z-index: 200;
  transition: left 0.25s ease;
}

.toggle-btn:hover { background: #334155; color: white; }

.app-shell.collapsed .toggle-btn { left: 48px; }

.main-content {
  flex: 1;
  margin-left: 240px;
  min-height: 100vh;
  background: #0F172A;
  overflow-x: hidden;
  transition: margin-left 0.25s ease;
}

.app-shell.collapsed .main-content { margin-left: 60px; }
</style>