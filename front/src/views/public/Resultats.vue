<template>
  <div class="resultats-page">
    <div class="page-header">
      <div class="page-header-inner">
        <h1 class="page-title">Consulter mes résultats</h1>
        <p class="page-sub">Sélectionnez votre filière puis identifiez-vous pour consulter votre résultat.</p>
      </div>
    </div>

    <div class="page-body">
      <div class="steps-container">

        <!-- Étape 1 -->
        <div class="step-card">
          <div class="step-label"><span class="step-num">1</span> Filière</div>
          <div v-if="loadingFilieres" class="loading-inline"><div class="spinner-xs"></div></div>
          <select v-else v-model="selectedFiliere" class="select-field" @change="onFiliereChange">
            <option value="">— Choisir une filière —</option>
            <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
          </select>
        </div>

        <!-- Étape 2 -->
        <div class="step-card" v-if="selectedFiliere">
          <div class="step-label"><span class="step-num">2</span> Diplôme et année</div>
          <div v-if="loadingDiplomes" class="loading-inline"><div class="spinner-xs"></div></div>
          <div v-else>
            <div v-for="d in diplomes" :key="d.id" style="margin-bottom:12px">
              <div style="font-size:12px;font-weight:600;color:#64748B;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.4px">{{ d.intitule }}</div>
              <select v-model="selectedSession" class="select-field" @change="resetResultat">
                <option value="">— Choisir une année —</option>
                <option v-for="s in d.sessions" :key="s.id" :value="s.id">{{ s.annee }} — Taux de réussite : {{ s.tauxReussite }}%</option>
              </select>
            </div>
            <p v-if="!diplomes.length" style="font-size:14px;color:#94A3B8;margin:0">Aucun résultat publié pour cette filière.</p>
          </div>
        </div>

        <!-- Étape 3 -->
        <div class="step-card" v-if="selectedSession">
          <div class="step-label"><span class="step-num">3</span> Votre identification</div>

          <div class="tabs">
            <button class="tab" :class="{ active: mode === 'numero' }" @click="mode='numero'; resetResultat()">N° étudiant</button>
            <button class="tab" :class="{ active: mode === 'email' }" @click="mode='email'; resetResultat()">Email</button>
            <button class="tab" :class="{ active: mode === 'nomprenom' }" @click="mode='nomprenom'; resetResultat()">Nom & Prénom</button>
          </div>

          <div v-if="mode === 'numero'" style="margin-top:16px">
            <label class="field-label">Numéro étudiant</label>
            <input v-model="form.numero" type="text" class="input-field" placeholder="Ex : 2025-INFO-00042" />
            <div class="field-hint">Figurant sur votre certificat d'inscription</div>
          </div>

          <div v-if="mode === 'email'" style="margin-top:16px">
            <label class="field-label">Adresse email</label>
            <input v-model="form.email" type="email" class="input-field" placeholder="jean.dupont@exemple.com" />
          </div>

          <div v-if="mode === 'nomprenom'" style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div>
              <label class="field-label">Nom</label>
              <input v-model="form.nom" type="text" class="input-field" placeholder="DUPONT" />
            </div>
            <div>
              <label class="field-label">Prénom</label>
              <input v-model="form.prenom" type="text" class="input-field" placeholder="Jean" />
            </div>
          </div>

          <button class="search-btn" @click="chercher" :disabled="searching || !canSearch">
            <span v-if="searching" class="spinner-xs white"></span>
            <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Rechercher mon résultat
          </button>
        </div>

        <!-- Résultat -->
        <transition name="fade">
          <div v-if="resultat" class="result-card">
            <div class="result-icon" :class="resultatClass(resultat.resultat)">
              <svg v-if="resultat.resultat === 'Admis'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <svg v-else-if="resultat.resultat === 'Rattrapage'" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.26"/></svg>
              <svg v-else width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <h2 class="result-name">{{ resultat.prenom }} {{ resultat.nom }}</h2>
            <div class="result-num">N° {{ resultat.numeroEtudiant }}</div>
            <div class="result-status" :class="resultatClass(resultat.resultat)">{{ resultat.resultat }}</div>
            <div class="result-info">{{ resultat.session.diplome }} — Promotion {{ resultat.session.annee }}</div>
            <div v-if="resultat.moyenne !== null" class="result-moyenne">Moyenne obtenue : <strong>{{ resultat.moyenne }}/20</strong></div>
          </div>
        </transition>

        <!-- Erreur -->
        <div v-if="erreur" class="error-msg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          {{ erreur }}
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
  try { diplomes.value = (await publicService.getDiplomesFiliere(selectedFiliere.value)).data }
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
  try { resultat.value = (await api.get(`/public/sessions/${selectedSession.value}/resultat`, { params })).data }
  catch (e) { erreur.value = e.response?.data?.error || 'Aucun résultat trouvé.' }
  finally { searching.value = false }
}

