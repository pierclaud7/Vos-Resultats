<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="col-md-4 col-lg-3">
      <div class="text-center mb-4">
        <h2 class="fw-bold text-primary">
          <i class="fas fa-graduation-cap me-2"></i>Vos Résultats
        </h2>
        <p class="text-muted">Administration</p>
      </div>

      <div class="card border-0 shadow-sm rounded-4 p-4">
        <h5 class="fw-bold mb-4 text-center">Connexion</h5>

        <div v-if="error" class="alert alert-danger small py-2">
          <i class="fas fa-exclamation-circle me-1"></i> {{ error }}
        </div>

        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label class="form-label fw-semibold small">Email</label>
            <input
              v-model="form.email"
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
              v-model="form.password"
              type="password"
              class="form-control"
              placeholder="••••••••"
              required
            />
          </div>

          <button
            type="submit"
            class="btn btn-primary w-100 fw-bold py-2"
            :disabled="auth.loading"
          >
            <span v-if="auth.loading">
              <span class="spinner-border spinner-border-sm me-1"></span> Connexion...
            </span>
            <span v-else>
              <i class="fas fa-sign-in-alt me-1"></i> Se connecter
            </span>
          </button>
        </form>
      </div>

      <div class="text-center mt-3">
        <RouterLink to="/" class="text-muted small text-decoration-none">
          <i class="fas fa-arrow-left me-1"></i> Retour au site
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

const auth   = useAuthStore()
const router = useRouter()
const route  = useRoute()

const form  = ref({ email: '', password: '' })
const error = ref(null)

async function handleLogin() {
  error.value = null
  const result = await auth.login(form.value.email, form.value.password)

  if (result.success) {
    const redirect = route.query.redirect || '/admin'
    router.push(redirect)
  } else {
    error.value = result.error
  }
}
</script>
