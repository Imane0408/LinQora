import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import useAuthStore from '@/store/authStore'

// Layouts
import AppLayout from '@/components/layout/AppLayout'
import AuthLayout from '@/components/layout/AuthLayout'

// Pages publiques
import LoginPage        from '@/pages/auth/LoginPage'
import RegisterPage     from '@/pages/auth/RegisterPage'
import EventPublicPage  from '@/pages/public/EventPublicPage'

// Pages dashboard
import DashboardPage        from '@/pages/dashboard/DashboardPage'
import EvenementsPage       from '@/pages/evenements/EvenementsPage'
import EvenementDetailPage  from '@/pages/evenements/EvenementDetailPage'
import EvenementCreatePage  from '@/pages/evenements/EvenementCreatePage'
import EvenementEditPage    from '@/pages/evenements/EvenementEditPage'
import InscriptionsPage     from '@/pages/inscriptions/InscriptionsPage'
import AteliersPage         from '@/pages/ateliers/AteliersPage'
import SpeakersPage         from '@/pages/speakers/SpeakersPage'
import PointagePage         from '@/pages/pointage/PointagePage'
import NetworkingPage       from '@/pages/networking/NetworkingPage'
import NotificationsPage    from '@/pages/notifications/NotificationsPage'
import EntreprisesPage      from '@/pages/admin/EntreprisesPage'
import UtilisateursPage     from '@/pages/admin/UtilisateursPage'
import ProfilPage           from '@/pages/profil/ProfilPage'

function PrivateRoute({ children, roles }) {
  const { token, user } = useAuthStore()
  if (!token) return <Navigate to="/login" replace />
  if (roles && !roles.includes(user?.role)) return <Navigate to="/dashboard" replace />
  return children
}

export default function App() {
  return (
    <BrowserRouter>
      <Toaster
        position="top-right"
        toastOptions={{
          style: {
            background: '#181d2e',
            color: '#f1f5f9',
            border: '1px solid rgba(255,255,255,0.1)',
            fontFamily: 'DM Sans, sans-serif',
            fontSize: '14px',
          },
          success: { iconTheme: { primary: '#22c55e', secondary: '#181d2e' } },
          error:   { iconTheme: { primary: '#ef4444', secondary: '#181d2e' } },
        }}
      />
      <Routes>
        {/* Auth */}
        <Route element={<AuthLayout />}>
          <Route path="/login"    element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />
        </Route>

        {/* Public */}
        <Route path="/e/:slug" element={<EventPublicPage />} />

        {/* App */}
        <Route element={<PrivateRoute><AppLayout /></PrivateRoute>}>
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="/dashboard"    element={<DashboardPage />} />
          <Route path="/evenements"   element={<EvenementsPage />} />
          <Route path="/evenements/nouveau" element={
            <PrivateRoute roles={['super_admin','admin_entreprise','gestionnaire']}>
              <EvenementCreatePage />
            </PrivateRoute>
          } />
          <Route path="/evenements/:id"      element={<EvenementDetailPage />} />
          <Route path="/evenements/:id/edit" element={
            <PrivateRoute roles={['super_admin','admin_entreprise','gestionnaire']}>
              <EvenementEditPage />
            </PrivateRoute>
          } />
          <Route path="/evenements/:id/inscriptions" element={<InscriptionsPage />} />
          <Route path="/evenements/:id/ateliers"     element={<AteliersPage />} />
          <Route path="/pointage"     element={<PointagePage />} />
          <Route path="/networking"   element={<NetworkingPage />} />
          <Route path="/notifications" element={<NotificationsPage />} />
          <Route path="/speakers"     element={<SpeakersPage />} />
          <Route path="/entreprises"  element={
            <PrivateRoute roles={['super_admin']}>
              <EntreprisesPage />
            </PrivateRoute>
          } />
          <Route path="/utilisateurs" element={
            <PrivateRoute roles={['super_admin','admin_entreprise']}>
              <UtilisateursPage />
            </PrivateRoute>
          } />
          <Route path="/profil" element={<ProfilPage />} />
        </Route>

        <Route path="*" element={<Navigate to="/dashboard" replace />} />
      </Routes>
    </BrowserRouter>
  )
}