function resultatClass(r) { return { Admis: 'admis', Rattrapage: 'rattrapage', Refusé: 'refuse' }[r] || 'attente' }
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

.resultats-page { min-height: calc(100vh - 60px); background: #F8FAFC; }

.page-header { background: #0F172A; padding: 48px 24px; }
.page-header-inner { max-width: 640px; margin: 0 auto; text-align: center; }
.page-title { font-size: 30px; font-weight: 800; color: white; margin: 0 0 10px; letter-spacing: -0.5px; }
.page-sub { font-size: 15px; color: #64748B; margin: 0; line-height: 1.6; }

.page-body { max-width: 640px; margin: 0 auto; padding: 32px 24px; }

.steps-container { display: flex; flex-direction: column; gap: 16px; }

.step-card { background: white; border: 1px solid #E2E8F0; border-radius: 12px; padding: 24px; }

.step-label { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; color: #0F172A; margin-bottom: 16px; }

.step-num { width: 26px; height: 26px; background: #2563EB; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex-shrink: 0; }

.select-field, .input-field { width: 100%; padding: 10px 14px; border: 1.5px solid #E2E8F0; border-radius: 8px; font-size: 14px; color: #0F172A; outline: none; transition: border-color 0.15s; font-family: 'DM Sans', sans-serif; background: white; }
.select-field:focus, .input-field:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
.input-field::placeholder { color: #CBD5E1; }

.field-label { display: block; font-size: 12.5px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.field-hint { font-size: 12px; color: #94A3B8; margin-top: 6px; }

/* Tabs */
.tabs { display: flex; gap: 4px; background: #F1F5F9; border-radius: 8px; padding: 4px; }
.tab { flex: 1; padding: 7px; border: none; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; background: transparent; color: #64748B; transition: all 0.15s; font-family: 'DM Sans', sans-serif; }
.tab.active { background: white; color: #0F172A; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }

/* Bouton recherche */
.search-btn { width: 100%; margin-top: 20px; padding: 12px; background: #2563EB; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s; font-family: 'DM Sans', sans-serif; }
.search-btn:hover:not(:disabled) { background: #1D4ED8; }
.search-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Résultat */
.result-card { background: white; border: 1px solid #E2E8F0; border-radius: 12px; padding: 40px 24px; text-align: center; }
.result-icon { width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
.result-icon.admis    { background: #F0FDF4; color: #059669; }
.result-icon.rattrapage { background: #FFFBEB; color: #D97706; }
.result-icon.refuse   { background: #FEF2F2; color: #DC2626; }
.result-name { font-size: 20px; font-weight: 800; color: #0F172A; margin: 0 0 6px; }
.result-num { font-size: 12px; color: #94A3B8; margin-bottom: 16px; }
.result-status { font-size: 28px; font-weight: 800; margin-bottom: 12px; }
.result-status.admis     { color: #059669; }
.result-status.rattrapage { color: #D97706; }
.result-status.refuse    { color: #DC2626; }
.result-info { font-size: 14px; color: #64748B; margin-bottom: 6px; }
.result-moyenne { font-size: 14px; color: #374151; }

/* Erreur */
.error-msg { display: flex; align-items: center; gap: 10px; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 10px; padding: 14px 18px; font-size: 14px; }

/* Spinners */
.loading-inline { display: flex; justify-content: center; padding: 16px; }
.spinner-xs { width: 20px; height: 20px; border: 2px solid #E2E8F0; border-top-color: #2563EB; border-radius: 50%; animation: spin 0.7s linear infinite; }
.spinner-xs.white { border-color: rgba(255,255,255,0.3); border-top-color: white; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s, transform 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
</style>
