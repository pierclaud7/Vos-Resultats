<template>
  <div class="page">

    <div v-if="loading" class="loading-state">
      <div class="spinner-sm"></div>
    </div>

    <template v-else-if="session">
      <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px">
          <router-link to="/admin/sessions" class="back-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          </router-link>
          <div>
            <h1 class="page-title">{{ session.diplome?.intitule }} — {{ session.annee }}</h1>
            <span class="badge" :class="badgeClass(session.statut)">{{ session.statut }}</span>
          </div>
        </div>
      </div>

      <div class="sd-grid">
        <div class="sd-card">
          <div class="sd-card-title">Informations</div>
          <div class="sd-info-row"><span class="sd-info-label">Filière</span><span class="sd-info-val">{{ session.diplome?.filiere?.nom ?? '—' }}</span></div>
          <div class="sd-info-row"><span class="sd-info-label">Diplôme</span><span class="sd-info-val">{{ session.diplome?.intitule ?? '—' }}</span></div>
          <div class="sd-info-row"><span class="sd-info-label">Année</span><span class="sd-info-val">{{ session.annee }}</span></div>
          <div class="sd-info-row"><span class="sd-info-label">Étudiants</span><span class="sd-info-val">{{ etudiants.length }}</span></div>
        </div>

        <div class="sd-card sd-card-center">
          <div class="sd-card-title">Taux de réussite</div>
          <div v-if="session.tauxReussite !== null" class="sd-taux">{{ session.tauxReussite }}%</div>
          <div v-else class="sd-taux-empty">Non calculé</div>
          <div class="sd-taux-sub">{{ nbAdmis }} admis sur {{ etudiants.length }} étudiant(s)</div>
        </div>

        <div class="sd-card">
          <div class="sd-card-title">Actions</div>
          <div style="display:flex;flex-direction:column;gap:10px">
            <button
              class="sd-btn sd-btn-warning"
              :disabled="actionLoading"
              @click="handleValider"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              Valider & calculer le taux
            </button>
            <button
              class="sd-btn sd-btn-success"
              :disabled="session.statut !== 'Validé' || actionLoading"
              @click="handlePublier"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
              Publier les résultats
            </button>
            <button
              class="sd-btn sd-btn-outline"
              :disabled="session.tauxReussite === null"
              @click="showShare = true"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
              Partager le taux
            </button>
          </div>
        </div>
      </div>

      <div v-if="message.text" class="alert" :class="`alert-${message.type}`" style="margin-bottom:16px">
        {{ message.text }}
      </div>

      <div class="table-card">
        <div class="table-card-head">
          <h3 class="table-card-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            Étudiants de cette session
          </h3>
          <button class="btn-primary" @click="openModal()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajouter
          </button>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>N° Étudiant</th>
              <th>Nom</th>
              <th>Prénom</th>
              <th>Email</th>
              <th>Moyenne</th>
              <th>Résultat</th>
              <th style="text-align:right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loadingEtudiants">
              <td colspan="7" class="loading-state"><div class="spinner-sm"></div></td>
            </tr>
            <tr v-else-if="!etudiants.length">
              <td colspan="7" class="empty-state">Aucun étudiant dans cette session.</td>
            </tr>
            <tr v-for="e in etudiants" :key="e.id">
              <td><code style="font-size:12px;background:#F1F5F9;padding:2px 6px;border-radius:4px">{{ e.numeroEtudiant }}</code></td>
              <td style="font-weight:600;color:#0F172A">{{ e.nom }}</td>
              <td>{{ e.prenom }}</td>
              <td style="color:#64748B;font-size:13px">{{ e.email }}</td>
              <td>{{ e.moyenne !== null ? `${e.moyenne}/20` : '—' }}</td>
              <td>
                <span class="badge" :class="resultatBadge(e.resultat)">{{ e.resultat ?? '—' }}</span>
              </td>
              <td style="text-align:right">
                <button class="btn-icon" @click="openModal(e)" title="Modifier">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="btn-icon danger" @click="confirmDelete(e)" title="Supprimer" style="margin-left:6px">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>

  <Teleport to="body">
    <div v-if="showModal" class="sdd-overlay" @click.self="closeModal">
      <div class="sdd-box">
        <div class="sdd-head">
          <h3 class="sdd-title">{{ editingId ? 'Modifier l\'étudiant' : 'Ajouter un étudiant' }}</h3>
          <button class="sdd-close" @click="closeModal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="sdd-body">
          <div class="sdd-row">
            <div class="sdd-field">
              <label>Nom</label>
              <input v-model="form.nom" type="text" placeholder="DUPONT" autofocus />
            </div>
            <div class="sdd-field">
              <label>Prénom</label>
              <input v-model="form.prenom" type="text" placeholder="Marie" />
            </div>
          </div>
          <div class="sdd-field">
            <label>Email</label>
            <input v-model="form.email" type="email" placeholder="marie@exemple.com" />
          </div>
          <div class="sdd-row">
            <div class="sdd-field">
              <label>Moyenne</label>
              <input v-model="form.moyenne" type="number" placeholder="12.5" min="0" max="20" step="0.5" />
            </div>
            <div class="sdd-field">
              <label>Résultat</label>
              <select v-model="form.resultat">
                <option value="">— Automatique —</option>
                <option value="Admis">Admis</option>
                <option value="Ajourné">Ajourné</option>
              </select>
            </div>
          </div>
        </div>
        <div class="sdd-foot">
          <button class="btn-secondary" @click="closeModal">Annuler</button>
          <button class="btn-primary" @click="save" :disabled="saving">
            <span v-if="saving" class="sdd-spinner"></span>
            <span v-else>{{ editingId ? 'Mettre à jour' : 'Ajouter' }}</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>

  <Teleport to="body">
    <div v-if="showShare" class="sdd-overlay" @click.self="showShare = false">
      <div class="sdd-box">
        <div class="sdd-head">
          <h3 class="sdd-title">Partager sur les réseaux</h3>
          <button class="sdd-close" @click="showShare = false">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="sdd-body">
          <div class="sdd-field">
            <label>Texte à publier</label>
            <textarea class="sdd-textarea" readonly>{{ shareText }}</textarea>
          </div>
          <div class="sdd-field">
            <label>URL</label>
            <div style="display:flex;gap:8px">
              <input :value="shareUrl" readonly style="flex:1;padding:10px 14px;border:1.5px solid #E2E8F0;border-radius:8px;font-size:13px;color:#64748B;background:#F8FAFC" />
              <button class="btn-secondary" @click="copyUrl" style="white-space:nowrap">
                {{ copied ? '✓ Copié' : 'Copier' }}
              </button>
            </div>
          </div>
        </div>
        <div class="sdd-foot">
          <button class="btn-primary" @click="showShare = false">Fermer</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { sessionService, etudiantService } from '@/services/index.js'

