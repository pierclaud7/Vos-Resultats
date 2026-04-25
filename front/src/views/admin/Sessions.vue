<template>
  <div class="page">
    <div class="topbar">
      <h1 class="page-title">Sessions</h1>
      <button class="btn-primary" @click="openModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle session
      </button>
    </div>

    <div v-if="message.text" class="alert" :class="`alert-${message.type}`">{{ message.text }}</div>

    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Diplôme</th>
            <th>Filière</th>
            <th>Année</th>
            <th>Statut</th>
            <th>Taux</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="loading-state"><div class="spinner-sm"></div></td></tr>
          <tr v-else-if="!sessions.length"><td colspan="6" class="empty-state">Aucune session — créez-en une !</td></tr>
          <tr v-for="s in sessions" :key="s.id">
            <td style="font-weight:600;color:white">
              <router-link :to="`/admin/sessions/${s.id}`" class="ses-link">{{ s.diplome?.intitule ?? '—' }}</router-link>
            </td>
            <td>{{ s.diplome?.filiere?.nom ?? '—' }}</td>
            <td>{{ s.annee }}</td>
            <td><span class="badge" :class="badgeClass(s.statut)">{{ s.statut }}</span></td>
            <td>{{ s.tauxReussite !== null ? `${s.tauxReussite}%` : '—' }}</td>
            <td style="text-align:right">
              <button class="btn-icon" @click="openModal(s)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="btn-icon danger" @click="confirmDelete(s)" style="margin-left:6px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <Teleport to="body">
    <div v-if="showModal" class="ses-overlay" @click.self="closeModal">
      <div class="ses-box">
        <div class="ses-head">
          <h3 class="ses-title">{{ editingId ? 'Modifier la session' : 'Nouvelle session' }}</h3>
          <button class="ses-close" @click="closeModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="ses-body">
          <div class="ses-field">
            <label>Diplôme</label>
            <select v-model="form.diplomeId">
              <option value="">— Sélectionner un diplôme —</option>
              <option v-for="d in diplomes" :key="d.id" :value="d.id">{{ d.intitule }}</option>
            </select>
          </div>
          <div class="ses-field">
            <label>Année</label>
            <input v-model="form.annee" type="number" placeholder="2025" min="2000" max="2100" />
          </div>
        </div>
        <div class="ses-foot">
          <button class="btn-secondary" @click="closeModal">Annuler</button>
          <button class="btn-primary" @click="save" :disabled="saving">
            <span v-if="saving" class="ses-spinner"></span>
            <span v-else>{{ editingId ? 'Mettre à jour' : 'Créer' }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { sessionService, diplomeService } from '@/services/index.js'

const sessions  = ref([])
const diplomes  = ref([])
const loading   = ref(true)
const saving    = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const form      = ref({ diplomeId: '', annee: new Date().getFullYear() })
const message   = ref({ text: '', type: 'success' })

async function load() {
  loading.value = true
  try {
    const [s, d] = await Promise.all([sessionService.getAll(), diplomeService.getAll()])
    sessions.value = s.data
    diplomes.value = d.data
  } finally { loading.value = false }
}

function openModal(s = null) {
  editingId.value = s?.id ?? null
  form.value = { diplomeId: s?.diplome?.id ?? '', annee: s?.annee ?? new Date().getFullYear() }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
  form.value = { diplomeId: '', annee: new Date().getFullYear() }
}

async function save() {
  if (!form.value.diplomeId || !form.value.annee) return
  saving.value = true
  try {
    editingId.value
      ? await sessionService.update(editingId.value, form.value)
      : await sessionService.create(form.value)
    showMsg(editingId.value ? 'Session mise à jour.' : 'Session créée.', 'success')
    closeModal()
    await load()
  } catch (e) {
    showMsg(e.response?.data?.error || 'Erreur.', 'danger')
  } finally { saving.value = false }
}

async function confirmDelete(s) {
  if (!confirm('Supprimer cette session ?')) return
  try {
    await sessionService.delete(s.id)
    showMsg('Session supprimée.', 'success')
    await load()
  } catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
}

function badgeClass(statut) {
  if (statut === 'Publié') return 'badge-green'
  if (statut === 'Validé') return 'badge-blue'
  return 'badge-gray'
}

function showMsg(text, type) {
  message.value = { text, type }
  setTimeout(() => message.value = { text: '', type: 'success' }, 4000)
}

onMounted(load)
</script>

<style>
@keyframes ses-spin { to { transform: rotate(360deg); } }

.ses-link { color: #60A5FA; text-decoration: none; font-weight: 600; }
.ses-link:hover { text-decoration: underline; }

.ses-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
}
.ses-box {
  background: #1E293B; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; width: 90%; max-width: 480px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.5);
  position: relative; z-index: 10000;
}
.ses-head {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);
}
.ses-title { font-size: 16px; font-weight: 700; color: white; margin: 0; }
.ses-close {
  background: none; border: none; cursor: pointer; color: #475569;
  padding: 4px; border-radius: 6px; display: flex; align-items: center;
}
.ses-close:hover { background: rgba(255,255,255,0.06); color: white; }
.ses-body { padding: 24px; }
.ses-foot {
  padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.06);
  display: flex; justify-content: flex-end; gap: 10px;
}
.ses-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
.ses-field label { font-size: 13px; font-weight: 600; color: #64748B; }
.ses-field input, .ses-field select {
  padding: 10px 14px; border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px; font-size: 14px; color: white; background: #0F172A;
  outline: none; font-family: 'DM Sans', sans-serif; width: 100%; box-sizing: border-box;
}
.ses-field input::placeholder { color: #334155; }
.ses-field input:focus, .ses-field select:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
.ses-field select option { background: #1E293B; color: white; }
.ses-spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.2); border-top-color: white;
  border-radius: 50%; animation: ses-spin 0.7s linear infinite; display: inline-block;
}
</style>