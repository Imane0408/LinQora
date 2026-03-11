import { Outlet, Navigate } from 'react-router-dom'
import useAuthStore from '@/store/authStore'
import styles from './AuthLayout.module.css'

export default function AuthLayout() {
  const { token } = useAuthStore()
  if (token) return <Navigate to="/dashboard" replace />

  return (
    <div className={styles.layout}>
      <div className={styles.bg}>
        <div className={styles.orb1} />
        <div className={styles.orb2} />
        <div className={styles.grid} />
      </div>
      <div className={styles.card}>
        <div className={styles.brand}>
          <div className={styles.brandIcon}>✦</div>
          <span className={styles.brandName}>LinQora</span>
        </div>
        <Outlet />
      </div>
    </div>
  )
}