const route  = useRoute()
const id     = route.params.id

const session          = ref(null)
const etudiants        = ref([])
const loading          = ref(true)
const loadingEtudiants = ref(true)
const actionLoading    = ref(false)
const showModal        = ref(false)
const showShare        = ref(false)
const editingId        = ref(null)
const saving           = ref(false)
const copied           = ref(false)
const message          = ref({ text: '', type: 'success' })
const form             = ref({ nom: '', prenom: '', email: '', moyenne: '', resultat: '' })

const nbAdmis = computed(() =>
  etudiants.value.filter(e => e.resultat === 'Admis').length
)

const shareText = computed(() => {
  if (!session.value) return ''
  return `🎓 Résultats ${session.value.diplome?.intitule} ${session.value.annee} — Taux de réussite : ${session.value.tauxReussite}% ! Félicitations aux admis ! #VosResultats`
})

const shareUrl = computed(() => {
  if (!session.value) return ''
  return `https://vos-resultats.fr/resultats/${session.value.diplome?.filiere?.id}/${session.value.diplome?.id}?annee=${session.value.annee}`
})

function badgeClass(statut) {
  if (statut === 'Publié') return 'badge-green'
  if (statut === 'Validé') return 'badge-blue'
  return 'badge-gray'
}

function resultatBadge(r) {
  if (r === 'Admis')      return 'badge-green'
  if (r === 'Ajourné')    return 'badge-red'
  if (r === 'Rattrapage') return 'badge-yellow'
  return 'badge-gray'
}

async function loadSession() {
  const res = await sessionService.getById(id)
  session.value = res.data
}

async function loadEtudiants() {
  loadingEtudiants.value = true
  const res = await etudiantService.getAll(id)
  etudiants.value = res.data
  loadingEtudiants.value = false
}

async function handleValider() {
  actionLoading.value = true
  try {
    await sessionService.valider(id)
    await Promise.all([loadSession(), loadEtudiants()])
    showMsg('Session validée avec succès.', 'success')
  } catch (err) {
    showMsg(err.response?.data?.error || 'Erreur lors de la validation.', 'danger')
  } finally {
    actionLoading.value = false
  }
}

async function handlePublier() {
  actionLoading.value = true
  try {
    await sessionService.publier(id)
    await loadSession()
    showMsg('Session publiée ! Les résultats sont visibles publiquement.', 'success')
  } catch (err) {
    showMsg(err.response?.data?.error || 'Erreur lors de la publication.', 'danger')
  } finally {
    actionLoading.value = false
  }
}

