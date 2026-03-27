<template>
  <div class="container py-5">
    <h1 class="fw-bold mb-2 text-center">
      <i class="fas fa-search me-2 text-primary"></i>Consulter mes résultats
    </h1>
    <p class="text-center text-muted mb-4">Sélectionnez votre filière, puis identifiez-vous.</p>

    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-6">

        <!-- Filière -->
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-3"><span class="badge bg-primary rounded-circle me-2">1</span>Filière</h6>
            <div v-if="loadingFilieres" class="text-center"><span class="spinner-border spinner-border-sm text-primary"></span></div>
            <select v-else v-model="selectedFiliere" class="form-select" @change="onFiliereChange">
              <option value="">-- Choisir une filière --</option>
              <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
            </select>
          </div>
        </div>

        <!-- Diplôme + Année -->
        <div v-if="selectedFiliere" class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-3"><span class="badge bg-primary rounded-circle me-2">2</span>Diplôme et année</h6>
            <div v-if="loadingDiplomes" class="text-center"><span class="spinner-border spinner-border-sm text-primary"></span></div>
            <div v-else>
              <div v-for="d in diplomes" :key="d.id" class="mb-3">
                <label class="form-label fw-semibold small text-muted">{{ d.intitule }}</label>
                <select v-model="selectedSession" class="form-select" @change="resetResultat">
                  <option value="">-- Choisir une année --</option>
                  <option v-for="s in d.sessions" :key="s.id" :value="s.id">{{ s.annee }} (taux : {{ s.tauxReussite }}%)</option>
                </select>
              </div>
              <p v-if="!diplomes.length" class="text-muted small mb-0">Aucun résultat publié pour cette filière.</p>
            </div>
          </div>
        </div>

        <!-- Identification -->
        <div v-if="selectedSession" class="card border-0 shadow-sm mb-3">
          <div class="card-body">
            <h6 class="fw-bold mb-3"><span class="badge bg-primary rounded-circle me-2">3</span>Votre identification</h6>
            <ul class="nav nav-tabs mb-3">
              <li class="nav-item">
                <button class="nav-link" :class="{ active: mode === 'numero' }" @click="mode='numero'; resetResultat()">N° étudiant</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" :class="{ active: mode === 'email' }" @click="mode='email'; resetResultat()">Email</button>
              </li>
              <li class="nav-item">
                <button class="nav-link" :class="{ active: mode === 'nomprenom' }" @click="mode='nomprenom'; resetResultat()">Nom & Prénom</button>
              </li>
            </ul>
            <div v-if="mode === 'numero'">
              <label class="form-label small fw-semibold">Numéro étudiant</label>
              <input v-model="form.numero" type="text" class="form-control" placeholder="Ex: 2025-INFO-00042" />
              <div class="form-text">Figurant sur votre certificat d'inscription.</div>
            </div>
            <div v-if="mode === 'email'">
              <label class="form-label small fw-semibold">Adresse email</label>
              <input v-model="form.email" type="email" class="form-control" placeholder="jean.dupont@exemple.com" />
            </div>
            <div v-if="mode === 'nomprenom'" class="row g-2">
              <div class="col-6">
                <label class="form-label small fw-semibold">Nom</label>
                <input v-model="form.nom" type="text" class="form-control" placeholder="DUPONT" />
              </div>
              <div class="col-6">
                <label class="form-label small fw-semibold">Prénom</label>
                <input v-model="form.prenom" type="text" class="form-control" placeholder="Jean" />
              </div>
            </div>
            <button class="btn btn-primary w-100 mt-3" @click="chercher" :disabled="searching || !canSearch">
              <span v-if="searching" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="fas fa-search me-2"></i> Rechercher
            </button>
          </div>
        </div>

        <!-- Résultat -->
        <div v-if="resultat" class="card border-0 shadow-sm text-center p-4">
          <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white mx-auto mb-3"
            :class="bgR(resultat.resultat)" style="width:80px;height:80px;font-size:2rem">
            <i :class="iconR(resultat.resultat)"></i>
          </span>
          <h4 class="fw-bold">{{ resultat.prenom }} {{ resultat.nom }}</h4>
          <p class="text-muted small mb-1">N° {{ resultat.numeroEtudiant }}</p>
          <h2 class="fw-bold my-2" :class="textR(resultat.resultat)">{{ resultat.resultat }}</h2>
          <p class="text-muted small mb-0">{{ resultat.session.diplome }} — Promotion {{ resultat.session.annee }}</p>
          <p v-if="resultat.moyenne !== null" class="text-muted small">Moyenne : <strong>{{ resultat.moyenne }}/20</strong></p>
        </div>

        <div v-if="erreur" class="alert alert-danger mt-3">
          <i class="fas fa-exclamation-circle me-2"></i>{{ erreur }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { publicService } from '@/services/index.js'
import api from '@/services/api.js'

const filieres = ref([]); const diplomes = ref([])
const selectedFiliere = ref(''); const selectedSession = ref('')
const mode = ref('numero')
const form = ref({ numero: '', email: '', nom: '', prenom: '' })
const resultat = ref(null); const erreur = ref('')
const loadingFilieres = ref(true); const loadingDiplomes = ref(false); const searching = ref(false)

publicService.getFilieres().then(r => { filieres.value = r.data; loadingFilieres.value = false })

async function onFiliereChange() {
  selectedSession.value = ''; diplomes.value = []; resetResultat()
  if (!selectedFiliere.value) return
  loadingDiplomes.value = true
  try { const r = await publicService.getDiplomesFiliere(selectedFiliere.value); diplomes.value = r.data }
  finally { loadingDiplomes.value = false }
}

function resetResultat() { resultat.value = null; erreur.value = '' }

const canSearch = computed(() => {
  if (mode.value === 'numero')    return form.value.numero.trim().length > 0
  if (mode.value === 'email')     return form.value.email.trim().length > 0
  if (mode.value === 'nomprenom') return form.value.nom.trim().length > 0 && form.value.prenom.trim().length > 0
  return false
})

async function chercher() {
  erreur.value = ''; resultat.value = null; searching.value = true
  const params = {}
  if (mode.value === 'numero')    params.numeroEtudiant = form.value.numero
  if (mode.value === 'email')     params.email          = form.value.email
  if (mode.value === 'nomprenom') { params.nom = form.value.nom; params.prenom = form.value.prenom }
  try {
    const r = await api.get(`/public/sessions/${selectedSession.value}/resultat`, { params })
    resultat.value = r.data
  } catch (err) { erreur.value = err.response?.data?.error || 'Aucun résultat trouvé.' }
  finally { searching.value = false }
}

const bgR   = r => ({ Admis: 'bg-success', Rattrapage: 'bg-warning', Refusé: 'bg-danger' }[r] || 'bg-secondary')
const textR = r => ({ Admis: 'text-success', Rattrapage: 'text-warning', Refusé: 'text-danger' }[r] || '')
const iconR = r => ({ Admis: 'fas fa-check', Rattrapage: 'fas fa-redo', Refusé: 'fas fa-times' }[r] || 'fas fa-question')
</script>
