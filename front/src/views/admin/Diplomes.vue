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
        <thead>
          <tr>
            <th>Intitulé</th>
            <th>Filière</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="3" class="loading-state"><div class="spinner-sm"></div></td></tr>
          <tr v-else-if="!diplomes.length"><td colspan="3" class="empty-state">Aucun diplôme — créez-en un !</td></tr>
          <tr v-for="d in diplomes" :key="d.id">
            <td style="font-weight:600;color:white">{{ d.intitule }}</td>
            <td><span class="badge badge-purple">{{ d.filiere?.nom ?? '—' }}</span></td>
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
  </div>

  <Teleport to="body">
    <div v-if="showModal" class="dip-overlay" @click.self="closeModal">
      <div class="dip-box">
        <div class="dip-head">
          <h3 class="dip-title">{{ editingId ? 'Modifier le diplôme' : 'Nouveau diplôme' }}</h3>
          <button class="dip-close" @click="closeModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="dip-body">
          <div class="dip-field">
            <label>Intitulé du diplôme</label>
            <input v-model="form.intitule" type="text" placeholder="Ex : Licence Informatique" autofocus />
          </div>
          <div class="dip-field">
            <label>Filière</label>
            <select v-model="form.filiereId">
              <option value="">— Sélectionner une filière —</option>
              <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
            </select>
          </div>
        </div>
        <div class="dip-foot">
          <button class="btn-secondary" @click="closeModal">Annuler</button>
          <button class="btn-primary" @click="save" :disabled="saving">
            <span v-if="saving" class="dip-spinner"></span>
            <span v-else>{{ editingId ? 'Mettre à jour' : 'Créer' }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { diplomeService, filiereService } from '@/services/index.js'

const diplomes  = ref([])
const filieres  = ref([])
const loading   = ref(true)
const saving    = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const form      = ref({ intitule: '', filiereId: '' })
const message   = ref({ text: '', type: 'success' })

async function load() {
  loading.value = true
  try {
    const [d, f] = await Promise.all([diplomeService.getAll(), filiereService.getAll()])
    diplomes.value = d.data
    filieres.value = f.data
  } finally { loading.value = false }
}

function openModal(d = null) {
  editingId.value = d?.id ?? null
  form.value = { intitule: d?.intitule ?? '', filiereId: d?.filiere?.id ?? '' }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
  form.value = { intitule: '', filiereId: '' }
}

async function save() {
  if (!form.value.intitule.trim() || !form.value.filiereId) return
  saving.value = true
  try {
    editingId.value
      ? await diplomeService.update(editingId.value, form.value)
      : await diplomeService.create(form.value)
    showMsg(editingId.value ? 'Diplôme mis à jour.' : 'Diplôme créé.', 'success')
    closeModal()
    await load()
  } catch (e) {
    showMsg(e.response?.data?.error || 'Erreur.', 'danger')
  } finally { saving.value = false }
}

async function confirmDelete(d) {
  if (!confirm(`Supprimer "${d.intitule}" ?`)) return
  try {
    await diplomeService.delete(d.id)
    showMsg('Diplôme supprimé.', 'success')
    await load()
  } catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
}

function showMsg(text, type) {
  message.value = { text, type }
  setTimeout(() => message.value = { text: '', type: 'success' }, 4000)
}

onMounted(load)
</script>

<style>
@keyframes dip-spin { to { transform: rotate(360deg); } }

.dip-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
}
.dip-box {
  background: #1E293B; border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; width: 90%; max-width: 480px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.5);
  position: relative; z-index: 10000;
}
.dip-head {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);
}
.dip-title { font-size: 16px; font-weight: 700; color: white; margin: 0; }
.dip-close {
  background: none; border: none; cursor: pointer; color: #475569;
  padding: 4px; border-radius: 6px; display: flex; align-items: center;
}
.dip-close:hover { background: rgba(255,255,255,0.06); color: white; }
.dip-body { padding: 24px; }
.dip-foot {
  padding: 16px 24px; border-top: 1px solid rgba(255,255,255,0.06);
  display: flex; justify-content: flex-end; gap: 10px;
}
.dip-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
.dip-field label { font-size: 13px; font-weight: 600; color: #64748B; }
.dip-field input, .dip-field select {
  padding: 10px 14px; border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px; font-size: 14px; color: white; background: #0F172A;
  outline: none; font-family: 'DM Sans', sans-serif; width: 100%; box-sizing: border-box;
}
.dip-field input::placeholder { color: #334155; }
.dip-field input:focus, .dip-field select:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
.dip-field select option { background: #1E293B; color: white; }
.dip-spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.2); border-top-color: white;
  border-radius: 50%; animation: dip-spin 0.7s linear infinite; display: inline-block;
}
</style>