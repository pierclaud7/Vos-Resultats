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
        <div class="footer-brand">
          <div class="footer-logo">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
              <path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
          </div>
          Vos Résultats
        </div>
        <span class="footer-copy">© {{ new Date().getFullYear() }} — Plateforme de résultats d'examens</span>
      </div>
    </footer>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

.site {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: #0F172A;
}

.header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(15, 23, 42, 0.95);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255,255,255,0.06);
}

.header-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 32px;
  height: 64px;
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
  color: white;
  text-decoration: none;
  letter-spacing: -0.3px;
  flex-shrink: 0;
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
  gap: 2px;
  flex: 1;
}

.nav-link {
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #64748B;
  text-decoration: none;
  transition: all 0.15s;
}
.nav-link:hover { color: #E2E8F0; background: rgba(255,255,255,0.05); }
.nav-link.active { color: white; background: rgba(37,99,235,0.15); }

.btn-admin {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 18px;
  background: #2563EB;
  color: white;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.15s;
  flex-shrink: 0;
}
.btn-admin:hover { background: #1D4ED8; color: white; }

.footer {
  background: #080F1E;
  padding: 28px 32px;
  margin-top: auto;
  border-top: 1px solid rgba(255,255,255,0.04);
}

.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.footer-brand {
  display: flex;
  align-items: center;
  gap: 8px;
  color: white;
  font-weight: 700;
  font-size: 14px;
}

.footer-logo {
  width: 26px;
  height: 26px;
  background: #2563EB;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.footer-copy { color: #334155; font-size: 13px; }
</style>