<template>
  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3 fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-warning"></i> Sessions d'examen</h1>
      <button class="btn btn-primary" @click="openModal()">
        <i class="fas fa-plus me-1"></i> Nouvelle session
      </button>
    </div>

    <div v-if="message.text" class="alert" :class="`alert-${message.type}`">{{ message.text }}</div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
          <thead class="table-light">
            <tr>
              <th class="ps-3">Année</th>
              <th>Diplôme / Filière</th>
              <th>Statut</th>
              <th>Taux réussite</th>
              <th>Étudiants</th>
              <th class="text-end pe-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="6" class="text-center py-4"><span class="spinner-border text-primary"></span></td></tr>
            <tr v-else-if="sessions.length === 0"><td colspan="6" class="text-center py-4 text-muted">Aucune session trouvée.</td></tr>
            <tr v-for="s in sessions" :key="s.id">
              <td class="ps-3 fw-bold">{{ s.annee }}</td>
              <td>
                <span class="fw-bold">{{ s.diplome?.intitule }}</span>
                <br><small class="text-muted">{{ s.diplome?.filiere?.nom }}</small>
              </td>
              <td>
                <span class="badge" :class="statutBadge(s.statut)">{{ s.statut }}</span>
              </td>
              <td>
                <span v-if="s.tauxReussite !== null" class="fw-bold text-primary">{{ s.tauxReussite }}%</span>
                <span v-else class="text-muted small">-</span>
              </td>
              <td><span class="badge bg-secondary">{{ s.nbEtudiants }}</span></td>
              <td class="text-end pe-3">
                <!-- Valider -->
                <button v-if="s.statut === 'Brouillon'" class="btn btn-sm btn-info me-1" @click="valider(s)" title="Valider">
                  <i class="fas fa-check-circle"></i>
                </button>
                <!-- Publier + partage -->
                <button v-if="s.statut === 'Validé'" class="btn btn-sm btn-success me-1" @click="publier(s)" title="Publier">
                  <i class="fas fa-globe"></i>
                </button>
                <!-- Partage social (simulé) -->
                <button v-if="s.statut === 'Publié'" class="btn btn-sm btn-outline-info me-1" @click="openShareModal(s)" title="Partager">
                  <i class="fas fa-share-alt"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary me-1" @click="openModal(s)"><i class="fas fa-pen"></i></button>
                <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(s)" :disabled="s.statut === 'Publié'"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal création/édition -->
    <div v-if="showModal" class="modal d-block" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">{{ editingId ? 'Modifier la session' : 'Nouvelle session' }}</h5>
            <button class="btn-close btn-close-white" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Année</label>
              <input v-model="form.annee" type="number" class="form-control" placeholder="2025" />
            </div>
            <div>
              <label class="form-label fw-semibold">Diplôme</label>
              <select v-model="form.diplome_id" class="form-select">
                <option value="">-- Choisir un diplôme --</option>
                <option v-for="d in diplomes" :key="d.id" :value="d.id">
                  {{ d.intitule }} ({{ d.filiere?.nom }})
                </option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" @click="closeModal">Annuler</button>
            <button class="btn btn-primary" @click="save" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
              {{ editingId ? 'Mettre à jour' : 'Créer' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal partage social simulé -->
    <div v-if="shareModal.show" class="modal d-block" style="background:rgba(0,0,0,.5)">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="fas fa-share-alt me-2"></i> Partager les résultats</h5>
            <button class="btn-close btn-close-white" @click="shareModal.show = false"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted small mb-3">Voici le texte qui serait publié sur les réseaux sociaux :</p>
            <div class="bg-light border rounded p-3 mb-3">
              <p class="mb-0 fw-semibold">{{ shareModal.text }}</p>
            </div>
            <p class="text-muted small mb-2">URL simulée :</p>
            <code class="d-block bg-light p-2 rounded">{{ shareModal.url }}</code>
          </div>
          <div class="modal-footer">
            <button class="btn btn-outline-dark me-2" @click="copyShare">
              <i class="fas fa-copy me-1"></i> Copier le texte
            </button>
            <a :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(shareModal.text)}`" target="_blank" class="btn btn-info text-white me-2">
              <i class="fab fa-twitter me-1"></i> Twitter/X
            </a>
            <a :href="`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareModal.url)}`" target="_blank" class="btn btn-primary">
              <i class="fab fa-linkedin me-1"></i> LinkedIn
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
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
const form      = ref({ annee: '', diplome_id: '' })
const message   = ref({ text: '', type: 'success' })
const shareModal = ref({ show: false, text: '', url: '' })

async function load() {
  loading.value = true
  try {
    const [s, d] = await Promise.all([sessionService.getAll(), diplomeService.getAll()])
    sessions.value = s.data
    diplomes.value = d.data
  } finally { loading.value = false }
}

function statutBadge(statut) {
  return { 'Publié': 'bg-success', 'Validé': 'bg-info text-dark', 'Brouillon': 'bg-secondary' }[statut] || 'bg-secondary'
}

function openModal(session = null) {
  editingId.value = session?.id ?? null
  form.value      = { annee: session?.annee ?? '', diplome_id: session?.diplome?.id ?? '' }
  showModal.value = true
}

function closeModal() { showModal.value = false; editingId.value = null }

async function save() {
  saving.value = true
  try {
    editingId.value
      ? await sessionService.update(editingId.value, form.value)
      : await sessionService.create(form.value)
    showMessage(editingId.value ? 'Session mise à jour.' : 'Session créée.', 'success')
    closeModal(); await load()
  } catch (err) { showMessage(err.response?.data?.error || 'Erreur.', 'danger') }
  finally { saving.value = false }
}

async function valider(s) {
  if (!confirm(`Valider la session ${s.annee} ? Le taux de réussite sera calculé automatiquement.`)) return
  try {
    const res = await sessionService.valider(s.id)
    showMessage(`Session validée ! Taux de réussite : ${res.data.tauxReussite}%`, 'success')
    await load()
  } catch (err) { showMessage(err.response?.data?.error || 'Erreur.', 'danger') }
}

async function publier(s) {
  if (!confirm(`Publier les résultats de la session ${s.annee} ? Ils seront visibles publiquement.`)) return
  try {
    await sessionService.publier(s.id)
    showMessage('Session publiée ! Les résultats sont maintenant visibles.', 'success')
    await load()
  } catch (err) { showMessage(err.response?.data?.error || 'Erreur.', 'danger') }
}

function openShareModal(s) {
  const taux    = s.tauxReussite ?? 0
  const diplome = s.diplome?.intitule ?? 'diplôme'
  shareModal.value = {
    show: true,
    text: `🎓 Résultats ${diplome} ${s.annee} : ${taux}% de réussite ! Félicitations aux admis. Consultez vos résultats sur notre plateforme. #Examen #Résultats #${s.annee}`,
    url: `${window.location.origin}/resultats?session=${s.id}`,
  }
}

function copyShare() {
  navigator.clipboard.writeText(shareModal.value.text)
  showMessage('Texte copié dans le presse-papier !', 'success')
}

async function confirmDelete(s) {
  if (!confirm(`Supprimer la session ${s.annee} ?`)) return
  try { await sessionService.delete(s.id); showMessage('Session supprimée.', 'success'); await load() }
  catch (err) { showMessage(err.response?.data?.error || 'Erreur.', 'danger') }
}

function showMessage(text, type = 'success') {
  message.value = { text, type }
  setTimeout(() => (message.value = { text: '', type: 'success' }), 5000)
}

onMounted(load)
</script>
