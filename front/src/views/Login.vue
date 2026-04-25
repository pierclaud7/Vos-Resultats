<template>
  <div class="login-page">
    <div class="login-box">
      <div class="login-logo">
        <div class="logo-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
          </svg>
        </div>
        <span>Vos Résultats</span>
      </div>

      <h2 class="login-title">Connexion</h2>

      <div v-if="error" class="login-error">{{ error }}</div>

      <div class="login-field">
        <label>Email</label>
        <input v-model="email" type="email" placeholder="admin@example.com" @keyup.enter="handleLogin" autofocus />
      </div>

      <div class="login-field">
        <label>Mot de passe</label>
        <input v-model="password" type="password" placeholder="••••••••" @keyup.enter="handleLogin" />
      </div>

      <button class="login-btn" @click="handleLogin" :disabled="loading">
        <span v-if="loading" class="login-spinner"></span>
        <span v-else>Se connecter</span>
      </button>

      <router-link to="/" class="login-back">← Retour au site public</router-link>
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
  const result  = await auth.login(email.value, password.value)
  if (result.success) {
    router.push(route.query.redirect || '/admin')
  } else {
    error.value = result.error
  }
  loading.value = false
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

.login-page {
  min-height: 100vh;
  background: #0F172A;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.login-box {
  background: #1E293B;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 16px;
  padding: 40px;
  width: 100%;
  max-width: 380px;
}

.login-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 32px;
  color: white;
  font-weight: 700;
  font-size: 15px;
}

.logo-icon {
  width: 34px;
  height: 34px;
  background: #2563EB;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.login-title {
  font-size: 22px;
  font-weight: 800;
  color: white;
  margin: 0 0 28px;
  letter-spacing: -0.4px;
}

.login-error {
  background: rgba(220,38,38,0.1);
  color: #EF4444;
  border: 1px solid rgba(220,38,38,0.2);
  border-radius: 8px;
  padding: 12px 14px;
  font-size: 13.5px;
  margin-bottom: 20px;
}

.login-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 16px;
}

.login-field label {
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
}

.login-field input {
  padding: 11px 14px;
  border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  font-size: 14px;
  color: white;
  background: #0F172A;
  outline: none;
  font-family: 'DM Sans', sans-serif;
  transition: border-color 0.15s;
}
.login-field input::placeholder { color: #334155; }
.login-field input:focus {
  border-color: #2563EB;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
}

.login-btn {
  width: 100%;
  padding: 12px;
  background: #2563EB;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  transition: background 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 8px;
  margin-bottom: 20px;
}
.login-btn:hover:not(:disabled) { background: #1D4ED8; }
.login-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.login-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.2);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.login-back {
  display: block;
  text-align: center;
  color: #334155;
  font-size: 13px;
  text-decoration: none;
  transition: color 0.15s;
}
.login-back:hover { color: #64748B; }
</style>