import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { motion } from 'framer-motion'
import api from '@/lib/api'
import toast from 'react-hot-toast'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Table from '@/components/ui/Table'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Modal from '@/components/ui/Modal'
import Badge from '@/components/ui/Badge'
import { Plus, ToggleLeft, ToggleRight, Key, Search } from 'lucide-react'
import styles from './AdminPages.module.css'

export default function UtilisateursPage() {
  const [users, setUsers] = useState([])
  const [roles, setRoles] = useState([])
  const [loading, setLoading] = useState(true)
  const [modal, setModal] = useState(false)
  const [search, setSearch] = useState('')
  const { register, handleSubmit, reset, formState: { errors } } = useForm()

  const load = async () => {
    try {
      const [uRes, rRes] = await Promise.all([
        api.get('/utilisateurs', { params: { search } }),
        api.get && Promise.resolve({ data: [
          { idRole:1, nomRole:'admin_entreprise' },
          { idRole:2, nomRole:'gestionnaire' },
          { idRole:3, nomRole:'operateur_scan' },
          { idRole:4, nomRole:'participant' },
        ] }),
      ])
      setUsers(uRes.data.data || [])
    } catch {} finally { setLoading(false) }
  }
  useEffect(() => { load() }, [search])

  const toggleActif = async (u) => {
    await api.patch(`/utilisateurs/${u.idUtilisateur}/toggle-actif`)
    toast.success('Statut mis à jour'); load()
  }
  const resetPassword = async (u) => {
    const r = await api.patch(`/utilisateurs/${u.idUtilisateur}/reset-password`)
    toast.success(`Nouveau mot de passe : ${r.data.new_password}`)
  }
  const onSubmit = async (data) => {
    try {
      await api.post('/utilisateurs', { ...data, password: data.motDePasse })
      toast.success('Utilisateur créé'); setModal(false); reset(); load()
    } catch (err) {
      const errs = err.response?.data?.errors
      if (errs) Object.values(errs).flat().forEach(m => toast.error(m))
      else toast.error('Erreur')
    }
  }

  const columns = [
    { key:'nom', label:'Nom', render: r => (
      <div>
        <div style={{ fontWeight:600 }}>{r.prenom} {r.nom}</div>
        <div style={{ fontSize:12, color:'var(--color-text3)' }}>{r.email}</div>
      </div>
    )},
    { key:'role',       label:'Rôle',       render: r => <Badge label={r.role} /> },
    { key:'entreprise', label:'Entreprise',  render: r => r.entreprise?.nom || '—' },
    { key:'actif',      label:'Statut',      render: r => <Badge label={r.actif ? 'actif' : 'inactif'} color={r.actif ? 'success' : 'danger'}/> },
    { key:'actions',    label:'',            render: r => (
      <div className={styles.tableActions}>
        <Button size="sm" variant="ghost" icon={r.actif ? <ToggleRight size={14}/> : <ToggleLeft size={14}/>} onClick={() => toggleActif(r)}>
          {r.actif ? 'Désactiver' : 'Activer'}
        </Button>
        <Button size="sm" variant="ghost" icon={<Key size={13}/>} onClick={() => resetPassword(r)}>MDP</Button>
      </div>
    )},
  ]

  return (
    <div>
      <PageHeader
        title="Utilisateurs"
        subtitle={`${users.length} comptes`}
        actions={<Button icon={<Plus size={16}/>} onClick={() => { reset(); setModal(true) }}>Nouveau</Button>}
      />
      <div className={styles.filters}>
        <div className={styles.searchBox}>
          <Search size={16} className={styles.searchIcon}/>
          <input className={styles.searchInput} placeholder="Rechercher..." value={search} onChange={e => setSearch(e.target.value)}/>
        </div>
      </div>
      <Card>
        <Table columns={columns} data={users} loading={loading} emptyMsg="Aucun utilisateur" />
      </Card>

      <Modal open={modal} onClose={() => setModal(false)} title="Nouvel utilisateur">
        <form onSubmit={handleSubmit(onSubmit)} className={styles.form}>
          <div className={styles.row2}>
            <Input label="Prénom *" register={register} name="prenom" required />
            <Input label="Nom *"    register={register} name="nom"    required />
          </div>
          <Input label="Email *" type="email" register={register} name="email" required />
          <Input label="Mot de passe *" type="password" register={register} name="motDePasse" required />
          <div className={styles.field}>
            <label className={styles.label}>Rôle *</label>
            <select className={styles.select} {...register('idRole', { required: true })}>
              <option value="">Choisir un rôle</option>
              <option value="1">Admin Entreprise</option>
              <option value="2">Gestionnaire</option>
              <option value="3">Opérateur Scan</option>
              <option value="4">Participant</option>
            </select>
          </div>
          <div className={styles.modalActions}>
            <Button variant="secondary" type="button" onClick={() => setModal(false)}>Annuler</Button>
            <Button type="submit">Créer</Button>
          </div>
        </form>
      </Modal>
    </div>
  )
}
