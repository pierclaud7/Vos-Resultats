<template>
  <div class="nf-shell">
    <div class="nf-code">404</div>
    <h1 class="nf-title">Page introuvable</h1>
    <p class="nf-sub">La page que vous recherchez n'existe pas ou a été déplacée.</p>
    <div class="nf-actions">
      <router-link to="/" class="nf-btn-primary">Accueil</router-link>
      <button class="nf-btn-secondary" @click="$router.back()">Retour</button>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700;800&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

.nf-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #0F172A;
  padding: 40px 24px;
  text-align: center;
}

.nf-code {
  font-size: 96px;
  font-weight: 800;
  color: #2563EB;
  line-height: 1;
  margin-bottom: 16px;
  letter-spacing: -4px;
}

.nf-title {
  font-size: 28px;
  font-weight: 700;
  color: white;
  margin: 0 0 12px;
}

.nf-sub {
  font-size: 15px;
  color: #475569;
  margin: 0 0 32px;
  max-width: 400px;
}

.nf-actions {
  display: flex;
  gap: 12px;
}

.nf-btn-primary {
  padding: 11px 24px;
  background: #2563EB;
  color: white;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.15s;
}
.nf-btn-primary:hover { background: #1D4ED8; }

.nf-btn-secondary {
  padding: 11px 24px;
  background: rgba(255,255,255,0.04);
  color: #64748B;
  border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: all 0.15s;
}
.nf-btn-secondary:hover { background: rgba(255,255,255,0.07); color: #94A3B8; }
</style>