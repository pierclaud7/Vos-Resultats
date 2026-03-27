<template>
  <div class="page">
    <div class="topbar">
      <h1 class="page-title">Sessions d'examen</h1>
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
            <th>Année</th>
            <th>Diplôme / Filière</th>
            <th>Statut</th>
            <th>Taux réussite</th>
            <th>Étudiants</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="loading-state"><div class="spinner-sm"></div></td></tr>
          <tr v-else-if="!sessions.length"><td colspan="6" class="empty-state">Aucune session trouvée.</td></tr>
          <tr v-for="s in sessions" :key="s.id">
            <td style="font-weight:700;color:#0F172A;font-size:16px">{{ s.annee }}</td>
            <td>
              <div style="font-weight:600;color:#0F172A;font-size:13.5px">{{ s.diplome?.intitule }}</div>
              <div style="color:#94A3B8;font-size:12px">{{ s.diplome?.filiere?.nom }}</div>
            </td>
            <td>
              <span class="badge" :class="statutBadge(s.statut)">{{ s.statut }}</span>
            </td>
            <td>
              <span v-if="s.tauxReussite !== null" style="font-weight:700;color:#059669">{{ s.tauxReussite }}%</span>
              <span v-else style="color:#CBD5E1">—</span>
            </td>
            <td><span class="badge badge-gray">{{ s.nbEtudiants }}</span></td>
            <td style="text-align:right">
              <div style="display:flex;gap:6px;justify-content:flex-end">
                <!-- Valider -->
                <button v-if="s.statut === 'Brouillon'" class="btn-icon" @click="valider(s)" title="Valider la session" style="color:#D97706;border-color:#FCD34D">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
                <!-- Publier -->
                <button v-if="s.statut === 'Validé'" class="btn-icon" @click="publier(s)" title="Publier les résultats" style="color:#059669;border-color:#6EE7B7">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </button>
                <!-- Partager -->
                <button v-if="s.statut === 'Publié'" class="btn-icon" @click="openShare(s)" title="Partager">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                </button>
                <!-- Éditer -->
                <button class="btn-icon" @click="openModal(s)">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <!-- Supprimer -->
                <button class="btn-icon danger" @click="confirmDelete(s)" :disabled="s.statut === 'Publié'">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal création/édition -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-head">
          <h3 class="modal-title">{{ editingId ? 'Modifier la session' : 'Nouvelle session' }}</h3>
          <button class="modal-close" @click="closeModal"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
          <div class="field"><label>Année</label><input v-model="form.annee" type="number" placeholder="2025" /></div>
          <div class="field">
            <label>Diplôme</label>
            <select v-model="form.diplome_id">
              <option value="">-- Choisir un diplôme --</option>
              <option v-for="d in diplomes" :key="d.id" :value="d.id">{{ d.intitule }} ({{ d.filiere?.nom }})</option>
            </select>
          </div>
        </div>
        <div class="modal-foot">
          <button class="btn-secondary" @click="closeModal">Annuler</button>
          <button class="btn-primary" @click="save" :disabled="saving">{{ editingId ? 'Mettre à jour' : 'Créer' }}</button>
        </div>
      </div>
    </div>

    <!-- Modal partage social simulé -->
    <div v-if="shareModal.show" class="modal-overlay" @click.self="shareModal.show = false">
      <div class="modal" style="max-width:520px">
        <div class="modal-head">
          <h3 class="modal-title">Partager les résultats</h3>
          <button class="modal-close" @click="shareModal.show = false"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
        </div>
        <div class="modal-body">
          <p style="font-size:13px;color:#64748B;margin:0 0 12px">Texte à publier sur les réseaux sociaux :</p>
          <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:16px;font-size:14px;color:#0F172A;line-height:1.6;margin-bottom:16px">
            {{ shareModal.text }}
          </div>
          <p style="font-size:12px;color:#94A3B8;margin:0 0 6px">URL simulée :</p>
          <code style="display:block;background:#F1F5F9;padding:10px 14px;border-radius:6px;font-size:12px;color:#64748B">{{ shareModal.url }}</code>
        </div>
        <div class="modal-foot">
          <button class="btn-secondary" @click="copyShare">Copier le texte</button>
          <a :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareModal.text)}`" target="_blank" class="btn-primary" style="text-decoration:none">Twitter / X</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import '@/assets/admin.css'
import { ref, onMounted } from 'vue'
import { sessionService, diplomeService } from '@/services/index.js'

const sessions  = ref([]); const diplomes = ref([])
const loading   = ref(true); const saving = ref(false)
const showModal = ref(false); const editingId = ref(null)
const form      = ref({ annee: '', diplome_id: '' })
const message   = ref({ text: '', type: 'success' })
const shareModal = ref({ show: false, text: '', url: '' })

async function load() {
  loading.value = true
  try {
    const [s, d] = await Promise.all([sessionService.getAll(), diplomeService.getAll()])
    sessions.value = s.data; diplomes.value = d.data
  } finally { loading.value = false }
}

function statutBadge(s) {
  return { 'Publié': 'badge-green', 'Validé': 'badge-yellow', 'Brouillon': 'badge-gray' }[s] || 'badge-gray'
}

function openModal(s = null) { editingId.value = s?.id ?? null; form.value = { annee: s?.annee ?? '', diplome_id: s?.diplome?.id ?? '' }; showModal.value = true }
function closeModal() { showModal.value = false; editingId.value = null }

async function save() {
  saving.value = true
  try {
    editingId.value ? await sessionService.update(editingId.value, form.value) : await sessionService.create(form.value)
    showMsg('Session enregistrée.', 'success'); closeModal(); await load()
  } catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
  finally { saving.value = false }
}

async function valider(s) {
  if (!confirm(`Valider la session ${s.annee} ? Le taux de réussite sera calculé automatiquement.`)) return
  try {
    const r = await sessionService.valider(s.id)
    showMsg(`Session validée ! Taux de réussite : ${r.data.tauxReussite}%`, 'success')
    await load()
  } catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
}

async function publier(s) {
  if (!confirm(`Publier les résultats de la session ${s.annee} ? Ils seront visibles publiquement.`)) return
  try { await sessionService.publier(s.id); showMsg('Session publiée !', 'success'); await load() }
  catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
}

function openShare(s) {
  const taux = s.tauxReussite ?? 0
  shareModal.value = {
    show: true,
    text: `🎓 Résultats ${s.diplome?.intitule} ${s.annee} : ${taux}% de réussite ! Félicitations aux admis. #Résultats #${s.annee}`,
    url: `${window.location.origin}/resultats?session=${s.id}`,
  }
}

function copyShare() {
  navigator.clipboard.writeText(shareModal.value.text)
  showMsg('Texte copié !', 'success')
}

async function confirmDelete(s) {
  if (!confirm(`Supprimer la session ${s.annee} ?`)) return
  try { await sessionService.delete(s.id); showMsg('Session supprimée.', 'success'); await load() }
  catch (e) { showMsg(e.response?.data?.error || 'Erreur.', 'danger') }
}

function showMsg(text, type) { message.value = { text, type }; setTimeout(() => message.value = { text: '', type: 'success' }, 5000) }
onMounted(load)
</script>
