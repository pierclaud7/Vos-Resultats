<template>
  <div class="page">
    <div class="topbar">
      <h1 class="page-title">Étudiants</h1>
      <div style="display:flex;gap:10px">
        <router-link to="/admin/import-csv" class="btn-secondary" style="text-decoration:none;display:flex;align-items:center;gap:6px">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Import CSV
        </router-link>
        <button class="btn-primary" @click="openModal()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Inscrire
        </button>
      </div>
    </div>

    <!-- Filtre session -->
    <div style="background:white;border:1px solid #E2E8F0;border-radius:12px;padding:16px 20px;margin-bottom:16px;display:flex;align-items:center;gap:16px">
      <label style="font-size:13px;font-weight:600;color:#374151;white-space:nowrap">Filtrer par session</label>
      <select v-model="filterSession" class="filter-select" @change="load">
        <option value="">Toutes les sessions</option>
        <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.diplome?.intitule }} — {{ s.annee }}</option>
      </select>
    </div>

    <div v-if="message.text" class="alert" :class="`alert-${message.type}`">{{ message.text }}</div>

    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Étudiant</th>
            <th>N° Étudiant</th>
            <th>Session</th>
            <th>Moyenne</th>
            <th>Résultat</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="loading-state"><div class="spinner-sm"></div></td></tr>
          <tr v-else-if="!etudiants.length"><td colspan="6" class="empty-state">Aucun étudiant trouvé.</td></tr>
          <tr v-for="e in etudiants" :key="e.id">
            <td>
              <div style="font-weight:600;color:#0F172A">{{ e.nom }} {{ e.prenom }}</div>
              <div style="font-size:12px;color:#94A3B8">{{ e.email }}</div>
            </td>
            <td><code style="font-size:12px;background:#F8FAFC;padding:2px 8px;border-radius:4px;color:#475569">{{ e.numeroEtudiant }}</code></td>
            <td>
              <span v-if="e.session" class="badge badge-gray">{{ e.session.diplome?.intitule }} ({{ e.session.annee }})</span>
            </td>
            <td>
              <span v-if="e.moyenne !== null" style="font-weight:700" :style="{ color: moyenneColor(e) }">{{ e.moyenne }}/20</span>
              <span v-else style="color:#CBD5E1">—</span>
            </td>
            <td><span class="badge" :class="resultatBadge(e.resultat)">{{ e.resultat }}</span></td>
            <td style="text-align:right">
              <button class="btn-icon" @click="openModal(e)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="btn-icon danger" @click="confirmDelete(e)" style="margin-left:6px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal" style="max-width:560px">
        <div class="modal-head">
          <h3 class="modal-title">{{ editingId ? 'Modifier l\'étudiant' : 'Inscrire un étudiant' }}</h3>
          <button class="modal-close" @click="closeModal"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="field"><label>Nom *</label><input v-model="form.nom" type="text" placeholder="DUPONT" /></div>
            <div class="field"><label>Prénom *</label><input v-model="form.prenom" type="text" placeholder="Jean" /></div>
          </div>
          <div class="field"><label>Email *</label><input v-model="form.email" type="email" placeholder="jean.dupont@exemple.com" /></div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="field"><label>Date de naissance</label><input v-model="form.dateNaissance" type="date" /></div>
            <div class="field">
              <label>Session *</label>
              <select v-model="form.session_id">
                <option value="">-- Choisir --</option>
                <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.diplome?.intitule }} — {{ s.annee }}</option>
              </select>
            </div>
          </div>
          <div style="border-top:1px solid #F1F5F9;padding-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:center">
            <div class="field" style="margin:0"><label>Moyenne (/20)</label><input v-model="form.moyenne" type="number" step="0.01" min="0" max="20" placeholder="14.5" /></div>
            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:14px;display:flex;align-items:center;gap:10px;margin-top:4px">
              <input v-model="form.estAdmis" type="checkbox" id="admis" style="width:16px;height:16px;accent-color:#059669" />
              <label for="admis" style="font-size:13.5px;font-weight:600;color:#059669;cursor:pointer;margin:0">Déclarer ADMIS</label>
            </div>
          </div>
        </div>
        <div class="modal-foot">
          <button class="btn-secondary" @click="closeModal">Annuler</button>
          <button class="btn-primary" @click="save" :disabled="saving">{{ editingId ? 'Mettre à jour' : 'Inscrire' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import '@/assets/admin.css'
import { ref, onMounted } from 'vue'
import { etudiantService, sessionService } from '@/services/index.js'

const etudiants = ref([]); const sessions = ref([])
const loading   = ref(true); const saving = ref(false)
const showModal = ref(false); const editingId = ref(null)
const filterSession = ref('')
const message = ref({ text: '', type: 'success' })
const form = ref({ nom: '', prenom: '', email: '', dateNaissance: '', session_id: '', moyenne: null, estAdmis: false })

async function load() {
  loading.value = true
  try {
    const [e, s] = await Promise.all([etudiantService.getAll(filterSession.value || null), sessionService.getAll()])
    etudiants.value = e.data; sessions.value = s.data
  } finally { loading.value = false }
}

function openModal(e = null) {
  editingId.value = e?.id ?? null
  form.value = { nom: e?.nom ?? '', prenom: e?.prenom ?? '', email: e?.email ?? '', dateNaissance: e?.dateNaissance ?? '', session_id: e?.session?.id ?? '', moyenne: e?.moyenne ?? null, estAdmis: e?.estAdmis ?? false }
  showModal.value = true
}

function closeModal() { showModal.value = false; editingId.value = null }

async function save() {
  saving.value = true
  try {
    editingId.value ? await etudiantService.update(editingId.value, form.value) : await etudiantService.create(form.value)
    showMsg('Étudiant enregistré.', 'success'); closeModal(); await load()
  } catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
  finally { saving.value = false }
}

async function confirmDelete(e) {
  if (!confirm(`Supprimer ${e.prenom} ${e.nom} ?`)) return
  try { await etudiantService.delete(e.id); showMsg('Étudiant supprimé.', 'success'); await load() }
  catch (err) { showMsg(err.response?.data?.error || 'Erreur.', 'danger') }
}

function moyenneColor(e) { return e.estAdmis || e.moyenne >= 10 ? '#059669' : e.moyenne >= 8 ? '#D97706' : '#DC2626' }
function resultatBadge(r) { return { Admis: 'badge-green', Rattrapage: 'badge-yellow', Refusé: 'badge-red', 'En attente': 'badge-gray' }[r] || 'badge-gray' }
function showMsg(text, type) { message.value = { text, type }; setTimeout(() => message.value = { text: '', type: 'success' }, 4000) }

onMounted(load)
</script>

<style scoped>
.filter-select { flex: 1; max-width: 320px; padding: 8px 12px; border: 1.5px solid #E2E8F0; border-radius: 8px; font-size: 13.5px; color: #374151; outline: none; font-family: 'DM Sans', sans-serif; }
.filter-select:focus { border-color: #2563EB; }
</style>
