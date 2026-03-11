import styles from './Card.module.css'

export default function Card({ children, title, subtitle, action, noPad, glass, className = '' }) {
  return (
    <div className={`${styles.card} ${glass ? styles.glass : ''} ${className}`}>
      {(title || action) && (
        <div className={styles.header}>
          <div>
            {title && <h3 className={styles.title}>{title}</h3>}
            {subtitle && <p className={styles.subtitle}>{subtitle}</p>}
          </div>
          {action && <div className={styles.action}>{action}</div>}
        </div>
      )}
      <div className={noPad ? '' : styles.body}>{children}</div>
    </div>
  )
}
