<template>
  <div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 fw-bold mb-0">
          <i class="fas fa-file-csv me-2 text-success"></i> Import CSV
        </h1>
        <p class="text-muted small mb-0">Importer une liste d'étudiants depuis un fichier CSV</p>
      </div>
      <a :href="`/api/import/csv/template`" class="btn btn-outline-secondary btn-sm" download>
        <i class="fas fa-download me-1"></i> Télécharger le modèle CSV
      </a>
    </div>

    <!-- Alertes -->
    <div v-if="result" class="alert mb-4" :class="result.created > 0 ? 'alert-success' : 'alert-warning'">
      <i class="fas fa-check-circle me-2"></i>
      <strong>{{ result.message }}</strong>
      <ul v-if="result.errors.length > 0" class="mt-2 mb-0 small">
        <li v-for="err in result.errors" :key="err">{{ err }}</li>
      </ul>
    </div>

    <div v-if="errorMsg" class="alert alert-danger mb-4">
      <i class="fas fa-exclamation-circle me-2"></i> {{ errorMsg }}
    </div>

    <div class="row">
      <!-- Formulaire import -->
      <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-upload text-primary me-2"></i> Importer des étudiants
          </div>
          <div class="card-body">

            <div class="mb-3">
              <label class="form-label fw-semibold">Session d'examen *</label>
              <select v-model="selectedSession" class="form-select">
                <option value="">-- Choisir une session --</option>
                <optgroup
                  v-for="filiere in sessionsGroupees"
                  :key="filiere.nom"
                  :label="filiere.nom"
                >
                  <option v-for="s in filiere.sessions" :key="s.id" :value="s.id">
                    {{ s.diplome }} — {{ s.annee }}
                  </option>
                </optgroup>
              </select>
            </div>

            <div class="mb-4">
              <label class="form-label fw-semibold">Fichier CSV *</label>
              <input
                ref="fileInput"
                type="file"
                accept=".csv,.txt"
                class="form-control"
                @change="onFileChange"
              />
              <div class="form-text">Formats acceptés : .csv — Séparateur : virgule ou point-virgule</div>
            </div>

            <!-- Aperçu du fichier -->
            <div v-if="preview.length > 0" class="mb-4">
              <label class="form-label fw-semibold small text-muted">Aperçu (5 premières lignes)</label>
              <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                <table class="table table-sm table-bordered mb-0 small">
                  <thead class="table-light">
                    <tr><th v-for="h in preview[0]" :key="h">{{ h }}</th></tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, i) in preview.slice(1)" :key="i">
                      <td v-for="(cell, j) in row" :key="j">{{ cell }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <button
              class="btn btn-success w-100 fw-bold"
              @click="importer"
              :disabled="!selectedSession || !csvFile || uploading"
            >
              <span v-if="uploading">
                <span class="spinner-border spinner-border-sm me-2"></span> Import en cours...
              </span>
              <span v-else>
                <i class="fas fa-file-import me-2"></i> Lancer l'import
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- Format attendu -->
      <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white fw-bold">
            <i class="fas fa-info-circle text-info me-2"></i> Format du fichier CSV
          </div>
          <div class="card-body">
            <p class="text-muted small mb-3">La première ligne doit contenir les en-têtes suivants :</p>

            <div class="table-responsive mb-4">
              <table class="table table-sm table-bordered small">
                <thead class="table-light">
                  <tr>
                    <th>Colonne</th>
                    <th>Obligatoire</th>
                    <th>Exemple</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="col in colonnes" :key="col.nom">
                    <td><code>{{ col.nom }}</code></td>
                    <td>
                      <span class="badge" :class="col.requis ? 'bg-danger' : 'bg-secondary'">
                        {{ col.requis ? 'Oui' : 'Non' }}
                      </span>
                    </td>
                    <td class="text-muted">{{ col.exemple }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <p class="fw-semibold small mb-2">Exemple complet :</p>
            <pre class="bg-light rounded p-3 small mb-0" style="font-size: 0.75rem;">nom,prenom,email,dateNaissance,numeroEtudiant,moyenne,estAdmis
DUPONT,Jean,jean@ecole.fr,2001-03-15,ETU-2025-00001,14.5,1
MARTIN,Marie,marie@ecole.fr,2000-07-22,,12,1
DURAND,Paul,paul@ecole.fr,,,8.5,0
BERNARD,Emma,emma@ecole.fr,,,,</pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { sessionService } from '@/services/index.js'
import api from '@/services/api.js'

const sessions        = ref([])
const selectedSession = ref('')
const csvFile         = ref(null)
const preview         = ref([])
const uploading       = ref(false)
const result          = ref(null)
const errorMsg        = ref('')
const fileInput       = ref(null)

const colonnes = [
  { nom: 'nom',            requis: true,  exemple: 'DUPONT' },
  { nom: 'prenom',         requis: true,  exemple: 'Jean' },
  { nom: 'email',          requis: true,  exemple: 'jean.dupont@ecole.fr' },
  { nom: 'dateNaissance',  requis: false, exemple: '2001-03-15' },
  { nom: 'numeroEtudiant', requis: false, exemple: 'ETU-2025-00001 (auto si vide)' },
  { nom: 'moyenne',        requis: false, exemple: '14.5' },
  { nom: 'estAdmis',       requis: false, exemple: '1 (oui) ou 0 (non)' },
]

// Grouper les sessions par filière pour l'affichage
const sessionsGroupees = computed(() => {
  const map = {}
  for (const s of sessions.value) {
    const filiere = s.diplome?.filiere?.nom || 'Autres'
    if (!map[filiere]) map[filiere] = { nom: filiere, sessions: [] }
    map[filiere].sessions.push({
      id:     s.id,
      diplome: s.diplome?.intitule,
      annee:  s.annee,
    })
  }
  return Object.values(map)
})

function onFileChange(event) {
  csvFile.value = event.target.files[0] || null
  preview.value = []
  result.value  = null
  errorMsg.value = ''

  if (!csvFile.value) return

  // Générer un aperçu côté client
  const reader = new FileReader()
  reader.onload = (e) => {
    const text    = e.target.result
    const sep     = text.indexOf(';') > text.indexOf(',') ? ';' : ','
    const lines   = text.trim().split('\n').slice(0, 6)
    preview.value = lines.map(l => l.split(sep).map(c => c.trim().replace(/^"|"$/g, '')))
  }
  reader.readAsText(csvFile.value)
}

async function importer() {
  result.value  = null
  errorMsg.value = ''
  uploading.value = true

  try {
    const formData = new FormData()
    formData.append('csv', csvFile.value)

    const res = await api.post(`/import/csv/${selectedSession.value}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    result.value = res.data

    // Réinitialiser le formulaire après succès
    if (res.data.created > 0) {
      csvFile.value      = null
      preview.value      = []
      if (fileInput.value) fileInput.value.value = ''
    }
  } catch (err) {
    errorMsg.value = err.response?.data?.error || 'Erreur lors de l\'import.'
  } finally {
    uploading.value = false
  }
}

onMounted(async () => {
  const res = await sessionService.getAll()
  sessions.value = res.data
})
</script>
