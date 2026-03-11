import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { motion } from 'framer-motion'
import { AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts'
import api from '@/lib/api'
import useAuthStore from '@/store/authStore'
import StatCard from '@/components/ui/StatCard'
import Card from '@/components/ui/Card'
import Badge from '@/components/ui/Badge'
import Button from '@/components/ui/Button'
import PageHeader from '@/components/ui/PageHeader'
import { Calendar, Users, CheckCircle, TrendingUp, ArrowRight, Plus } from 'lucide-react'
import styles from './DashboardPage.module.css'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'

export default function DashboardPage() {
  const { user, isSuperAdmin, canManage } = useAuthStore()
  const [stats, setStats] = useState(null)
  const [events, setEvents] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const load = async () => {
      try {
        const [evRes] = await Promise.all([
          api.get('/evenements?per_page=6'),
        ])
        setEvents(evRes.data.data || [])
        // Stats globales si super admin
        if (isSuperAdmin()) {
          const s = await api.get('/super-admin/stats-globales')
          setStats(s.data)
        }
      } catch {}
      setLoading(false)
    }
    load()
  }, [])

  const chartData = [
    { name: 'Jan', inscrits: 40 }, { name: 'Fév', inscrits: 65 },
    { name: 'Mar', inscrits: 55 }, { name: 'Avr', inscrits: 90 },
    { name: 'Mai', inscrits: 120 },{ name: 'Juin', inscrits: 145 },
  ]

  return (
    <div>
      <PageHeader
        title={`Bonjour, ${user?.prenom} 👋`}
        subtitle={`Bienvenue sur votre tableau de bord LinQora — ${format(new Date(), 'EEEE d MMMM yyyy', { locale: fr })}`}
        actions={canManage() && (
          <Button icon={<Plus size={16}/>} size="md">
            <Link to="/evenements/nouveau" style={{ color: 'inherit' }}>Nouvel événement</Link>
          </Button>
        )}
      />

      {/* Stats */}
      {stats && (
        <div className={styles.statsGrid}>
          {[
            { label: 'Entreprises', value: stats.total_entreprises, icon: <Calendar/>, color: 'accent' },
            { label: 'Événements',  value: stats.total_evenements,  icon: <Calendar/>, color: 'success' },
            { label: 'Participants',value: stats.total_participants, icon: <Users/>,    color: 'warning' },
            { label: 'Inscriptions',value: stats.total_inscriptions, icon: <CheckCircle/>, color: 'purple' },
          ].map((s, i) => (
            <motion.div key={s.label} initial={{ opacity:0, y:20 }} animate={{ opacity:1, y:0 }} transition={{ delay: i*0.07 }}>
              <StatCard {...s} />
            </motion.div>
          ))}
        </div>
      )}

      <div className={styles.grid}>
        {/* Graphique */}
        <Card title="Inscriptions — 6 derniers mois" className={styles.chartCard}>
          <ResponsiveContainer width="100%" height={220}>
            <AreaChart data={chartData}>
              <defs>
                <linearGradient id="grad" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%"  stopColor="#6366f1" stopOpacity={0.4}/>
                  <stop offset="95%" stopColor="#6366f1" stopOpacity={0}/>
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" stroke="rgba(255,255,255,0.05)" />
              <XAxis dataKey="name" stroke="#64748b" tick={{ fontSize:12 }} />
              <YAxis stroke="#64748b" tick={{ fontSize:12 }} />
              <Tooltip
                contentStyle={{ background:'#181d2e', border:'1px solid rgba(255,255,255,0.1)', borderRadius:'8px', fontSize:'13px' }}
                labelStyle={{ color:'#94a3b8' }}
              />
              <Area type="monotone" dataKey="inscrits" stroke="#6366f1" fill="url(#grad)" strokeWidth={2} />
            </AreaChart>
          </ResponsiveContainer>
        </Card>

        {/* Événements récents */}
        <Card
          title="Événements récents"
          action={<Link to="/evenements"><Button variant="ghost" size="sm" icon={<ArrowRight size={14}/>}>Voir tout</Button></Link>}
        >
          <div className={styles.eventList}>
            {loading ? (
              Array(4).fill(0).map((_, i) => <div key={i} className={`skeleton ${styles.skEvent}`}/>)
            ) : events.slice(0,5).map(ev => (
              <Link to={`/evenements/${ev.idEvenement}`} key={ev.idEvenement} className={styles.eventRow}>
                <div className={styles.eventDot} />
                <div className={styles.eventInfo}>
                  <span className={styles.eventTitle}>{ev.titre}</span>
                  <span className={styles.eventDate}>{ev.dateDebut ? format(new Date(ev.dateDebut), 'd MMM yyyy', { locale: fr }) : ''}</span>
                </div>
                <Badge label={ev.statut} />
              </Link>
            ))}
          </div>
        </Card>
      </div>
    </div>
  )
}
