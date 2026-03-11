import { useEffect, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
import api from '@/lib/api'
import toast from 'react-hot-toast'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Table from '@/components/ui/Table'
import Badge from '@/components/ui/Badge'
import Button from '@/components/ui/Button'
import { ChevronRight, Check, X, Download, Search } from 'lucide-react'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import styles from './InscriptionsPage.module.css'

export default function InscriptionsPage() {
  const { id } = useParams()
  const [inscriptions, setInscriptions] = useState([])
  const [loading, setLoading] = useState(true)
  const [search, setSearch] = useState('')
  const [statut, setStatut] = useState('')

  const load = async () => {
    setLoading(true)
    try {
      const params = {}
      if (search) params.search = search
      if (statut) params.statut = statut
      const r = await api.get(`/evenements/${id}/inscriptions`, { params })
      setInscriptions(r.data.data || [])
    } catch {}
    setLoading(false)
  }

  useEffect(() => { load() }, [search, statut])

  const handleValider = async (insc) => {
    await api.patch(`/inscriptions/${insc.idInscription}/valider`)
    toast.success('Inscription validée')
    load()
  }
  const handleAnnuler = async (insc) => {
    await api.patch(`/inscriptions/${insc.idInscription}/annuler`)
    toast.success('Inscription annulée')
    load()
  }
  const handlePaye = async (insc) => {
    await api.patch(`/inscriptions/${insc.idInscription}/paiement`, { methode: 'manuel' })
    toast.success('Paiement enregistré')
    load()
  }
  const exportCsv = () => { window.open(`/api/evenements/${id}/export/excel`, '_blank') }

  const columns = [
    { key: 'participant', label: 'Participant', render: r => (
      <div>
        <div style={{ fontWeight:600 }}>{r.participant?.utilisateur?.prenom} {r.participant?.utilisateur?.nom}</div>
        <div style={{ fontSize:12, color:'var(--color-text3)' }}>{r.participant?.utilisateur?.email}</div>
      </div>
    )},
    { key: 'organisation', label: 'Organisation', render: r => r.participant?.organisation || '—' },
    { key: 'dateInscr', label: 'Inscrit le', render: r => r.dateInscr ? format(new Date(r.dateInscr), 'd MMM yyyy', { locale: fr }) : '—' },
    { key: 'statut',    label: 'Statut',     render: r => <Badge label={r.statut} /> },
    { key: 'present',   label: 'Présent',    render: r => r.presentGlobal ? <Badge label="présent" color="success"/> : <Badge label="absent" color="neutral"/> },
    { key: 'paiement',  label: 'Payé',       render: r => r.aPaye ? <Badge label="payé" color="success"/> : <Badge label="non payé" color="warning"/> },
    { key: 'actions',   label: '',           render: r => (
      <div className={styles.actions}>
        {r.statut === 'en_attente' && <Button size="sm" variant="success" icon={<Check size={13}/>} onClick={() => handleValider(r)}>Valider</Button>}
        {r.statut !== 'annule' && <Button size="sm" variant="danger" icon={<X size={13}/>} onClick={() => handleAnnuler(r)}>Annuler</Button>}
        {!r.aPaye && <Button size="sm" variant="secondary" onClick={() => handlePaye(r)}>Marquer payé</Button>}
      </div>
    )},
  ]

  return (
    <div>
      <PageHeader
        title="Inscriptions"
        breadcrumb={<><Link to="/evenements">Événements</Link> <ChevronRight size={12}/> <Link to={`/evenements/${id}`}>Détail</Link> <ChevronRight size={12}/> Inscriptions</>}
        actions={<Button variant="secondary" icon={<Download size={15}/>} onClick={exportCsv}>Exporter CSV</Button>}
      />
      <div className={styles.filters}>
        <div className={styles.searchBox}>
          <Search size={16} className={styles.searchIcon}/>
          <input className={styles.searchInput} placeholder="Rechercher..." value={search} onChange={e => setSearch(e.target.value)}/>
        </div>
        <select className={styles.select} value={statut} onChange={e => setStatut(e.target.value)}>
          <option value="">Tous</option>
          <option value="confirme">Confirmés</option>
          <option value="en_attente">En attente</option>
          <option value="annule">Annulés</option>
        </select>
      </div>
      <Card>
        <Table columns={columns} data={inscriptions} loading={loading} emptyMsg="Aucune inscription" />
      </Card>
    </div>
  )
}
