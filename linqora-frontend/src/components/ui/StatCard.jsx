import styles from './StatCard.module.css'

export default function StatCard({ label, value, icon, trend, color = 'accent' }) {
  return (
    <div className={`${styles.card} ${styles[color]}`}>
      <div className={styles.top}>
        <div className={styles.icon}>{icon}</div>
        {trend !== undefined && (
          <span className={`${styles.trend} ${trend >= 0 ? styles.up : styles.down}`}>
            {trend >= 0 ? '↑' : '↓'} {Math.abs(trend)}%
          </span>
        )}
      </div>
      <div className={styles.value}>{value ?? '—'}</div>
      <div className={styles.label}>{label}</div>
      <div className={styles.glow} />
    </div>
  )
}
