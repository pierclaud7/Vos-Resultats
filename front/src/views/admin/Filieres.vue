<template>
  <div class="page">
    <div class="topbar">
      <h1 class="page-title">Filières</h1>
      <button class="btn-primary" @click="openModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nouvelle filière
      </button>
    </div>

    <div v-if="message.text" class="alert" :class="`alert-${message.type}`">
      {{ message.text }}
    </div>

    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Diplômes</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="3" class="loading-state"><div class="spinner-sm"></div></td>
          </tr>
          <tr v-else-if="!filieres.length">
            <td colspan="3" class="empty-state">Aucune filière — créez-en une !</td>
          </tr>
          <tr v-for="f in filieres" :key="f.id">
            <td style="font-weight:600;color:#0F172A">{{ f.nom }}</td>
            <td><span class="badge badge-blue">{{ f.nbDiplomes }} diplôme(s)</span></td>
            <td style="text-align:right">
              <button class="btn-icon" @click="openModal(f)" title="Modifier">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </button>
              <button class="btn-icon danger" @click="confirmDelete(f)" title="Supprimer" style="margin-left:6px">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-head">
          <h3 class="modal-title">{{ editingId ? 'Modifier la filière' : 'Nouvelle filière' }}</h3>
          <button class="modal-close" @click="closeModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="modal-body">
          <div class="field">
            <label>Nom de la filière</label>
            <input v-model="form.nom" type="text" placeholder="Ex : Informatique" autofocus />
          </div>
        </div>
        <div class="modal-foot">
          <button class="btn-secondary" @click="closeModal">Annuler</button>
          <button class="btn-primary" @click="save" :disabled="saving">
            <span v-if="saving" style="width:14px;height:14px;border:2px solid rgba(255,255,255,0.3);border-top-color:white;border-radius:50%;animation:spin 0.7s linear infinite;display:inline-block"></span>
            <span v-else>{{ editingId ? 'Mettre à jour' : 'Créer' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import '@/assets/admin.css'
import { ref, onMounted } from 'vue'
import { filiereService } from '@/services/index.js'

const filieres  = ref([])
const loading   = ref(true)
const saving    = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const form      = ref({ nom: '' })
const message   = ref({ text: '', type: 'success' })

async function load() {
  loading.value = true
  try { filieres.value = (await filiereService.getAll()).data }
  finally { loading.value = false }
}

function openModal(f = null) { editingId.value = f?.id ?? null; form.value = { nom: f?.nom ?? '' }; showModal.value = true }
function closeModal() { showModal.value = false; editingId.value = null; form.value = { nom: '' } }

async function save() {
  if (!form.value.nom.trim()) return
  saving.value = true
  try {
    editingId.value ? await filiereService.update(editingId.value, form.value) : await filiereService.create(form.value)
    showMsg(editingId.value ? 'Filière mise à jour.' : 'Filière créée.', 'success')
    closeModal(); await load()
  } catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
  finally { saving.value = false }
}

async function confirmDelete(f) {
  if (!confirm(`Supprimer "${f.nom}" ?`)) return
  try { await filiereService.delete(f.id); showMsg('Filière supprimée.', 'success'); await load() }
  catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
}

function showMsg(text, type) { message.value = { text, type }; setTimeout(() => message.value = { text: '', type: 'success' }, 4000) }

onMounted(load)
</script>
