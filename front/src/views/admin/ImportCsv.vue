<template>
  <div class="page">
    <div class="topbar">
      <h1 class="page-title">Import CSV</h1>
      <router-link to="/admin/etudiants" class="btn-secondary" style="text-decoration:none;display:flex;align-items:center;gap:6px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Retour
      </router-link>
    </div>

    <div class="csv-grid">

      <!-- Format attendu -->
      <div class="csv-card">
        <div class="csv-card-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Format attendu
        </div>
        <p class="csv-text">Fichier <code class="csv-code">.csv</code> avec séparateur <code class="csv-code">;</code> ou <code class="csv-code">,</code><br>La première ligne doit être l'en-tête.</p>
        <pre class="csv-pre">nom;prenom;email;date_naissance
DUPONT;Marie;marie@exemple.com;2001-03-15
MARTIN;Thomas;thomas@exemple.com;2000-07-22</pre>
        <p class="csv-text" style="margin-top:12px">
          <strong style="color:#94A3B8">Obligatoires :</strong> nom, prenom, email<br>
          <strong style="color:#94A3B8">Optionnel :</strong> date_naissance (YYYY-MM-DD)<br>
          Le <strong style="color:#94A3B8">numéro étudiant</strong> est généré automatiquement.
        </p>
        <button class="csv-dl-btn" @click="downloadTemplate">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Télécharger le modèle CSV
        </button>
      </div>

      <!-- Formulaire import -->
      <div class="csv-card">
        <div class="csv-card-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Importer un fichier
        </div>

        <div class="csv-field">
          <label>Session d'examen *</label>
          <select v-model="sessionId" :disabled="importing">
            <option value="">— Choisir une session —</option>
            <option v-for="s in sessions" :key="s.id" :value="s.id">
              {{ s.diplome?.intitule }} — {{ s.annee }}
            </option>
          </select>
        </div>

        <div class="csv-field">
          <label>Fichier CSV *</label>
          <div class="csv-dropzone" :class="{ 'has-file': selectedFile }" @click="$refs.fileInput.click()">
            <input ref="fileInput" type="file" accept=".csv" style="display:none" @change="onFileChange" :disabled="importing" />
            <div v-if="!selectedFile" style="text-align:center">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px;color:#334155">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
              <div style="font-size:13.5px;color:#475569;font-weight:600">Cliquer pour sélectionner</div>
              <div style="font-size:12px;color:#334155;margin-top:4px">Fichier .csv uniquement</div>
            </div>
            <div v-else style="display:flex;align-items:center;gap:10px">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              <div>
                <div style="font-size:14px;font-weight:600;color:#10B981">{{ selectedFile.name }}</div>
                <div style="font-size:12px;color:#475569">{{ fileSize }}</div>
              </div>
            </div>
          </div>
        </div>

        <button class="csv-import-btn" @click="doImport" :disabled="!sessionId || !selectedFile || importing">
          <span v-if="importing" class="csv-spinner"></span>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
          </svg>
          {{ importing ? 'Import en cours...' : 'Lancer l\'import' }}
        </button>

        <!-- Résultat -->
        <div v-if="result" style="margin-top:20px">
          <div class="csv-success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            {{ result.imported }} étudiant(s) importé(s) avec succès
          </div>
          <div v-if="result.errors?.length" class="csv-warnings">
            <div style="font-weight:600;margin-bottom:6px">{{ result.errors.length }} ligne(s) ignorée(s) :</div>
            <ul style="margin:0;padding-left:16px">
              <li v-for="e in result.errors" :key="e" style="font-size:12px;margin-bottom:4px">{{ e }}</li>
            </ul>
          </div>
          <router-link v-if="result.imported > 0" to="/admin/etudiants" class="csv-goto">
            Voir les étudiants importés →
          </router-link>
        </div>

        <!-- Erreur -->
        <div v-if="error" class="csv-error">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          {{ error }}
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { sessionService, etudiantService } from '@/services/index.js'

const sessions    = ref([])
const sessionId   = ref('')
const selectedFile = ref(null)
const importing   = ref(false)
const result      = ref(null)
const error       = ref('')
const fileInput   = ref(null)

const fileSize = computed(() =>
  selectedFile.value ? `${(selectedFile.value.size / 1024).toFixed(1)} KB` : ''
)

