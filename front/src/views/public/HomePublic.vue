<template>
  <div>
    <section class="bg-primary text-white py-5">
      <div class="container py-4 text-center">
        <h1 class="display-5 fw-bold mb-3"><i class="fas fa-graduation-cap me-3"></i>Vos Résultats</h1>
        <p class="lead mb-4 opacity-75">Plateforme officielle de consultation des résultats d'examens.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
          <router-link to="/resultats" class="btn btn-light btn-lg fw-bold px-4 text-primary">
            <i class="fas fa-search me-2"></i> Consulter mes résultats
          </router-link>
          <router-link to="/statistiques" class="btn btn-outline-light btn-lg px-4">
            <i class="fas fa-chart-bar me-2"></i> Statistiques
          </router-link>
        </div>
      </div>
    </section>
    <section class="py-5 bg-light">
      <div class="container">
        <h2 class="text-center fw-bold mb-5">Comment ça fonctionne ?</h2>
        <div class="row g-4">
          <div class="col-md-4" v-for="step in steps" :key="step.icon">
            <div class="card border-0 shadow-sm h-100 text-center p-4">
              <div class="mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width:64px;height:64px">
                  <i :class="`fas fa-${step.icon} fa-lg`"></i>
                </span>
              </div>
              <h5 class="fw-bold">{{ step.title }}</h5>
              <p class="text-muted small mb-0">{{ step.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>
<script setup>
const steps = [
  { icon: 'layer-group', title: 'Choisissez votre filière',  desc: 'Sélectionnez la filière et le diplôme préparé.' },
  { icon: 'envelope',    title: 'Entrez votre email',        desc: 'Saisissez votre email pour retrouver votre résultat.' },
  { icon: 'award',       title: 'Consultez vos résultats',   desc: 'Résultat affiché : Admis, Rattrapage ou Refusé.' },
]
</script>
