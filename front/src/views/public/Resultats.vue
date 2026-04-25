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

        <div class="step-card">
          <div class="step-label"><span class="step-num">1</span> Filière</div>
          <div v-if="loadingFilieres" class="loading-inline"><div class="spinner-xs"></div></div>
          <select v-else v-model="selectedFiliere" class="select-field" @change="onFiliereChange">
            <option value="">— Choisir une filière —</option>
            <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
          </select>
        </div>

        <div class="step-card" v-if="selectedFiliere">
          <div class="step-label"><span class="step-num">2</span> Diplôme et année</div>
          <div v-if="loadingDiplomes" class="loading-inline"><div class="spinner-xs"></div></div>
          <div v-else>
            <div v-for="d in diplomes" :key="d.id" style="margin-bottom:12px">
              <div class="diplome-label">{{ d.intitule }}</div>
              <select v-model="selectedSession" class="select-field" @change="resetResultat">
                <option value="">— Choisir une année —</option>
                <option v-for="s in d.sessions" :key="s.id" :value="s.id">{{ s.annee }}</option>
              </select>
            </div>
            <p v-if="!diplomes.length" class="empty-txt">Aucun résultat publié pour cette filière.</p>
          </div>
        </div>

        <div class="step-card" v-if="selectedSession">
          <div class="step-label"><span class="step-num">3</span> Votre identification</div>

          <div class="tabs">
            <button class="tab" :class="{ active: mode === 'numero' }" @click="mode='numero'; resetResultat()">N° étudiant</button>
            <button class="tab" :class="{ active: mode === 'email' }" @click="mode='email'; resetResultat()">Email</button>
            <button class="tab" :class="{ active: mode === 'nomprenom' }" @click="mode='nomprenom'; resetResultat()">Nom & Prénom</button>
          </div>

          <div v-if="mode === 'numero'" style="margin-top:16px">
            <label class="field-label">Numéro étudiant</label>
            <input v-model="form.numero" type="text" class="input-field" placeholder="Ex : 2025-ETU-00001" />
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

const filieres        = ref([])
const diplomes        = ref([])
const selectedFiliere = ref('')
const selectedSession = ref('')
const mode            = ref('numero')
const form            = ref({ numero: '', email: '', nom: '', prenom: '' })
const resultat        = ref(null)
const erreur          = ref('')
const loadingFilieres = ref(true)
const loadingDiplomes = ref(false)
const searching       = ref(false)

publicService.getFilieres().then(r => {
  filieres.value = r.data
  loadingFilieres.value = false
})

async function onFiliereChange() {
  selectedSession.value = ''
  diplomes.value = []
  resetResultat()
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

function resultatClass(r) {
  return { Admis: 'admis', Rattrapage: 'rattrapage', Refusé: 'refuse' }[r] || 'attente'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap');
* { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }

.resultats-page { min-height: calc(100vh - 64px); background: #0F172A; }

.page-header { background: #080F1E; padding: 56px 32px; border-bottom: 1px solid rgba(255,255,255,0.04); }
.page-header-inner { max-width: 640px; margin: 0 auto; text-align: center; }
.page-title { font-size: 32px; font-weight: 800; color: white; margin: 0 0 12px; letter-spacing: -0.8px; }
.page-sub { font-size: 15px; color: #475569; margin: 0; line-height: 1.6; }

.page-body { max-width: 600px; margin: 0 auto; padding: 40px 24px; }
.steps-container { display: flex; flex-direction: column; gap: 16px; }

.step-card {
  background: #1E293B;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 24px;
}

.step-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 700;
  color: white;
  margin-bottom: 16px;
}

.step-num {
  width: 26px;
  height: 26px;
  background: #2563EB;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 800;
  flex-shrink: 0;
}

.diplome-label {
  font-size: 11px;
  font-weight: 700;
  color: #475569;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin-bottom: 6px;
}

.select-field, .input-field {
  width: 100%;
  padding: 11px 14px;
  border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  font-size: 14px;
  color: white;
  outline: none;
  transition: border-color 0.15s;
  font-family: 'DM Sans', sans-serif;
  background: #0F172A;
}
.select-field:focus, .input-field:focus {
  border-color: #2563EB;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
}
.input-field::placeholder { color: #334155; }
.select-field option { background: #1E293B; color: white; }

.field-label { display: block; font-size: 12.5px; font-weight: 600; color: #64748B; margin-bottom: 6px; }
.field-hint  { font-size: 12px; color: #334155; margin-top: 6px; }
.empty-txt   { font-size: 14px; color: #334155; margin: 0; }

.tabs {
  display: flex;
  gap: 4px;
  background: #0F172A;
  border-radius: 8px;
  padding: 4px;
}
.tab {
  flex: 1;
  padding: 8px;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  background: transparent;
  color: #475569;
  transition: all 0.15s;
  font-family: 'DM Sans', sans-serif;
}
.tab.active { background: #1E293B; color: white; box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
.tab:hover:not(.active) { color: #94A3B8; }

.search-btn {
  width: 100%;
  margin-top: 20px;
  padding: 13px;
  background: #2563EB;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.15s;
  font-family: 'DM Sans', sans-serif;
}
.search-btn:hover:not(:disabled) { background: #1D4ED8; }
.search-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.result-card {
  background: #1E293B;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 44px 24px;
  text-align: center;
}
.result-icon {
  width: 76px;
  height: 76px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 22px;
}
.result-icon.admis      { background: rgba(5,150,105,0.15); color: #10B981; }
.result-icon.rattrapage { background: rgba(217,119,6,0.15);  color: #F59E0B; }
.result-icon.refuse     { background: rgba(220,38,38,0.15);  color: #EF4444; }
.result-icon.attente    { background: rgba(100,116,139,0.15); color: #64748B; }
.result-name   { font-size: 22px; font-weight: 800; color: white; margin: 0 0 6px; }
.result-num    { font-size: 12px; color: #334155; margin-bottom: 18px; }
.result-status { font-size: 30px; font-weight: 800; margin-bottom: 14px; }
.result-status.admis     { color: #10B981; }
.result-status.rattrapage { color: #F59E0B; }
.result-status.refuse    { color: #EF4444; }
.result-status.attente   { color: #64748B; }
.result-info   { font-size: 14px; color: #475569; margin-bottom: 8px; }
.result-moyenne { font-size: 14px; color: #64748B; }
.result-moyenne strong { color: #94A3B8; }

.error-msg {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(220,38,38,0.1);
  color: #EF4444;
  border: 1px solid rgba(220,38,38,0.2);
  border-radius: 10px;
  padding: 14px 18px;
  font-size: 14px;
}

.loading-inline { display: flex; justify-content: center; padding: 16px; }
.spinner-xs {
  width: 20px; height: 20px;
  border: 2px solid rgba(255,255,255,0.1);
  border-top-color: #2563EB;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
.spinner-xs.white { border-color: rgba(255,255,255,0.2); border-top-color: white; }
@keyframes spin { to { transform: rotate(360deg); } }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s, transform 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(10px); }
</style>