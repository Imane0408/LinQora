import styles from './Input.module.css'

export default function Input({
  label, error, icon, type = 'text', placeholder,
  value, onChange, name, register, required, ...props
}) {
  const inputProps = register ? register(name, { required }) : { value, onChange }
  return (
    <div className={styles.field}>
      {label && <label className={styles.label}>{label}</label>}
      <div className={styles.wrapper}>
        {icon && <span className={styles.iconLeft}>{icon}</span>}
        <input
          type={type}
          placeholder={placeholder}
          className={`${styles.input} ${icon ? styles.hasIcon : ''} ${error ? styles.hasError : ''}`}
          {...inputProps}
          {...props}
        />
      </div>
      {error && <span className={styles.error}>{error}</span>}
    </div>
  )
}
