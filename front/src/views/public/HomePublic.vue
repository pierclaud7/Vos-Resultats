<template>
  <div>
    <!-- Hero -->
    <section class="hero">
      <div class="hero-inner">
        <div class="hero-badge">Plateforme officielle</div>
        <h1 class="hero-title">Consultez vos résultats<br>d'examens</h1>
        <p class="hero-sub">Accédez à vos résultats en quelques secondes. Recherche par numéro étudiant, email ou nom.</p>
        <div class="hero-actions">
          <router-link to="/resultats" class="hero-btn-primary">
            Consulter mes résultats
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </router-link>
          <router-link to="/statistiques" class="hero-btn-secondary">Voir les statistiques</router-link>
        </div>
      </div>
      <div class="hero-visual">
        <div class="result-card">
          <div class="result-card-header">
            <div class="result-avatar">JD</div>
            <div>
              <div class="result-name">DUPONT Jean</div>
              <div class="result-num">2025-INFO-00042</div>
            </div>
          </div>
          <div class="result-divider"></div>
          <div class="result-status admis">✓ ADMIS</div>
          <div class="result-detail">BTS SIO — Promotion 2025</div>
          <div class="result-score">Moyenne : 14.5 / 20</div>
        </div>
      </div>
    </section>

    <!-- Comment ça marche -->
    <section class="how-section">
      <div class="how-inner">
        <h2 class="how-title">Comment consulter vos résultats ?</h2>
        <div class="steps">
          <div class="step" v-for="(step, i) in steps" :key="i">
            <div class="step-number">{{ i + 1 }}</div>
            <div class="step-content">
              <h3 class="step-title">{{ step.title }}</h3>
              <p class="step-desc">{{ step.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
const steps = [
  { title: 'Choisissez votre filière', desc: 'Sélectionnez la filière et le diplôme que vous avez préparé.' },
  { title: 'Identifiez-vous', desc: 'Entrez votre numéro étudiant, votre email ou vos nom et prénom.' },
  { title: 'Consultez votre résultat', desc: 'Votre résultat s\'affiche : Admis, Rattrapage ou Refusé.' },
]
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

/* Hero */
.hero { background: #0F172A; padding: 80px 24px; display: flex; justify-content: center; }
.hero-inner { max-width: 1100px; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }

.hero-badge { display: inline-flex; background: rgba(37,99,235,0.15); color: #60A5FA; font-size: 12px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(96,165,250,0.2); margin-bottom: 20px; }

.hero-title { font-size: 42px; font-weight: 800; color: white; line-height: 1.15; letter-spacing: -1px; margin: 0 0 16px; }

.hero-sub { font-size: 16px; color: #94A3B8; line-height: 1.7; margin: 0 0 32px; }

.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }

.hero-btn-primary { display: flex; align-items: center; gap: 8px; padding: 13px 24px; background: #2563EB; color: white; border-radius: 10px; font-weight: 700; font-size: 14px; text-decoration: none; transition: background 0.15s; }
.hero-btn-primary:hover { background: #1D4ED8; color: white; }

.hero-btn-secondary { display: flex; align-items: center; padding: 13px 24px; background: rgba(255,255,255,0.07); color: #E2E8F0; border-radius: 10px; font-weight: 600; font-size: 14px; text-decoration: none; border: 1px solid rgba(255,255,255,0.1); transition: background 0.15s; }
.hero-btn-secondary:hover { background: rgba(255,255,255,0.12); color: white; }

/* Result card */
.hero-visual { display: flex; justify-content: center; }

.result-card { background: white; border-radius: 16px; padding: 28px; width: 280px; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
.result-card-header { display: flex; align-items: center; gap: 14px; margin-bottom: 20px; }
.result-avatar { width: 44px; height: 44px; background: #2563EB; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 14px; flex-shrink: 0; }
.result-name { font-weight: 700; color: #0F172A; font-size: 14px; }
.result-num { font-size: 11px; color: #94A3B8; margin-top: 2px; }
.result-divider { height: 1px; background: #F1F5F9; margin-bottom: 20px; }
.result-status { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
.result-status.admis { color: #059669; }
.result-detail { font-size: 13px; color: #64748B; margin-bottom: 6px; }
.result-score { font-size: 13px; font-weight: 600; color: #374151; }

/* How */
.how-section { padding: 80px 24px; background: #F8FAFC; }
.how-inner { max-width: 900px; margin: 0 auto; }
.how-title { font-size: 28px; font-weight: 800; color: #0F172A; text-align: center; margin: 0 0 48px; letter-spacing: -0.5px; }

.steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

.step { background: white; border: 1px solid #E2E8F0; border-radius: 14px; padding: 28px; }
.step-number { width: 36px; height: 36px; background: #EFF6FF; color: #2563EB; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; margin-bottom: 16px; }
.step-title { font-size: 15px; font-weight: 700; color: #0F172A; margin: 0 0 8px; }
.step-desc { font-size: 13.5px; color: #64748B; line-height: 1.6; margin: 0; }

@media (max-width: 768px) {
  .hero-inner { grid-template-columns: 1fr; }
  .hero-visual { display: none; }
  .steps { grid-template-columns: 1fr; }
}
</style>
