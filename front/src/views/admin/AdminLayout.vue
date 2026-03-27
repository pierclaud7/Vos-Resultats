<template>
  <div class="d-flex flex-column min-vh-100">
    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary shadow-sm sticky-top">
      <div class="container-fluid">
        <RouterLink class="navbar-brand fw-bold" to="/admin">
          <i class="fas fa-graduation-cap me-2"></i>Vos Résultats
        </RouterLink>

        <div class="d-flex align-items-center gap-3">
          <RouterLink to="/" class="btn btn-outline-light btn-sm">
            <i class="fas fa-eye me-1"></i> Site public
          </RouterLink>

          <div class="dropdown">
            <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle me-1"></i> {{ auth.user?.email }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item text-danger" @click="handleLogout">
                  <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <div class="d-flex flex-grow-1">
      <!-- SIDEBAR -->
      <aside class="bg-white border-end shadow-sm" style="width: 240px; min-height: calc(100vh - 56px)">
        <nav class="p-3">
          <p class="text-uppercase text-muted small fw-bold px-2 mb-2">Navigation</p>
          <ul class="nav flex-column gap-1">
            <li class="nav-item">
              <RouterLink to="/admin" class="nav-link rounded" exact-active-class="bg-primary text-white">
                <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
              </RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink to="/admin/filieres" class="nav-link rounded" active-class="bg-primary text-white">
                <i class="fas fa-layer-group me-2"></i> Filières
              </RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink to="/admin/diplomes" class="nav-link rounded" active-class="bg-primary text-white">
                <i class="fas fa-graduation-cap me-2"></i> Diplômes
              </RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink to="/admin/sessions" class="nav-link rounded" active-class="bg-primary text-white">
                <i class="fas fa-calendar-alt me-2"></i> Sessions
              </RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink to="/admin/etudiants" class="nav-link rounded" active-class="bg-primary text-white">
                <i class="fas fa-users me-2"></i> Étudiants
              </RouterLink>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- CONTENU -->
      <main class="flex-grow-1 p-4 bg-light">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const auth   = useAuthStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.nav-link {
  color: #495057;
  transition: all 0.15s;
}
.nav-link:hover {
  background-color: #e9ecef;
}
</style>
