<template>
  <div class="page">
    <div class="topbar">
      <h1 class="page-title">Diplômes</h1>
      <button class="btn-primary" @click="openModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouveau diplôme
      </button>
    </div>

    <div v-if="message.text" class="alert" :class="`alert-${message.type}`">{{ message.text }}</div>

    <div class="table-card">
      <table class="data-table">
        <thead><tr><th>Intitulé</th><th>Filière</th><th>Sessions</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="4" class="loading-state"><div class="spinner-sm"></div></td></tr>
          <tr v-else-if="!diplomes.length"><td colspan="4" class="empty-state">Aucun diplôme trouvé.</td></tr>
          <tr v-for="d in diplomes" :key="d.id">
            <td style="font-weight:600;color:#0F172A">{{ d.intitule }}</td>
            <td><span class="badge badge-gray">{{ d.filiere?.nom ?? '-' }}</span></td>
            <td><span class="badge badge-blue">{{ d.nbSessions }}</span></td>
            <td style="text-align:right">
              <button class="btn-icon" @click="openModal(d)">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="btn-icon danger" @click="confirmDelete(d)" style="margin-left:6px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-head">
          <h3 class="modal-title">{{ editingId ? 'Modifier' : 'Nouveau diplôme' }}</h3>
          <button class="modal-close" @click="closeModal"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
          <div class="field"><label>Intitulé</label><input v-model="form.intitule" type="text" placeholder="Ex : BTS SIO" /></div>
          <div class="field">
            <label>Filière</label>
            <select v-model="form.filiere_id">
              <option value="">-- Choisir --</option>
              <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
            </select>
          </div>
        </div>
        <div class="modal-foot">
          <button class="btn-secondary" @click="closeModal">Annuler</button>
          <button class="btn-primary" @click="save" :disabled="saving">{{ editingId ? 'Mettre à jour' : 'Créer' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import '@/assets/admin.css'
import { ref, onMounted } from 'vue'
import { diplomeService, filiereService } from '@/services/index.js'

const diplomes  = ref([]); const filieres = ref([])
const loading   = ref(true); const saving = ref(false)
const showModal = ref(false); const editingId = ref(null)
const form      = ref({ intitule: '', filiere_id: '' })
const message   = ref({ text: '', type: 'success' })

async function load() {
  loading.value = true
  try { const [d, f] = await Promise.all([diplomeService.getAll(), filiereService.getAll()]); diplomes.value = d.data; filieres.value = f.data }
  finally { loading.value = false }
}

function openModal(d = null) { editingId.value = d?.id ?? null; form.value = { intitule: d?.intitule ?? '', filiere_id: d?.filiere?.id ?? '' }; showModal.value = true }
function closeModal() { showModal.value = false; editingId.value = null }

async function save() {
  saving.value = true
  try {
    editingId.value ? await diplomeService.update(editingId.value, form.value) : await diplomeService.create(form.value)
    showMsg('Diplôme enregistré.', 'success'); closeModal(); await load()
  } catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
  finally { saving.value = false }
}

async function confirmDelete(d) {
  if (!confirm(`Supprimer "${d.intitule}" ?`)) return
  try { await diplomeService.delete(d.id); showMsg('Supprimé.', 'success'); await load() }
  catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
}

function showMsg(text, type) { message.value = { text, type }; setTimeout(() => message.value = { text: '', type: 'success' }, 4000) }
onMounted(load)
</script>
