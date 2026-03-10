import { create } from 'zustand'
import api from '@/lib/api'

const useAuthStore = create((set, get) => ({
  user: JSON.parse(localStorage.getItem('user') || 'null'),
  token: localStorage.getItem('token') || null,
  loading: false,

  login: async (email, password) => {
    set({ loading: true })
    const res = await api.post('/auth/login', { email, password })
    const { access_token, utilisateur } = res.data
    localStorage.setItem('token', access_token)
    localStorage.setItem('user', JSON.stringify(utilisateur))
    set({ token: access_token, user: utilisateur, loading: false })
    return utilisateur
  },

  logout: async () => {
    try { await api.post('/auth/logout') } catch {}
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    set({ token: null, user: null })
  },

  refreshMe: async () => {
    const res = await api.get('/auth/me')
    const u = res.data
    localStorage.setItem('user', JSON.stringify(u))
    set({ user: u })
    return u
  },

  isRole: (role) => get().user?.role === role,
  isSuperAdmin:    () => get().user?.role === 'super_admin',
  isAdminEntreprise: () => get().user?.role === 'admin_entreprise',
  isGestionnaire:  () => get().user?.role === 'gestionnaire',
  isParticipant:   () => get().user?.role === 'participant',
  isOperateurScan: () => get().user?.role === 'operateur_scan',
  canManage: () => ['super_admin','admin_entreprise','gestionnaire'].includes(get().user?.role),
}))

export default useAuthStore
