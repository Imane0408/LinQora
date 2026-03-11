import { useEffect, useState } from 'react'
import { motion } from 'framer-motion'
import api from '@/lib/api'
import toast from 'react-hot-toast'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'
import EmptyState from '@/components/ui/EmptyState'
import { Bell, Check, Trash2, CheckCheck } from 'lucide-react'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import styles from './NotificationsPage.module.css'

export default function NotificationsPage() {
  const [notifs, setNotifs] = useState([])
  const [loading, setLoading] = useState(true)

  const load = async () => {
    try {
      const r = await api.get('/notifications')
      setNotifs(r.data.data || [])
    } catch {}
    setLoading(false)
  }

  useEffect(() => { load() }, [])

  const marquerLu = async (id) => {
    await api.patch(`/notifications/${id}/lire`)
    load()
  }
  const supprimer = async (id) => {
    await api.delete(`/notifications/${id}`)
    load()
  }
  const toutLire = async () => {
    await api.patch('/notifications/tout-lire')
    toast.success('Toutes les notifications lues')
    load()
  }

  return (
    <div>
      <PageHeader
        title="Notifications"
        subtitle={`${notifs.filter(n => !n.lu).length} non lues`}
        actions={<Button variant="secondary" icon={<CheckCheck size={15}/>} onClick={toutLire}>Tout marquer lu</Button>}
      />
      <Card>
        {loading ? (
          Array(5).fill(0).map((_, i) => <div key={i} className={`skeleton ${styles.skNotif}`}/>)
        ) : notifs.length === 0 ? (
          <EmptyState icon={<Bell size={40}/>} title="Aucune notification" />
        ) : (
          <div className={styles.list}>
            {notifs.map((n, i) => (
              <motion.div
                key={n.idNotification}
                className={`${styles.item} ${!n.lu ? styles.unread : ''}`}
                initial={{ opacity:0, x:-10 }} animate={{ opacity:1, x:0 }} transition={{ delay: i*0.04 }}
              >
                <div className={`${styles.dot} ${!n.lu ? styles.dotActive : ''}`}/>
                <div className={styles.content}>
                  <div className={styles.subject}>{n.sujet}</div>
                  <div className={styles.body}>{n.contenu}</div>
                  <div className={styles.time}>{format(new Date(n.dateCreation), 'd MMM à HH:mm', { locale: fr })}</div>
                </div>
                <div className={styles.itemActions}>
                  {!n.lu && <Button size="sm" variant="ghost" icon={<Check size={13}/>} onClick={() => marquerLu(n.idNotification)} />}
                  <Button size="sm" variant="ghost" icon={<Trash2 size={13}/>} onClick={() => supprimer(n.idNotification)} />
                </div>
              </motion.div>
            ))}
          </div>
        )}
      </Card>
    </div>
  )
}
