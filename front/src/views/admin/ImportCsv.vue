<template>
  <div class="page">
    <div class="topbar">
      <h1 class="page-title">Import CSV</h1>
      <router-link to="/admin/etudiants" class="btn-secondary" style="text-decoration:none;display:flex;align-items:center;gap:6px">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Retour
      </router-link>
    </div>

    <div class="grid-2">
      <!-- Format attendu -->
      <div class="info-card">
        <div class="info-card-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Format attendu
        </div>
        <p class="info-text">Fichier <code>.csv</code> avec séparateur <code>;</code> ou <code>,</code><br>La première ligne doit être l'en-tête.</p>
        <pre class="code-block">nom;prenom;email;date_naissance
DUPONT;Marie;marie@exemple.com;2001-03-15
MARTIN;Thomas;thomas@exemple.com;2000-07-22</pre>
        <p class="info-text" style="margin-top:12px">
          <strong>Obligatoires :</strong> nom, prenom, email<br>
          <strong>Optionnel :</strong> date_naissance (YYYY-MM-DD)<br>
          Le <strong>numéro étudiant</strong> est généré automatiquement.
        </p>
        <button class="download-btn" @click="downloadTemplate">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Télécharger le modèle CSV
        </button>
      </div>

      <!-- Formulaire -->
      <div class="form-card">
        <div class="info-card-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Importer un fichier
        </div>

        <div class="form-field">
          <label class="field-label">Session d'examen *</label>
          <select v-model="sessionId" class="select-field" :disabled="importing">
            <option value="">— Choisir une session —</option>
            <option v-for="s in sessions" :key="s.id" :value="s.id">{{ s.diplome?.intitule }} — {{ s.annee }}</option>
          </select>
        </div>

        <div class="form-field">
          <label class="field-label">Fichier CSV *</label>
          <div class="file-zone" :class="{ 'has-file': selectedFile }" @click="$refs.fileInput.click()">
            <input ref="fileInput" type="file" accept=".csv" style="display:none" @change="onFileChange" :disabled="importing" />
            <div v-if="!selectedFile">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px;color:#94A3B8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <div style="font-size:13.5px;color:#64748B;font-weight:600">Cliquer pour sélectionner</div>
              <div style="font-size:12px;color:#94A3B8;margin-top:4px">Fichier .csv uniquement</div>
            </div>
            <div v-else style="display:flex;align-items:center;gap:10px">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
              <div>
                <div style="font-size:14px;font-weight:600;color:#059669">{{ selectedFile.name }}</div>
                <div style="font-size:12px;color:#94A3B8">{{ fileSize }}</div>
              </div>
            </div>
          </div>
        </div>

        <button class="import-btn" @click="doImport" :disabled="!sessionId || !selectedFile || importing">
          <span v-if="importing" class="spinner-xs white"></span>
          <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          {{ importing ? 'Import en cours...' : 'Lancer l\'import' }}
        </button>

        <!-- Résultat import -->
        <div v-if="result" style="margin-top:20px">
          <div class="result-success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            {{ result.imported }} étudiant(s) importé(s) avec succès
          </div>
          <div v-if="result.errors?.length" class="result-warnings">
            <div style="font-weight:600;margin-bottom:6px">{{ result.errors.length }} ligne(s) ignorée(s) :</div>
            <ul style="margin:0;padding-left:16px">
              <li v-for="e in result.errors" :key="e" style="font-size:12px;margin-bottom:4px">{{ e }}</li>
            </ul>
          </div>
          <router-link v-if="result.imported > 0" to="/admin/etudiants" class="goto-btn">
            Voir les étudiants importés →
          </router-link>
        </div>

        <div v-if="error" class="result-error">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import '@/assets/admin.css'
import { ref, computed, onMounted } from 'vue'
import { sessionService, etudiantService } from '@/services/index.js'

const sessions = ref([]); const sessionId = ref(''); const selectedFile = ref(null)
const importing = ref(false); const result = ref(null); const error = ref(''); const fileInput = ref(null)

const fileSize = computed(() => selectedFile.value ? `${(selectedFile.value.size / 1024).toFixed(1)} KB` : '')

onMounted(async () => { sessions.value = (await sessionService.getAll()).data })

function onFileChange(e) { selectedFile.value = e.target.files[0] || null; result.value = null; error.value = '' }

async function doImport() {
  importing.value = true; result.value = null; error.value = ''
  try {
    const fd = new FormData()
    fd.append('csv', selectedFile.value)
    fd.append('session_id', sessionId.value)
    result.value = (await etudiantService.importCsv(fd)).data
    selectedFile.value = null
    if (fileInput.value) fileInput.value.value = ''
  } catch (e) { error.value = e.response?.data?.error || 'Erreur lors de l\'import.' }
  finally { importing.value = false }
}

function downloadTemplate() {
  const csv = 'nom;prenom;email;date_naissance\nDUPONT;Marie;marie.dupont@exemple.com;2001-03-15\nMARTIN;Thomas;thomas.martin@exemple.com;2000-07-22'
  const a = document.createElement('a')
  a.href = URL.createObjectURL(new Blob([csv], { type: 'text/csv' }))
  a.download = 'import_exemple.csv'
  a.click()
}
</script>

<style scoped>
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

.info-card, .form-card { background: white; border: 1px solid #E2E8F0; border-radius: 12px; padding: 24px; }

.info-card-title { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #0F172A; margin-bottom: 16px; }

.info-text { font-size: 13.5px; color: #64748B; line-height: 1.6; margin: 0 0 12px; }

.code-block { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px; padding: 14px; font-size: 12px; color: #374151; line-height: 1.7; overflow-x: auto; }

.download-btn { display: flex; align-items: center; gap: 8px; padding: 9px 16px; background: #F0FDF4; color: #059669; border: 1px solid #BBF7D0; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; margin-top: 16px; font-family: 'DM Sans', sans-serif; }
.download-btn:hover { background: #DCFCE7; }

.form-field { margin-bottom: 18px; }
.field-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.select-field { width: 100%; padding: 10px 14px; border: 1.5px solid #E2E8F0; border-radius: 8px; font-size: 14px; color: #0F172A; outline: none; font-family: 'DM Sans', sans-serif; }
.select-field:focus { border-color: #2563EB; }

.file-zone { border: 2px dashed #E2E8F0; border-radius: 10px; padding: 28px; text-align: center; cursor: pointer; transition: all 0.15s; }
.file-zone:hover { border-color: #2563EB; background: #F8FAFF; }
.file-zone.has-file { border-color: #059669; background: #F0FDF4; border-style: solid; text-align: left; padding: 16px 20px; }

.import-btn { width: 100%; padding: 12px; background: #2563EB; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s; font-family: 'DM Sans', sans-serif; margin-top: 4px; }
.import-btn:hover:not(:disabled) { background: #1D4ED8; }
.import-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.result-success { display: flex; align-items: center; gap: 8px; background: #F0FDF4; color: #059669; border: 1px solid #BBF7D0; border-radius: 8px; padding: 12px 16px; font-size: 13.5px; font-weight: 600; margin-bottom: 12px; }
.result-warnings { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #92400E; margin-bottom: 12px; }
.result-error { display: flex; align-items: center; gap: 8px; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 8px; padding: 12px 16px; font-size: 13.5px; margin-top: 12px; }
.goto-btn { display: inline-flex; align-items: center; color: #2563EB; font-size: 13.5px; font-weight: 600; text-decoration: none; }
.goto-btn:hover { text-decoration: underline; }

.spinner-xs { width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
.spinner-xs.white { border-color: rgba(255,255,255,0.3); border-top-color: white; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
