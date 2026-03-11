import { useEffect, useState } from 'react'
import { useForm } from 'react-hook-form'
import { motion } from 'framer-motion'
import api from '@/lib/api'
import toast from 'react-hot-toast'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Modal from '@/components/ui/Modal'
import Badge from '@/components/ui/Badge'
import EmptyState from '@/components/ui/EmptyState'
import { Plus, Edit, Trash2, Building2 } from 'lucide-react'
import styles from './AdminPages.module.css'

export default function EntreprisesPage() {
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)
  const [modal, setModal] = useState(false)
  const { register, handleSubmit, reset, formState: { errors } } = useForm()

  const load = async () => {
    try { const r = await api.get('/entreprises'); setItems(r.data.data || []) }
    catch {} finally { setLoading(false) }
  }
  useEffect(() => { load() }, [])

  const onSubmit = async (data) => {
    try {
      await api.post('/entreprises', data)
      toast.success('Entreprise créée'); setModal(false); reset(); load()
    } catch (err) {
      const errs = err.response?.data?.errors
      if (errs) Object.values(errs).flat().forEach(m => toast.error(m))
      else toast.error('Erreur')
    }
  }
  const handleDelete = async (id) => {
    if (!confirm('Supprimer cette entreprise ?')) return
    await api.delete(`/entreprises/${id}`); toast.success('Supprimée'); load()
  }

  return (
    <div>
      <PageHeader
        title="Entreprises"
        subtitle={`${items.length} entreprises`}
        actions={<Button icon={<Plus size={16}/>} onClick={() => { reset(); setModal(true) }}>Nouvelle entreprise</Button>}
      />
      {loading ? (
        <div className={styles.grid}>{Array(4).fill(0).map((_, i) => <div key={i} className={`skeleton ${styles.skCard}`}/>)}</div>
      ) : items.length === 0 ? (
        <EmptyState icon={<Building2 size={48}/>} title="Aucune entreprise" action={<Button icon={<Plus size={16}/>} onClick={() => setModal(true)}>Créer</Button>}/>
      ) : (
        <div className={styles.grid}>
          {items.map((e, i) => (
            <motion.div key={e.idEntreprise} initial={{ opacity:0, y:14 }} animate={{ opacity:1, y:0 }} transition={{ delay: i*0.05 }}>
              <Card>
                <div className={styles.itemCard}>
                  <div className={styles.iconBox}><Building2 size={24}/></div>
                  <div className={styles.info}>
                    <div className={styles.itemName}>{e.nom}</div>
                    <div className={styles.itemMeta}>{e.email || '—'}</div>
                    <div className={styles.counts}>
                      <span>{e.utilisateurs_count || 0} utilisateurs</span>
                      <span>{e.evenements_count || 0} événements</span>
                    </div>
                    <Badge label={e.actif ? 'actif' : 'inactif'} color={e.actif ? 'success' : 'danger'} />
                  </div>
                  <Button size="sm" variant="danger" icon={<Trash2 size={13}/>} onClick={() => handleDelete(e.idEntreprise)} />
                </div>
              </Card>
            </motion.div>
          ))}
        </div>
      )}

      <Modal open={modal} onClose={() => setModal(false)} title="Nouvelle entreprise" size="lg">
        <form onSubmit={handleSubmit(onSubmit)} className={styles.form}>
          <p className={styles.section}>Informations entreprise</p>
          <div className={styles.row2}>
            <Input label="Nom *" register={register} name="nom" required error={errors.nom?.message} />
            <Input label="Email" type="email" register={register} name="email" />
          </div>
          <div className={styles.row2}>
            <Input label="Téléphone" register={register} name="telephone" />
            <Input label="Adresse" register={register} name="adresse" />
          </div>
          <hr className={styles.divider}/>
          <p className={styles.section}>Compte Admin initial</p>
          <div className={styles.row2}>
            <Input label="Prénom admin *" register={register} name="admin_prenom" required />
            <Input label="Nom admin *" register={register} name="admin_nom" required />
          </div>
          <div className={styles.row2}>
            <Input label="Email admin *" type="email" register={register} name="admin_email" required />
            <Input label="Mot de passe *" type="password" register={register} name="admin_password" required />
          </div>
          <div className={styles.modalActions}>
            <Button variant="secondary" type="button" onClick={() => setModal(false)}>Annuler</Button>
            <Button type="submit">Créer l'entreprise</Button>
          </div>
        </form>
      </Modal>
    </div>
  )
}
