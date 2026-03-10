import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import api from '@/lib/api'
import useAuthStore from '@/store/authStore'
import PageHeader from '@/components/ui/PageHeader'
import Button from '@/components/ui/Button'
import Badge from '@/components/ui/Badge'
import EmptyState from '@/components/ui/EmptyState'
import { Plus, Calendar, MapPin, Users, Search, Filter } from 'lucide-react'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import styles from './EvenementsPage.module.css'

export default function EvenementsPage() {
  const { canManage } = useAuthStore()
  const [events, setEvents] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [statut, setStatut] = useState('')

  const load = async () => {
    setLoading(true)
    try {
      const params = {}
      if (search) params.search = search
      if (statut) params.statut = statut
      const r = await api.get('/evenements', { params })
      setEvents(r.data.data || [])
    } catch {}
    setLoading(false)
  }

  useEffect(() => { load() }, [search, statut])

  return (
    <div>
      <PageHeader
        title="Événements"
        subtitle={`${events.length} événements trouvés`}
        actions={canManage() && (
          <Link to="/evenements/nouveau">
            <Button icon={<Plus size={16}/>}>Créer un événement</Button>
          </Link>
        )}
      />

      {/* Filters */}
      <div className={styles.filters}>
        <div className={styles.searchBox}>
          <Search size={16} className={styles.searchIcon} />
          <input
            className={styles.searchInput}
            placeholder="Rechercher un événement..."
            value={search}
            onChange={e => setSearch(e.target.value)}
          />
        </div>
        <select className={styles.select} value={statut} onChange={e => setStatut(e.target.value)}>
          <option value="">Tous les statuts</option>
          <option value="publie">Publié</option>
          <option value="brouillon">Brouillon</option>
          <option value="archive">Archivé</option>
          <option value="annule">Annulé</option>
        </select>
      </div>

      {loading ? (
        <div className={styles.grid}>
          {Array(6).fill(0).map((_, i) => <div key={i} className={`skeleton ${styles.skCard}`}/>)}
        </div>
      ) : events.length === 0 ? (
        <EmptyState
          icon="📅"
          title="Aucun événement"
          description="Créez votre premier événement pour commencer."
          action={canManage() && <Link to="/evenements/nouveau"><Button icon={<Plus size={16}/>}>Créer un événement</Button></Link>}
        />
      ) : (
        <div className={styles.grid}>
          {events.map((ev, i) => (
            <motion.div key={ev.idEvenement} initial={{ opacity:0, y:16 }} animate={{ opacity:1, y:0 }} transition={{ delay: i*0.05 }}>
              <Link to={`/evenements/${ev.idEvenement}`} className={styles.card}>
                {/* Banner */}
                <div className={styles.banner} style={{ background: `linear-gradient(135deg, hsl(${(i*47)%360},60%,30%), hsl(${(i*47+60)%360},60%,20%))` }}>
                  <Badge label={ev.statut} />
                  <Badge label={ev.mode} color="blue" />
                </div>
                {/* Content */}
                <div className={styles.cardBody}>
                  <h3 className={styles.cardTitle}>{ev.titre}</h3>
                  <div className={styles.cardMeta}>
                    <span><Calendar size={13}/> {ev.dateDebut ? format(new Date(ev.dateDebut), 'd MMM yyyy', { locale: fr }) : '—'}</span>
                    {ev.lieu && <span><MapPin size={13}/> {ev.lieu}</span>}
                    <span><Users size={13}/> {ev.inscriptions_count || 0} inscrits</span>
                  </div>
                  {ev.description && (
                    <p className={styles.cardDesc}>{ev.description.substring(0,100)}…</p>
                  )}
                </div>
              </Link>
            </motion.div>
          ))}
        </div>
      )}
    </div>
  )
}