function openModal(e = null) {
  editingId.value = e?.id ?? null
  form.value = {
    nom:      e?.nom      ?? '',
    prenom:   e?.prenom   ?? '',
    email:    e?.email    ?? '',
    moyenne:  e?.moyenne  ?? '',
    resultat: e?.resultat ?? '',
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingId.value = null
  form.value = { nom: '', prenom: '', email: '', moyenne: '', resultat: '' }
}

async function save() {
  if (!form.value.nom.trim() || !form.value.prenom.trim()) return
  saving.value = true
  try {
    const payload = {
      nom:        form.value.nom,
      prenom:     form.value.prenom,
      email:      form.value.email,
      session_id: id,
      moyenne:    form.value.moyenne !== '' ? parseFloat(form.value.moyenne) : null,
      resultat:   form.value.resultat || null,
    }
    editingId.value
      ? await etudiantService.update(editingId.value, payload)
      : await etudiantService.create(payload)
    showMsg(editingId.value ? 'Étudiant mis à jour.' : 'Étudiant ajouté.', 'success')
    closeModal()
    await loadEtudiants()
  } catch (e) {
    showMsg(e.response?.data?.error || 'Erreur.', 'danger')
  } finally {
    saving.value = false
  }
}

async function confirmDelete(e) {
  if (!confirm(`Supprimer ${e.prenom} ${e.nom} ?`)) return
  try {
    await etudiantService.delete(e.id)
    showMsg('Étudiant supprimé.', 'success')
    await loadEtudiants()
  } catch (err) {
    showMsg(err.response?.data?.error || 'Erreur.', 'danger')
  }
}

function copyUrl() {
  navigator.clipboard.writeText(shareUrl.value)
  copied.value = true
  setTimeout(() => copied.value = false, 2000)
}

function showMsg(text, type) {
  message.value = { text, type }
  setTimeout(() => message.value = { text: '', type: 'success' }, 4000)
}

onMounted(async () => {
  await Promise.all([loadSession(), loadEtudiants()])
  loading.value = false
})
</script>

<style>
@keyframes sdd-spin { to { transform: rotate(360deg); } }

.sd-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
  margin-bottom: 24px;
}
.sd-card {
  background: white;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 20px 24px;
}
.sd-card-center { text-align: center; }
.sd-card-title {
  font-size: 12px;
  font-weight: 700;
  color: #94A3B8;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  margin-bottom: 16px;
}
.sd-info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #F1F5F9;
  font-size: 13.5px;
}
.sd-info-row:last-child { border-bottom: none; }
.sd-info-label { color: #64748B; }
.sd-info-val   { font-weight: 600; color: #0F172A; }
.sd-taux {
  font-size: 52px;
  font-weight: 800;
  color: #2563EB;
  line-height: 1;
  margin-bottom: 8px;
}
.sd-taux-empty { font-size: 16px; color: #94A3B8; margin-bottom: 8px; }
.sd-taux-sub   { font-size: 13px; color: #64748B; }
.sd-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  font-family: 'DM Sans', sans-serif;
  transition: all 0.15s;
}
.sd-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.sd-btn-warning { background: #FFFBEB; color: #D97706; border: 1.5px solid #FDE68A; }
.sd-btn-warning:not(:disabled):hover { background: #FEF3C7; }
.sd-btn-success { background: #F0FDF4; color: #059669; border: 1.5px solid #BBF7D0; }
.sd-btn-success:not(:disabled):hover { background: #DCFCE7; }
.sd-btn-outline { background: white; color: #2563EB; border: 1.5px solid #BFDBFE; }
.sd-btn-outline:not(:disabled):hover { background: #EFF6FF; }
.back-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  background: white;
  border: 1.5px solid #E2E8F0;
  border-radius: 8px;
  color: #374151;
  text-decoration: none;
  flex-shrink: 0;
  transition: all 0.15s;
}
.back-btn:hover { border-color: #CBD5E1; background: #F8FAFC; }
.table-card-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #F1F5F9;
}
.table-card-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 700;
  color: #0F172A;
  margin: 0;
}
.sdd-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(15, 23, 42, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.sdd-box {
  background: #ffffff;
  border-radius: 14px;
  width: 90%;
  max-width: 520px;
  box-shadow: 0 25px 60px rgba(0,0,0,0.35);
  position: relative;
  z-index: 10000;
}
.sdd-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #F1F5F9;
}
.sdd-title {
  font-size: 16px;
  font-weight: 700;
  color: #0F172A;
  margin: 0;
}
.sdd-close {
  background: none;
  border: none;
  cursor: pointer;
  color: #94A3B8;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
}
.sdd-close:hover { background: #F1F5F9; color: #0F172A; }
.sdd-body { padding: 24px; }
.sdd-foot {
  padding: 16px 24px;
  border-top: 1px solid #F1F5F9;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
.sdd-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 18px;
  flex: 1;
}
.sdd-field label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}
.sdd-field input,
.sdd-field select {
  padding: 10px 14px;
  border: 1.5px solid #E2E8F0;
  border-radius: 8px;
  font-size: 14px;
  color: #0F172A;
  background: #ffffff;
  outline: none;
  font-family: 'DM Sans', sans-serif;
  width: 100%;
  box-sizing: border-box;
}
.sdd-field input:focus,
.sdd-field select:focus {
  border-color: #2563EB;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.sdd-row { display: flex; gap: 16px; }
.sdd-textarea {
  width: 100%;
  padding: 12px 14px;
  border: 1.5px solid #E2E8F0;
  border-radius: 8px;
  font-size: 13px;
  color: #374151;
  background: #F8FAFC;
  resize: none;
  height: 90px;
  font-family: 'DM Sans', sans-serif;
  box-sizing: border-box;
}
.sdd-spinner {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: sdd-spin 0.7s linear infinite;
  display: inline-block;
}
</style>