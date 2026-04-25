import api from './api.js'

// ── AUTH ──────────────────────────────────────────────────────
export const authService = {
  login:  (email, password) => api.post('/auth/login', { email, password }),
  logout: ()                => api.post('/auth/logout'),
  me:     ()                => api.get('/auth/me'),
}

// ── FILIÈRES ──────────────────────────────────────────────────
export const filiereService = {
  getAll:  ()         => api.get('/filieres'),
  getById: (id)       => api.get(`/filieres/${id}`),
  create:  (data)     => api.post('/filieres', data),
  update:  (id, data) => api.put(`/filieres/${id}`, data),
  delete:  (id)       => api.delete(`/filieres/${id}`),
}

// ── DIPLÔMES ──────────────────────────────────────────────────
export const diplomeService = {
  getAll:  ()         => api.get('/diplomes'),
  getById: (id)       => api.get(`/diplomes/${id}`),
  create:  (data)     => api.post('/diplomes', {
    intitule:   data.intitule,
    filiere_id: data.filiereId,
  }),
  update:  (id, data) => api.put(`/diplomes/${id}`, {
    intitule:   data.intitule,
    filiere_id: data.filiereId,
  }),
  delete:  (id)       => api.delete(`/diplomes/${id}`),
}

// ── SESSIONS ──────────────────────────────────────────────────
export const sessionService = {
  getAll:  ()         => api.get('/sessions'),
  getById: (id)       => api.get(`/sessions/${id}`),
  create:  (data)     => api.post('/sessions', {
    annee:      data.annee,
    diplome_id: data.diplomeId,
  }),
  update:  (id, data) => api.put(`/sessions/${id}`, {
    annee:      data.annee,
    diplome_id: data.diplomeId,
  }),
  delete:  (id)       => api.delete(`/sessions/${id}`),
  valider: (id)       => api.post(`/sessions/${id}/valider`),
  publier: (id)       => api.post(`/sessions/${id}/publier`),
}

// ── ÉTUDIANTS ─────────────────────────────────────────────────
export const etudiantService = {
  getAll:  (sessionId = null) => api.get('/etudiants', {
    params: sessionId ? { session_id: sessionId } : {},
  }),
  getById: (id)       => api.get(`/etudiants/${id}`),
  create:  (data)     => api.post('/etudiants', {
    nom:        data.nom,
    prenom:     data.prenom,
    email:      data.email,
    session_id: data.sessionId ?? data.session_id,
    moyenne:    data.moyenne ?? null,
    resultat:   data.resultat ?? null,
  }),
  update:  (id, data) => api.put(`/etudiants/${id}`, {
    nom:        data.nom,
    prenom:     data.prenom,
    email:      data.email,
    session_id: data.sessionId ?? data.session_id,
    moyenne:    data.moyenne ?? null,
    resultat:   data.resultat ?? null,
  }),
  delete:    (id)       => api.delete(`/etudiants/${id}`),
  importCsv: (formData) => api.post('/etudiants/import/csv', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
}

// ── PUBLIC ────────────────────────────────────────────────────
export const publicService = {
  getFilieres:        ()                  => api.get('/public/filieres'),
  getDiplomesFiliere: (filiereId)         => api.get(`/public/filieres/${filiereId}/diplomes`),
  getResultat:        (sessionId, params) => api.get(`/public/sessions/${sessionId}/resultat`, { params }),
  getStatistiques:    (annee = null)      => api.get('/public/statistiques', {
    params: annee ? { annee } : {},
  }),
}