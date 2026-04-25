<template>
  <div class="page">
    <div class="topbar">
      <h1 class="page-title">Étudiants</h1>
      <button class="btn-primary" @click="openModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvel étudiant
      </button>
    </div>

    <div v-if="message.text" class="alert" :class="`alert-${message.type}`">{{ message.text }}</div>

    <div class="etu-filters">
      <input v-model="search" type="text" class="etu-search" placeholder="Rechercher par nom, prénom, numéro..." />
      <select v-model="filterSession" class="etu-select">
        <option value="">Toutes les sessions</option>
        <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.diplome?.intitule ?? '—' }} — {{ s.annee }}</option>
      </select>
    </div>

    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>N° Étudiant</th>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Session</th>
            <th>Moyenne</th>
            <th>Résultat</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="loading-state"><div class="spinner-sm"></div></td></tr>
          <tr v-else-if="!filtered.length"><td colspan="7" class="empty-state">Aucun étudiant trouvé.</td></tr>
          <tr v-for="e in filtered" :key="e.id">
            <td><code class="etu-code">{{ e.numeroEtudiant }}</code></td>
            <td style="font-weight:600;color:white">{{ e.prenom }} {{ e.nom }}</td>
            <td>{{ e.email }}</td>
            <td>{{ e.session?.diplome?.intitule ? `${e.session.diplome.intitule} — ${e.session.annee}` : '—' }}</td>
            <td>{{ e.moyenne !== null ? `${e.moyenne}/20` : '—' }}</td>
            <td><span class="badge" :class="resultatBadge(e.resultat)">{{ e.resultat ?? '—' }}</span></td>
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
  </div>

  <Teleport to="body">
    <div v-if="showModal" class="etu-overlay" @click.self="closeModal">
      <div class="etu-box">
        <div class="etu-head">
          <h3 class="etu-title">{{ editingId ? 'Modifier l\'étudiant' : 'Nouvel étudiant' }}</h3>
          <button class="etu-close" @click="closeModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="etu-body">
          <div class="etu-row">
            <div class="etu-field"><label>Nom</label><input v-model="form.nom" type="text" placeholder="DUPONT" autofocus /></div>
            <div class="etu-field"><label>Prénom</label><input v-model="form.prenom" type="text" placeholder="Marie" /></div>
          </div>
          <div class="etu-field"><label>Email</label><input v-model="form.email" type="email" placeholder="marie.dupont@email.com" /></div>
          <div class="etu-field">
            <label>Session</label>
            <select v-model="form.sessionId">
              <option value="">— Sélectionner une session —</option>
              <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.diplome?.intitule ?? '—' }} — {{ s.annee }}</option>
            </select>
          </div>
          <div class="etu-row">
            <div class="etu-field"><label>Moyenne</label><input v-model="form.moyenne" type="number" placeholder="12.5" min="0" max="20" step="0.5" /></div>
            <div class="etu-field">
              <label>Résultat</label>
              <select v-model="form.resultat">
                <option value="">— Automatique —</option>
                <option value="Admis">Admis</option>
                <option value="Ajourné">Ajourné</option>
                <option value="Refusé">Refusé</option>
                <option value="Rattrapage">Rattrapage</option>
              </select>
            </div>
          </div>
        </div>
        <div class="etu-foot">
          <button class="btn-secondary" @click="closeModal">Annuler</button>
          <button class="btn-primary" @click="save" :disabled="saving">
            <span v-if="saving" class="etu-spinner"></span>
            <span v-else>{{ editingId ? 'Mettre à jour' : 'Créer' }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { etudiantService, sessionService } from '@/services/index.js'

const etudiants     = ref([])
const sessions      = ref([])
const loading       = ref(true)
const saving        = ref(false)
const showModal     = ref(false)
const editingId     = ref(null)
const search        = ref('')
const filterSession = ref('')
const message       = ref({ text: '', type: 'success' })
const form          = ref({ nom: '', prenom: '', email: '', sessionId: '', moyenne: '', resultat: '' })

const filtered = computed(() => {
  let list = etudiants.value
  if (filterSession.value) list = list.filter(e => e.session?.id == filterSession.value)
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(e =>
      e.nom?.toLowerCase().includes(q) || e.prenom?.toLowerCase().includes(q) ||
      e.numeroEtudiant?.toLowerCase().includes(q) || e.email?.toLowerCase().includes(q)
    )
  }
  return list
})