onMounted(async () => {
  sessions.value = (await sessionService.getAll()).data
})

function onFileChange(e) {
  selectedFile.value = e.target.files[0] || null
  result.value = null
  error.value  = ''
}

async function doImport() {
  importing.value = true
  result.value    = null
  error.value     = ''
  try {
    const fd = new FormData()
    fd.append('csv', selectedFile.value)
    fd.append('session_id', sessionId.value)
    result.value = (await etudiantService.importCsv(fd)).data
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''
  } catch (e) {
    error.value = e.response?.data?.error || 'Erreur lors de l\'import.'
  } finally {
    importing.value = false
  }
}

function downloadTemplate() {
  const csv = 'nom;prenom;email;date_naissance\nDUPONT;Marie;marie.dupont@exemple.com;2001-03-15\nMARTIN;Thomas;thomas.martin@exemple.com;2000-07-22'
  const a   = document.createElement('a')
  a.href    = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }))
  a.download = 'import_exemple.csv'
  a.click()
}
</script>

<style scoped>
.csv-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.csv-card {
  background: #1E293B;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 12px;
  padding: 24px;
}

.csv-card-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 700;
  color: white;
  margin-bottom: 16px;
}

.csv-text {
  font-size: 13.5px;
  color: #475569;
  line-height: 1.6;
  margin: 0 0 12px;
}

.csv-code {
  background: rgba(255,255,255,0.06);
  color: #60A5FA;
  padding: 1px 6px;
  border-radius: 4px;
  font-size: 13px;
}

.csv-pre {
  background: #0F172A;
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 8px;
  padding: 14px;
  font-size: 12px;
  color: #64748B;
  line-height: 1.7;
  overflow-x: auto;
}

.csv-dl-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 16px;
  background: rgba(5,150,105,0.1);
  color: #10B981;
  border: 1px solid rgba(5,150,105,0.2);
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
  margin-top: 16px;
  font-family: 'DM Sans', sans-serif;
}
.csv-dl-btn:hover { background: rgba(5,150,105,0.15); }

.csv-field {
  margin-bottom: 18px;
}
.csv-field label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
  margin-bottom: 6px;
}
.csv-field select {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  font-size: 14px;
  color: white;
  background: #0F172A;
  outline: none;
  font-family: 'DM Sans', sans-serif;
}
.csv-field select:focus { border-color: #2563EB; }
.csv-field select option { background: #1E293B; }

.csv-dropzone {
  border: 2px dashed rgba(255,255,255,0.08);
  border-radius: 10px;
  padding: 28px;
  text-align: center;
  cursor: pointer;
  transition: all 0.15s;
}
.csv-dropzone:hover {
  border-color: rgba(37,99,235,0.3);
  background: rgba(37,99,235,0.03);
}
.csv-dropzone.has-file {
  border-color: rgba(5,150,105,0.3);
  background: rgba(5,150,105,0.05);
  border-style: solid;
  text-align: left;
  padding: 16px 20px;
}

.csv-import-btn {
  width: 100%;
  padding: 12px;
  background: #2563EB;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.15s;
  font-family: 'DM Sans', sans-serif;
  margin-top: 4px;
}
.csv-import-btn:hover:not(:disabled) { background: #1D4ED8; }
.csv-import-btn:disabled { opacity: 0.4; cursor: not-allowed; }

.csv-success {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(5,150,105,0.1);
  color: #10B981;
  border: 1px solid rgba(5,150,105,0.2);
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 13.5px;
  font-weight: 600;
  margin-bottom: 12px;
}

.csv-warnings {
  background: rgba(217,119,6,0.1);
  border: 1px solid rgba(217,119,6,0.2);
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 13px;
  color: #F59E0B;
  margin-bottom: 12px;
}

.csv-error {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(220,38,38,0.1);
  color: #EF4444;
  border: 1px solid rgba(220,38,38,0.2);
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 13.5px;
  margin-top: 12px;
}

.csv-goto {
  display: inline-flex;
  align-items: center;
  color: #60A5FA;
  font-size: 13.5px;
  font-weight: 600;
  text-decoration: none;
}
.csv-goto:hover { text-decoration: underline; }

.csv-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.2);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>