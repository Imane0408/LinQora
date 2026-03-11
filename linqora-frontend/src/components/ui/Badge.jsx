import styles from './Badge.module.css'

const colorMap = {
  publie:     'success',
  confirme:   'success',
  accepte:    'success',
  brouillon:  'warning',
  en_attente: 'warning',
  archive:    'neutral',
  annule:     'danger',
  refuse:     'danger',
  presentiel: 'accent',
  en_ligne:   'blue',
  hybride:    'purple',
  termine:    'neutral',
}

export default function Badge({ label, color, size = 'sm' }) {
  const resolvedColor = color || colorMap[label?.toLowerCase()] || 'neutral'
  const displayLabel = label?.replace(/_/g, ' ')
  return (
    <span className={`${styles.badge} ${styles[resolvedColor]} ${styles[size]}`}>
      {displayLabel}
    </span>
  )
}
