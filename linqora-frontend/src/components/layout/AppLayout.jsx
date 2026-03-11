import { useState, useEffect } from 'react'
import { Outlet, NavLink, useNavigate, useLocation } from 'react-router-dom'
import { motion, AnimatePresence } from 'framer-motion'
import useAuthStore from '@/store/authStore'
import api from '@/lib/api'
import {
  LayoutDashboard, Calendar, Users, Mic2, QrCode, Network,
  Bell, Building2, UserCog, LogOut, Menu, X, ChevronRight,
  Sparkles, User
} from 'lucide-react'
import styles from './AppLayout.module.css'

export default function AppLayout() {
  const { user, logout, canManage, isSuperAdmin, isOperateurScan } = useAuthStore()
  const navigate = useNavigate()
  const location = useLocation()
  const [sidebarOpen, setSidebarOpen] = useState(true)
  const [notifCount, setNotifCount] = useState(0)
  const [mobileOpen, setMobileOpen] = useState(false)

  useEffect(() => {
    api.get('/notifications/non-lues').then(r => setNotifCount(r.data.count)).catch(() => {})
  }, [location.pathname])

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  const navItems = [
    { to: '/dashboard',   icon: LayoutDashboard, label: 'Dashboard',      always: true },
    { to: '/evenements',  icon: Calendar,         label: 'Événements',     always: true },
    { to: '/pointage',    icon: QrCode,           label: 'Scan QR',        roles: ['super_admin','admin_entreprise','gestionnaire','operateur_scan'] },
    { to: '/networking',  icon: Network,          label: 'Networking',     roles: ['participant'] },
    { to: '/speakers',    icon: Mic2,             label: 'Speakers',       roles: ['super_admin','admin_entreprise','gestionnaire'] },
    { to: '/utilisateurs',icon: UserCog,          label: 'Utilisateurs',   roles: ['super_admin','admin_entreprise'] },
    { to: '/entreprises', icon: Building2,        label: 'Entreprises',    roles: ['super_admin'] },
  ]

  const visibleNav = navItems.filter(item =>
    item.always || (item.roles && item.roles.includes(user?.role))
  )

  const Sidebar = () => (
    <aside className={`${styles.sidebar} ${!sidebarOpen ? styles.collapsed : ''}`}>
      {/* Logo */}
      <div className={styles.logo}>
        <div className={styles.logoIcon}>
          <Sparkles size={18} />
        </div>
        <AnimatePresence>
          {sidebarOpen && (
            <motion.span
              initial={{ opacity:0, width:0 }}
              animate={{ opacity:1, width:'auto' }}
              exit={{ opacity:0, width:0 }}
              className={styles.logoText}
            >LinQora</motion.span>
          )}
        </AnimatePresence>
      </div>

      {/* Nav */}
      <nav className={styles.nav}>
        {visibleNav.map(({ to, icon: Icon, label }) => (
          <NavLink key={to} to={to} className={({ isActive }) =>
            `${styles.navItem} ${isActive ? styles.active : ''}`
          }>
            <Icon size={18} className={styles.navIcon} />
            <AnimatePresence>
              {sidebarOpen && (
                <motion.span
                  initial={{ opacity:0 }}
                  animate={{ opacity:1 }}
                  exit={{ opacity:0 }}
                  className={styles.navLabel}
                >{label}</motion.span>
              )}
            </AnimatePresence>
            {sidebarOpen && <ChevronRight size={14} className={styles.navArrow} />}
          </NavLink>
        ))}
      </nav>

      {/* Bottom */}
      <div className={styles.sidebarBottom}>
        <NavLink to="/notifications" className={styles.navItem}>
          <div className={styles.notifWrapper}>
            <Bell size={18} />
            {notifCount > 0 && <span className={styles.badge}>{notifCount}</span>}
          </div>
          {sidebarOpen && <motion.span initial={{ opacity:0 }} animate={{ opacity:1 }} className={styles.navLabel}>Notifications</motion.span>}
        </NavLink>
        <NavLink to="/profil" className={styles.navItem}>
          <User size={18} className={styles.navIcon} />
          {sidebarOpen && <motion.span initial={{ opacity:0 }} animate={{ opacity:1 }} className={styles.navLabel}>Profil</motion.span>}
        </NavLink>
        <button className={styles.navItem} onClick={handleLogout}>
          <LogOut size={18} className={styles.navIcon} />
          {sidebarOpen && <motion.span initial={{ opacity:0 }} animate={{ opacity:1 }} className={styles.navLabel}>Déconnexion</motion.span>}
        </button>
      </div>

      {/* Toggle */}
      <button className={styles.toggleBtn} onClick={() => setSidebarOpen(p => !p)}>
        {sidebarOpen ? <X size={14} /> : <Menu size={14} />}
      </button>
    </aside>
  )

  return (
    <div className={styles.layout}>
      {/* Mobile overlay */}
      <AnimatePresence>
        {mobileOpen && (
          <motion.div
            className={styles.mobileOverlay}
            initial={{ opacity:0 }} animate={{ opacity:1 }} exit={{ opacity:0 }}
            onClick={() => setMobileOpen(false)}
          />
        )}
      </AnimatePresence>

      {/* Mobile sidebar */}
      <AnimatePresence>
        {mobileOpen && (
          <motion.div
            className={styles.mobileSidebar}
            initial={{ x:-280 }} animate={{ x:0 }} exit={{ x:-280 }}
            transition={{ type:'spring', stiffness:300, damping:30 }}
          >
            <Sidebar />
          </motion.div>
        )}
      </AnimatePresence>

      {/* Desktop sidebar */}
      <div className={styles.desktopSidebar}>
        <Sidebar />
      </div>

      {/* Main */}
      <main className={styles.main}>
        {/* Topbar */}
        <header className={styles.topbar}>
          <button className={styles.mobileMenuBtn} onClick={() => setMobileOpen(true)}>
            <Menu size={20} />
          </button>
          <div className={styles.topbarRight}>
            <div className={styles.userChip}>
              <div className={styles.userAvatar}>
                {user?.prenom?.[0]}{user?.nom?.[0]}
              </div>
              <div className={styles.userInfo}>
                <span className={styles.userName}>{user?.prenom} {user?.nom}</span>
                <span className={styles.userRole}>{user?.role?.replace('_',' ')}</span>
              </div>
            </div>
          </div>
        </header>

        {/* Page content */}
        <div className={styles.content}>
          <motion.div
            key={location.pathname}
            initial={{ opacity:0, y:12 }}
            animate={{ opacity:1, y:0 }}
            transition={{ duration:0.25 }}
          >
            <Outlet />
          </motion.div>
        </div>
      </main>
    </div>
  )
}
