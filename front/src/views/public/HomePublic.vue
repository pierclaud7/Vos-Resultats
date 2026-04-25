<template>
  <div>
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
    </section>

    <section class="how-section">
      <div class="how-inner">
        <div class="how-label">Simple & rapide</div>
        <h2 class="how-title">Comment consulter vos résultats ?</h2>
        <p class="how-sub">En trois étapes simples, accédez à votre résultat d'examen.</p>
        <div class="steps">
          <div class="step" v-for="(step, i) in steps" :key="i">
            <div class="step-number">{{ i + 1 }}</div>
            <h3 class="step-title">{{ step.title }}</h3>
            <p class="step-desc">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="cta-inner">
        <h2 class="cta-title">Prêt à consulter vos résultats ?</h2>
        <p class="cta-sub">Entrez votre numéro étudiant, email ou nom et prénom pour accéder à votre résultat.</p>
        <router-link to="/resultats" class="hero-btn-primary">
          Consulter maintenant
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
const steps = [
  { title: 'Choisissez votre filière', desc: 'Sélectionnez la filière et le diplôme que vous avez préparé.' },
  { title: 'Identifiez-vous', desc: 'Entrez votre numéro étudiant, votre email ou vos nom et prénom.' },
  { title: 'Consultez votre résultat', desc: 'Votre résultat s\'affiche instantanément : Admis, Rattrapage ou Refusé.' },
]
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

/* Hero */
.hero {
  background: #0F172A;
  padding: 120px 32px 100px;
  display: flex;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

.hero::before {
  content: '';
  position: absolute;
  top: -200px;
  left: 50%;
  transform: translateX(-50%);
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, rgba(37,99,235,0.12) 0%, transparent 70%);
  pointer-events: none;
}

.hero-inner {
  max-width: 680px;
  width: 100%;
  text-align: center;
  position: relative;
}

.hero-badge {
  display: inline-flex;
  background: rgba(37,99,235,0.12);
  color: #60A5FA;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  padding: 6px 16px;
  border-radius: 20px;
  border: 1px solid rgba(96,165,250,0.2);
  margin-bottom: 28px;
}

.hero-title {
  font-size: 52px;
  font-weight: 800;
  color: white;
  line-height: 1.12;
  letter-spacing: -1.5px;
  margin: 0 0 22px;
}

.hero-sub {
  font-size: 17px;
  color: #64748B;
  line-height: 1.7;
  margin: 0 auto 40px;
  max-width: 480px;
}

.hero-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
}

.hero-btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 28px;
  background: #2563EB;
  color: white;
  border-radius: 10px;
  font-weight: 700;
  font-size: 15px;
  text-decoration: none;
  transition: background 0.15s, transform 0.15s;
}
.hero-btn-primary:hover { background: #1D4ED8; color: white; transform: translateY(-1px); }

.hero-btn-secondary {
  display: inline-flex;
  align-items: center;
  padding: 14px 28px;
  background: rgba(255,255,255,0.05);
  color: #94A3B8;
  border-radius: 10px;
  font-weight: 600;
  font-size: 15px;
  text-decoration: none;
  border: 1px solid rgba(255,255,255,0.08);
  transition: all 0.15s;
}
.hero-btn-secondary:hover { background: rgba(255,255,255,0.08); color: #E2E8F0; }

/* How */
.how-section {
  background: #080F1E;
  padding: 100px 32px;
}

.how-inner {
  max-width: 960px;
  margin: 0 auto;
  text-align: center;
}

.how-label {
  display: inline-flex;
  color: #2563EB;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 16px;
}

.how-title {
  font-size: 32px;
  font-weight: 800;
  color: white;
  margin: 0 0 14px;
  letter-spacing: -0.8px;
}

.how-sub {
  font-size: 15px;
  color: #475569;
  margin: 0 auto 56px;
  max-width: 440px;
  line-height: 1.6;
}

.steps {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  text-align: left;
}

.step {
  background: #0F172A;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 16px;
  padding: 32px;
  transition: border-color 0.2s;
}
.step:hover { border-color: rgba(37,99,235,0.3); }

.step-number {
  width: 40px;
  height: 40px;
  background: rgba(37,99,235,0.12);
  color: #3B82F6;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 17px;
  margin-bottom: 20px;
}

.step-title {
  font-size: 15px;
  font-weight: 700;
  color: white;
  margin: 0 0 10px;
}

.step-desc {
  font-size: 13.5px;
  color: #475569;
  line-height: 1.65;
  margin: 0;
}

/* CTA */
.cta-section {
  background: #0F172A;
  padding: 100px 32px;
  border-top: 1px solid rgba(255,255,255,0.04);
}

.cta-inner {
  max-width: 560px;
  margin: 0 auto;
  text-align: center;
}

.cta-title {
  font-size: 30px;
  font-weight: 800;
  color: white;
  margin: 0 0 14px;
  letter-spacing: -0.5px;
}

.cta-sub {
  font-size: 15px;
  color: #475569;
  line-height: 1.6;
  margin: 0 auto 32px;
}

@media (max-width: 768px) {
  .hero-title { font-size: 34px; }
  .steps { grid-template-columns: 1fr; }
  .how-title { font-size: 24px; }
}
</style>