async function load() {
  loading.value = true
  try {
    const [et, se] = await Promise.all([etudiantService.getAll(), sessionService.getAll()])
    etudiants.value = et.data
    sessions.value  = se.data
  } finally { loading.value = false }
}

function openModal(e = null) {
  editingId.value = e?.id ?? null
  form.value = {
    nom: e?.nom ?? '', prenom: e?.prenom ?? '', email: e?.email ?? '',
    sessionId: e?.session?.id ?? '', moyenne: e?.moyenne ?? '', resultat: e?.resultat ?? '',
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
  form.value = { nom: '', prenom: '', email: '', sessionId: '', moyenne: '', resultat: '' }
}

async function save() {
  if (!form.value.nom.trim() || !form.value.prenom.trim()) return
  saving.value = true
  try {
    editingId.value
      ? await etudiantService.update(editingId.value, form.value)
      : await etudiantService.create(form.value)
    showMsg(editingId.value ? 'Étudiant mis à jour.' : 'Étudiant créé.', 'success')
    closeModal()
    await load()
  } catch (e) {
    showMsg(e.response?.data?.error || 'Erreur.', 'danger')
  } finally { saving.value = false }
}

async function confirmDelete(e) {
  if (!confirm(`Supprimer ${e.prenom} ${e.nom} ?`)) return
  try {
    await etudiantService.delete(e.id)
    showMsg('Étudiant supprimé.', 'success')
    await load()
  } catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
}

function resultatBadge(r) {
  if (r === 'Admis')      return 'badge-green'
  if (r === 'Ajourné')    return 'badge-red'
  if (r === 'Refusé')     return 'badge-red'
  if (r === 'Rattrapage') return 'badge-yellow'
  return 'badge-gray'
}

function showMsg(text, type) {
  message.value = { text, type }
  setTimeout(() => message.value = { text: '', type: 'success' }, 4000)
}

onMounted(load)
</script>

<style>
@keyframes etu-spin { to { transform: rotate(360deg); } }

.etu-code { font-size: 12px; background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px; color: #60A5FA; }

.etu-filters { display: flex; gap: 12px; margin-bottom: 20px; }
.etu-search, .etu-select {
  padding: 10px 14px; border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px; font-size: 14px; color: white; background: #1E293B;
  outline: none; font-family: 'DM Sans', sans-serif;
}
.etu-search { flex: 1; }
.etu-search::placeholder { color: #334155; }
.etu-search:focus, .etu-select:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
.etu-select option { background: #1E293B; color: white; }

.etu-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
}
.etu-box {
  background: #1E293B; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; width: 90%; max-width: 560px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.5);
  position: relative; z-index: 10000;
}
.etu-head {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);
}
.etu-title { font-size: 16px; font-weight: 700; color: white; margin: 0; }
.etu-close {
  background: none; border: none; cursor: pointer; color: #475569;
  padding: 4px; border-radius: 6px; display: flex; align-items: center;
}
.etu-close:hover { background: rgba(255,255,255,0.06); color: white; }
.etu-body { padding: 24px; }
.etu-foot {
  padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.06);
  display: flex; justify-content: flex-end; gap: 10px;
}
.etu-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; flex: 1; }
.etu-field label { font-size: 13px; font-weight: 600; color: #64748B; }
.etu-field input, .etu-field select {
  padding: 10px 14px; border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px; font-size: 14px; color: white; background: #0F172A;
  outline: none; font-family: 'DM Sans', sans-serif; width: 100%; box-sizing: border-box;
}
.etu-field input::placeholder { color: #334155; }
.etu-field input:focus, .etu-field select:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
.etu-field select option { background: #1E293B; color: white; }
.etu-row { display: flex; gap: 16px; }
.etu-spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.2); border-top-color: white;
  border-radius: 50%; animation: etu-spin 0.7s linear infinite; display: inline-block;
}
</style>