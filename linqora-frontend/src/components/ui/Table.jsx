import styles from './Table.module.css'

export default function Table({ columns, data, loading, emptyMsg = 'Aucune donnée' }) {
  return (
    <div className={styles.wrapper}>
      <table className={styles.table}>
        <thead>
          <tr>
            {columns.map(col => (
              <th key={col.key} className={styles.th} style={{ width: col.width }}>
                {col.label}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {loading ? (
            Array(5).fill(0).map((_, i) => (
              <tr key={i} className={styles.tr}>
                {columns.map(col => (
                  <td key={col.key} className={styles.td}>
                    <div className={`skeleton ${styles.skeletonCell}`} />
                  </td>
                ))}
              </tr>
            ))
          ) : data?.length === 0 ? (
            <tr><td colSpan={columns.length} className={styles.empty}>{emptyMsg}</td></tr>
          ) : (
            data?.map((row, i) => (
              <tr key={row.id || i} className={styles.tr}>
                {columns.map(col => (
                  <td key={col.key} className={styles.td}>
                    {col.render ? col.render(row) : row[col.key]}
                  </td>
                ))}
              </tr>
            ))
          )}
        </tbody>
      </table>
    </div>
  )
}
