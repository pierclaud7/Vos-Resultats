import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'

// ── Public
import HomePublic   from '@/views/public/HomePublic.vue'
import Resultats    from '@/views/public/Resultats.vue'
import Statistiques from '@/views/public/Statistiques.vue'

// ── Auth
import Login        from '@/views/Login.vue'

// ── Admin
import Dashboard    from '@/views/admin/Dashboard.vue'
import Filieres     from '@/views/admin/Filieres.vue'
import Diplomes     from '@/views/admin/Diplomes.vue'
import Sessions     from '@/views/admin/Sessions.vue'
import SessionDetail from '@/views/admin/SessionDetail.vue'
import Etudiants    from '@/views/admin/Etudiants.vue'
import ImportCSV    from '@/views/admin/ImportCSV.vue'

// ── Erreurs
import NotFound     from '@/views/errors/NotFound.vue'

const routes = [
  // Public
  { path: '/',             name: 'home',          component: HomePublic,    meta: { requiresAuth: false, layout: 'public' } },
  { path: '/resultats',    name: 'resultats',     component: Resultats,     meta: { requiresAuth: false, layout: 'public' } },
  { path: '/statistiques', name: 'statistiques',  component: Statistiques,  meta: { requiresAuth: false, layout: 'public' } },

  // Auth
  { path: '/login',        name: 'login',         component: Login,         meta: { requiresAuth: false, layout: 'blank'  } },

  // Admin
  { path: '/admin',                      name: 'dashboard',     component: Dashboard,     meta: { requiresAuth: true, layout: 'admin' } },
  { path: '/admin/filieres',             name: 'filieres',      component: Filieres,      meta: { requiresAuth: true, layout: 'admin' } },
  { path: '/admin/diplomes',             name: 'diplomes',      component: Diplomes,      meta: { requiresAuth: true, layout: 'admin' } },
  { path: '/admin/sessions',             name: 'sessions',      component: Sessions,      meta: { requiresAuth: true, layout: 'admin' } },
  { path: '/admin/sessions/:id',         name: 'sessionDetail', component: SessionDetail, meta: { requiresAuth: true, layout: 'admin' } },
  { path: '/admin/etudiants',            name: 'etudiants',     component: Etudiants,     meta: { requiresAuth: true, layout: 'admin' } },
  { path: '/admin/import-csv',           name: 'importCsv',     component: ImportCSV,     meta: { requiresAuth: true, layout: 'admin' } },

  // 404
  { path: '/:pathMatch(.*)*', name: 'notFound', component: NotFound, meta: { requiresAuth: false, layout: 'blank' } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach(async (to) => {
  if (!to.meta.requiresAuth) return true
  const auth = useAuthStore()
  if (!auth.isAuthenticated) await auth.fetchMe()
  if (!auth.isAuthenticated) return { name: 'login', query: { redirect: to.fullPath } }
  return true
})

export default router