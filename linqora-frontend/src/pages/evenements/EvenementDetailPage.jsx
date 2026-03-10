import { useEffect, useState } from 'react'
import { useParams, Link, useNavigate } from 'react-router-dom'
import { motion } from 'framer-motion'
import api from '@/lib/api'
import useAuthStore from '@/store/authStore'
import PageHeader from '@/components/ui/PageHeader'
import Button from '@/components/ui/Button'
import Card from '@/components/ui/Card'
import Badge from '@/components/ui/Badge'
import StatCard from '@/components/ui/StatCard'
import toast from 'react-hot-toast'
import { Edit, Trash2, Calendar, MapPin, Users, Clock, QrCode, Network, ChevronRight } from 'lucide-react'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import styles from './EvenementDetailPage.module.css'

export default function EvenementDetailPage() {
  const { id } = useParams()
  const { canManage } = useAuthStore()
  const navigate = useNavigate()
  const [ev, setEv] = useState(null)
  const [dashboard, setDashboard] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    Promise.all([
      api.get(`/evenements/${id}`),
      canManage() ? api.get(`/evenements/${id}/dashboard`) : Promise.resolve(null),
    ]).then(([evR, dashR]) => {
      setEv(evR.data)
      if (dashR) setDashboard(dashR.data)
    }).catch(() => toast.error('Événement introuvable'))
     .finally(() => setLoading(false))
  }, [id])

  const handleDelete = async () => {
    if (!confirm('Supprimer cet événement ?')) return
    await api.delete(`/evenements/${id}`)
    toast.success('Événement supprimé')
    navigate('/evenements')
  }

  const handleStatut = async (statut) => {
    await api.patch(`/evenements/${id}/statut`, { statut })
    setEv(prev => ({ ...prev, statut }))
    toast.success(`Statut mis à jour : ${statut}`)
  }

  if (loading) return <div className={styles.loading}><div className="skeleton" style={{ height:400, borderRadius:16 }}/></div>
  if (!ev) return null

  return (
    <div>
      <PageHeader
        title={ev.titre}
        breadcrumb={<><Link to="/evenements">Événements</Link> <ChevronRight size={12}/> Détail</>}
        actions={canManage() && (
          <div style={{ display:'flex', gap:10 }}>
            <select
              className={styles.statutSelect}
              value={ev.statut}
              onChange={e => handleStatut(e.target.value)}
            >
              {['brouillon','publie','archive','annule'].map(s => (
                <option key={s} value={s}>{s}</option>
              ))}
            </select>
            <Link to={`/evenements/${id}/edit`}>
              <Button variant="secondary" icon={<Edit size={15}/>}>Modifier</Button>
            </Link>
            <Button variant="danger" icon={<Trash2 size={15}/>} onClick={handleDelete}>Supprimer</Button>
          </div>
        )}
      />

      {/* Stats dashboard */}
      {dashboard && (
        <div className={styles.statsGrid}>
          <StatCard label="Inscrits confirmés" value={dashboard.inscrits_confirmes} icon={<Users/>} color="accent" />
          <StatCard label="Présents" value={dashboard.inscrits_presents} icon={<Users/>} color="success" />
          <StatCard label="Absents" value={dashboard.inscrits_absents} icon={<Users/>} color="warning" />
          <StatCard label="Taux participation" value={`${dashboard.taux_participation}%`} icon={<ChevronRight/>} color="purple" />
        </div>
      )}

      <div className={styles.grid}>
        {/* Info */}
        <Card title="Informations">
          <div className={styles.infoList}>
            <div className={styles.infoRow}><Calendar size={15}/><span>{format(new Date(ev.dateDebut), 'PPPP', { locale: fr })}</span></div>
            <div className={styles.infoRow}><Clock size={15}/><span>{format(new Date(ev.dateDebut), 'HH:mm')} → {format(new Date(ev.dateFin), 'HH:mm')}</span></div>
            {ev.lieu && <div className={styles.infoRow}><MapPin size={15}/><span>{ev.lieu}</span></div>}
            <div className={styles.infoRow}><Badge label={ev.mode} color="blue" size="md"/></div>
            {ev.description && <p className={styles.desc}>{ev.description}</p>}
          </div>
        </Card>

        {/* Actions rapides */}
        <div className={styles.actions}>
          <Link to={`/evenements/${id}/inscriptions`} className={styles.actionCard}>
            <Users size={24} />
            <div>
              <div className={styles.actionTitle}>Inscriptions</div>
              <div className={styles.actionSub}>{ev.inscriptions_count || 0} inscrits</div>
            </div>
            <ChevronRight size={16} className={styles.actionArrow} />
          </Link>
          <Link to={`/evenements/${id}/ateliers`} className={styles.actionCard}>
            <Calendar size={24} />
            <div>
              <div className={styles.actionTitle}>Ateliers</div>
              <div className={styles.actionSub}>{ev.ateliers_count || 0} ateliers</div>
            </div>
            <ChevronRight size={16} className={styles.actionArrow} />
          </Link>
          <Link to="/pointage" className={styles.actionCard}>
            <QrCode size={24} />
            <div>
              <div className={styles.actionTitle}>Scan QR</div>
              <div className={styles.actionSub}>Pointage présences</div>
            </div>
            <ChevronRight size={16} className={styles.actionArrow} />
          </Link>
          {ev.networkingActif && (
            <Link to="/networking" className={styles.actionCard}>
              <Network size={24} />
              <div>
                <div className={styles.actionTitle}>Networking</div>
                <div className={styles.actionSub}>Rencontres one-to-one</div>
              </div>
              <ChevronRight size={16} className={styles.actionArrow} />
            </Link>
          )}
        </div>
      </div>

      {/* Ateliers */}
      {ev.ateliers?.length > 0 && (
        <Card title="Programme des ateliers" className={styles.ateliersCard}>
          <div className={styles.ateliersList}>
            {ev.ateliers.map(a => (
              <div key={a.idAtelier} className={styles.atelierItem}>
                <div className={styles.atelierTime}>{format(new Date(a.horaire), 'HH:mm')}</div>
                <div className={styles.atelierInfo}>
                  <div className={styles.atelierTitle}>{a.titre}</div>
                  <div className={styles.atelierMeta}>
                    {a.salle && <span>📍 {a.salle}</span>}
                    <span>⏱ {a.dureeMinutes} min</span>
                    <Badge label={a.categorie} />
                  </div>
                  {a.speakers?.length > 0 && (
                    <div className={styles.speakers}>
                      {a.speakers.map(s => (
                        <span key={s.idSpeaker} className={styles.speakerChip}>{s.nomComplet}</span>
                      ))}
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </Card>
      )}
    </div>
  )
}
