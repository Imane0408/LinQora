import { useEffect, useState } from 'react'
import { motion } from 'framer-motion'
import api from '@/lib/api'
import toast from 'react-hot-toast'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'
import Badge from '@/components/ui/Badge'
import Modal from '@/components/ui/Modal'
import EmptyState from '@/components/ui/EmptyState'
import { Network, Calendar, MessageSquare, Check, X } from 'lucide-react'
import styles from './NetworkingPage.module.css'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'

export default function NetworkingPage() {
  const [rencontres, setRencontres] = useState([])
  const [loading, setLoading] = useState(true)

  const load = async () => {
    try {
      const r = await api.get('/networking/mes-rencontres')
      setRencontres(r.data)
    } catch {}
    setLoading(false)
  }

  useEffect(() => { load() }, [])

  const handleRepondre = async (id, action) => {
    try {
      await api.patch(`/networking/rencontres/${id}/repondre`, { action })
      toast.success(action === 'accepter' ? 'Rencontre acceptée !' : 'Rencontre refusée.')
      load()
    } catch { toast.error('Erreur') }
  }

  return (
    <div>
      <PageHeader title="Networking" subtitle="Vos rencontres one-to-one" />

      {loading ? (
        <div className={styles.grid}>
          {Array(4).fill(0).map((_, i) => <div key={i} className={`skeleton ${styles.skCard}`}/>)}
        </div>
      ) : rencontres.length === 0 ? (
        <EmptyState icon={<Network size={48}/>} title="Aucune rencontre" description="Activez votre profil networking sur un événement pour commencer à proposer des rencontres." />
      ) : (
        <div className={styles.grid}>
          {rencontres.map((r, i) => (
            <motion.div key={r.idRencontre} initial={{ opacity:0, y:16 }} animate={{ opacity:1, y:0 }} transition={{ delay: i*0.06 }}>
              <Card>
                <div className={styles.rencontre}>
                  <div className={styles.header}>
                    <Badge label={r.statut} />
                    <span className={styles.date}>
                      <Calendar size={13}/> {format(new Date(r.creaneau), 'd MMM yyyy à HH:mm', { locale: fr })}
                    </span>
                  </div>
                  <div className={styles.participants}>
                    <div className={styles.person}>
                      <div className={styles.avatar}>{r.profilDemandeur?.participant?.utilisateur?.prenom?.[0]}</div>
                      <span>{r.profilDemandeur?.participant?.utilisateur?.prenom} {r.profilDemandeur?.participant?.utilisateur?.nom}</span>
                    </div>
                    <div className={styles.arrow}>→</div>
                    <div className={styles.person}>
                      <div className={styles.avatar}>{r.profilReceveur?.participant?.utilisateur?.prenom?.[0]}</div>
                      <span>{r.profilReceveur?.participant?.utilisateur?.prenom} {r.profilReceveur?.participant?.utilisateur?.nom}</span>
                    </div>
                  </div>
                  {r.message && <p className={styles.message}><MessageSquare size={13}/> {r.message}</p>}
                  {r.lieu && <p className={styles.lieu}>📍 {r.lieu}</p>}
                  {r.statut === 'en_attente' && (
                    <div className={styles.actionsRow}>
                      <Button variant="success" size="sm" icon={<Check size={13}/>} onClick={() => handleRepondre(r.idRencontre, 'accepter')}>Accepter</Button>
                      <Button variant="danger" size="sm" icon={<X size={13}/>} onClick={() => handleRepondre(r.idRencontre, 'refuser')}>Refuser</Button>
                    </div>
                  )}
                </div>
              </Card>
            </motion.div>
          ))}
        </div>
      )}
    </div>
  )
}
