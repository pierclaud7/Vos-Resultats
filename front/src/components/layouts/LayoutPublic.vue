<template>
  <div class="site">
    <header class="header">
      <div class="header-inner">
        <router-link to="/" class="logo">
          <div class="logo-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
          </div>
          Vos Résultats
        </router-link>

        <nav class="header-nav">
          <router-link to="/" class="nav-link" exact-active-class="active">Accueil</router-link>
          <router-link to="/resultats" class="nav-link" active-class="active">Mes Résultats</router-link>
          <router-link to="/statistiques" class="nav-link" active-class="active">Statistiques</router-link>
        </nav>

        <router-link to="/login" class="btn-admin">
          Administration
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </router-link>
      </div>
    </header>

    <main>
      <slot />
    </main>

    <footer class="footer">
      <div class="footer-inner">
        <span class="footer-brand">Vos Résultats</span>
        <span class="footer-copy">© {{ new Date().getFullYear() }} — Plateforme de résultats d'examens</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');

* { font-family: 'DM Sans', sans-serif; }

.site { display: flex; flex-direction: column; min-height: 100vh; background: #FAFAFA; }

/* ── Header ── */
.header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255,255,255,0.9);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid #E2E8F0;
}

.header-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
  height: 60px;
  display: flex;
  align-items: center;
  gap: 40px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: 15px;
  color: #0F172A;
  text-decoration: none;
  letter-spacing: -0.3px;
}

.logo-icon {
  width: 32px;
  height: 32px;
  background: #2563EB;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.header-nav {
  display: flex;
  align-items: center;
  gap: 4px;
  flex: 1;
}

.nav-link {
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #64748B;
  text-decoration: none;
  transition: all 0.15s;
}
.nav-link:hover { background: #F1F5F9; color: #0F172A; }
.nav-link.active { color: #2563EB; background: #EFF6FF; }

.btn-admin {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #0F172A;
  color: white;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.15s;
  margin-left: auto;
}
.btn-admin:hover { background: #1E293B; color: white; }

/* ── Footer ── */
.footer {
  background: #0F172A;
  padding: 24px;
  margin-top: auto;
}

.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-brand { color: white; font-weight: 700; font-size: 14px; }
.footer-copy { color: #64748B; font-size: 13px; }
</style>
