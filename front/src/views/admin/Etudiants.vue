<template>
  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 fw-bold mb-0"><i class="fas fa-users me-2 text-success"></i> Étudiants</h1>
      <button class="btn btn-primary" @click="openModal()">
        <i class="fas fa-user-plus me-1"></i> Inscrire un étudiant
      </button>
    </div>

    <!-- Filtre par session -->
    <div class="card border-0 shadow-sm mb-3 p-3">
      <div class="row align-items-center">
        <div class="col-md-4">
          <label class="form-label fw-semibold small mb-1">Filtrer par session</label>
          <select v-model="filterSession" class="form-select form-select-sm" @change="load">
            <option value="">Toutes les sessions</option>
            <option v-for="s in sessions" :key="s.id" :value="s.id">
              {{ s.diplome?.intitule }} — {{ s.annee }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="message.text" class="alert" :class="`alert-${message.type}`">{{ message.text }}</div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Nom / Prénom</th>
              <th>Email</th>
              <th>Session</th>
              <th>Moyenne</th>
              <th>Résultat</th>
              <th class="text-end pe-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="6" class="text-center py-4"><span class="spinner-border text-primary"></span></td></tr>
            <tr v-else-if="etudiants.length === 0"><td colspan="6" class="text-center py-4 text-muted">Aucun étudiant trouvé.</td></tr>
            <tr v-for="e in etudiants" :key="e.id">
              <td class="ps-3">
                <span class="fw-bold text-uppercase">{{ e.nom }}</span> {{ e.prenom }}
              </td>
              <td class="text-muted small">{{ e.email }}</td>
              <td>
                <span v-if="e.session" class="badge bg-light text-dark border">
                  {{ e.session.diplome?.intitule }} ({{ e.session.annee }})
                </span>
              </td>
              <td>
                <span v-if="e.moyenne !== null" class="fw-bold" :class="moyenneColor(e)">{{ e.moyenne }}/20</span>
                <span v-else class="text-muted small">-</span>
              </td>
              <td><span class="badge" :class="resultatBadge(e.resultat)">{{ e.resultat }}</span></td>
              <td class="text-end pe-3">
                <button class="btn btn-sm btn-outline-primary me-1" @click="openModal(e)"><i class="fas fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(e)"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal création/édition -->
    <div v-if="showModal" class="modal d-block" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">{{ editingId ? 'Modifier l\'étudiant' : 'Inscrire un étudiant' }}</h5>
            <button class="btn-close btn-close-white" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Nom *</label>
                <input v-model="form.nom" type="text" class="form-control" placeholder="DUPONT" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Prénom *</label>
                <input v-model="form.prenom" type="text" class="form-control" placeholder="Jean" />
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Email *</label>
              <input v-model="form.email" type="email" class="form-control" placeholder="jean.dupont@exemple.com" />
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Date de naissance</label>
                <input v-model="form.dateNaissance" type="date" class="form-control" />
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Session *</label>
                <select v-model="form.session_id" class="form-select">
                  <option value="">-- Choisir une session --</option>
                  <option v-for="s in sessions" :key="s.id" :value="s.id">
                    {{ s.diplome?.intitule }} — {{ s.annee }}
                  </option>
                </select>
              </div>
            </div>
            <hr>
            <div class="row align-items-center">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Moyenne (/20)</label>
                <input v-model="form.moyenne" type="number" step="0.01" min="0" max="20" class="form-control" placeholder="Ex: 14.5" />
              </div>
              <div class="col-md-6 mb-3 mt-2">
                <div class="form-check border rounded p-3 bg-light">
                  <input v-model="form.estAdmis" class="form-check-input" type="checkbox" id="estAdmis" />
                  <label class="form-check-label fw-bold text-success" for="estAdmis">
                    Déclarer ADMIS
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">Annuler</button>
            <button class="btn btn-primary" @click="save" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              {{ editingId ? 'Mettre à jour' : 'Inscrire' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { etudiantService, sessionService } from '@/services/index.js'

const etudiants   = ref([])
const sessions    = ref([])
const loading     = ref(true)
const saving      = ref(false)
const showModal   = ref(false)
const editingId   = ref(null)
const filterSession = ref('')
const message     = ref({ text: '', type: 'success' })
const form        = ref({ nom: '', prenom: '', email: '', dateNaissance: '', session_id: '', moyenne: null, estAdmis: false })

async function load() {
  loading.value = true
  try {
    const [e, s] = await Promise.all([
      etudiantService.getAll(filterSession.value || null),
      sessionService.getAll(),
    ])
    etudiants.value = e.data
    sessions.value  = s.data
  } finally { loading.value = false }
}

function openModal(etudiant = null) {
  editingId.value = etudiant?.id ?? null
  form.value = {
    nom: etudiant?.nom ?? '', prenom: etudiant?.prenom ?? '',
    email: etudiant?.email ?? '', dateNaissance: etudiant?.dateNaissance ?? '',
    session_id: etudiant?.session?.id ?? '', moyenne: etudiant?.moyenne ?? null,
    estAdmis: etudiant?.estAdmis ?? false,
  }
  showModal.value = true
}

function closeModal() { showModal.value = false; editingId.value = null }

async function save() {
  saving.value = true
  try {
    editingId.value
      ? await etudiantService.update(editingId.value, form.value)
      : await etudiantService.create(form.value)
    showMessage(editingId.value ? 'Étudiant mis à jour.' : 'Étudiant inscrit.', 'success')
    closeModal(); await load()
  } catch (err) { showMessage(err.response?.data?.error || 'Erreur.', 'danger') }
  finally { saving.value = false }
}

async function confirmDelete(e) {
  if (!confirm(`Supprimer ${e.prenom} ${e.nom} ?`)) return
  try { await etudiantService.delete(e.id); showMessage('Étudiant supprimé.', 'success'); await load() }
  catch (err) { showMessage(err.response?.data?.error || 'Erreur.', 'danger') }
}

function moyenneColor(e) {
  if (e.estAdmis || e.moyenne >= 10) return 'text-success'
  if (e.moyenne >= 8) return 'text-warning'
  return 'text-danger'
}

function resultatBadge(resultat) {
  return { 'Admis': 'bg-success', 'Rattrapage': 'bg-warning text-dark', 'Refusé': 'bg-danger', 'En attente': 'bg-secondary' }[resultat] || 'bg-secondary'
}

function showMessage(text, type = 'success') {
  message.value = { text, type }
  setTimeout(() => (message.value = { text: '', type: 'success' }), 4000)
}

onMounted(load)
</script>
