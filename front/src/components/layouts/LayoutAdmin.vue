<template>
  <div class="d-flex min-vh-100">

    <!-- Sidebar -->
    <nav class="bg-dark text-white d-flex flex-column p-0" style="width:240px;min-width:240px;">
      <div class="p-3 border-bottom border-secondary">
        <h5 class="mb-0 fw-bold">
          <i class="fas fa-graduation-cap text-warning me-2"></i>Administration
        </h5>
        <small class="text-muted">{{ auth.user?.email }}</small>
      </div>

      <ul class="nav flex-column p-2 flex-grow-1">
        <li class="nav-item mb-1">
          <router-link to="/admin" class="nav-link text-white rounded" active-class="bg-primary" exact>
            <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
          </router-link>
        </li>

        <li class="nav-item mt-3 mb-1 px-2">
          <small class="text-muted text-uppercase fw-bold" style="font-size:.7rem">Structure</small>
        </li>
        <li class="nav-item mb-1">
          <router-link to="/admin/filieres" class="nav-link text-white rounded" active-class="bg-primary">
            <i class="fas fa-layer-group me-2"></i> Filières
          </router-link>
        </li>
        <li class="nav-item mb-1">
          <router-link to="/admin/diplomes" class="nav-link text-white rounded" active-class="bg-primary">
            <i class="fas fa-graduation-cap me-2"></i> Diplômes
          </router-link>
        </li>

        <li class="nav-item mt-3 mb-1 px-2">
          <small class="text-muted text-uppercase fw-bold" style="font-size:.7rem">Examens</small>
        </li>
        <li class="nav-item mb-1">
          <router-link to="/admin/sessions" class="nav-link text-white rounded" active-class="bg-primary">
            <i class="fas fa-calendar-alt me-2"></i> Sessions
          </router-link>
        </li>
        <li class="nav-item mb-1">
          <router-link to="/admin/etudiants" class="nav-link text-white rounded" active-class="bg-primary">
            <i class="fas fa-users me-2"></i> Étudiants
          </router-link>
        </li>
        <li class="nav-item mb-1">
          <router-link to="/admin/import-csv" class="nav-link text-white rounded" active-class="bg-primary">
            <i class="fas fa-file-csv me-2"></i> Import CSV
          </router-link>
        </li>
      </ul>

      <div class="p-3 border-top border-secondary">
        <router-link to="/" class="btn btn-outline-light btn-sm w-100 mb-2">
          <i class="fas fa-globe me-1"></i> Voir le site public
        </router-link>
        <button @click="handleLogout" class="btn btn-danger btn-sm w-100">
          <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
        </button>
      </div>
    </nav>

    <!-- Contenu principal -->
    <main class="flex-grow-1 bg-light overflow-auto">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth.js'
import { useRouter }    from 'vue-router'

const auth   = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
