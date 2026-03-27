<template>
  <div class="d-flex flex-column min-vh-100">

    <!-- Navbar publique -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
      <div class="container">
        <router-link to="/" class="navbar-brand fw-bold">
          <i class="fas fa-graduation-cap me-2"></i> Vos Résultats
        </router-link>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navPublic">
          <ul class="navbar-nav mx-auto">
            <li class="nav-item">
              <router-link to="/" class="nav-link text-white" active-class="fw-bold">Accueil</router-link>
            </li>
            <li class="nav-item">
              <router-link to="/resultats" class="nav-link text-white" active-class="fw-bold">Mes Résultats</router-link>
            </li>
            <li class="nav-item">
              <router-link to="/statistiques" class="nav-link text-white" active-class="fw-bold">Statistiques</router-link>
            </li>
          </ul>

          <router-link to="/login" class="btn btn-light btn-sm fw-bold text-primary px-3">
            <i class="fas fa-sign-in-alt me-1"></i> Administration
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Contenu -->
    <main class="flex-grow-1">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="py-4 bg-white border-top text-center text-muted small">
      <div class="container">
        <p class="mb-1 fw-bold">Vos Résultats &copy; {{ new Date().getFullYear() }}</p>
        <p class="mb-0">Plateforme de consultation des résultats d'examens</p>
      </div>
    </footer>
  </div>
</template>
