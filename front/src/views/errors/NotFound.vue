<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="text-center px-4">
      <div class="display-1 fw-bold text-primary mb-2">404</div>
      <h2 class="fw-bold mb-3">Page introuvable</h2>
      <p class="text-muted mb-4">
        La page que vous recherchez n'existe pas ou a été déplacée.
      </p>
      <div class="d-flex gap-3 justify-content-center">
        <router-link to="/" class="btn btn-primary px-4">
          <i class="fas fa-home me-2"></i> Accueil
        </router-link>
        <button class="btn btn-outline-secondary px-4" @click="$router.back()">
          <i class="fas fa-arrow-left me-2"></i> Retour
        </button>
      </div>
    </div>
  </div>
</template>
