<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="col-md-4 col-lg-3">

      <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">
          <i class="fas fa-graduation-cap me-2"></i>Vos Résultats
        </h2>
        <p class="text-muted">Accès administration</p>
      </div>

      <div class="card border-0 shadow-lg rounded-4 p-4">
        <h1 class="h5 fw-bold text-center mb-4">Connexion</h1>

        <div v-if="error" class="alert alert-danger py-2 small text-center">
          <i class="fas fa-exclamation-circle me-1"></i> {{ error }}
        </div>

        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label class="form-label fw-semibold small">Adresse email</label>
            <input
              v-model="email"
              type="email"
              class="form-control"
              placeholder="admin@example.com"
              required
              autofocus
            />
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold small">Mot de passe</label>
            <input
              v-model="password"
              type="password"
              class="form-control"
              placeholder="••••••••"
              required
            />
          </div>

          <button
            type="submit"
            class="btn btn-primary w-100 fw-bold py-2"
            :disabled="loading"
          >
            <span v-if="loading">
              <span class="spinner-border spinner-border-sm me-2"></span> Connexion...
            </span>
            <span v-else>
              <i class="fas fa-sign-in-alt me-2"></i> Se connecter
            </span>
          </button>
        </form>
      </div>

      <div class="text-center mt-3">
        <router-link to="/" class="text-muted small text-decoration-none">
          <i class="fas fa-arrow-left me-1"></i> Retour au site public
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const auth     = useAuthStore()
const router   = useRouter()
const route    = useRoute()

const email    = ref('')
const password = ref('')
const error    = ref('')
const loading  = ref(false)

async function handleLogin() {
  error.value   = ''
  loading.value = true

  const result = await auth.login(email.value, password.value)

  if (result.success) {
    const redirect = route.query.redirect || '/admin'
    router.push(redirect)
  } else {
    error.value = result.error
  }

  loading.value = false
}
</script>
