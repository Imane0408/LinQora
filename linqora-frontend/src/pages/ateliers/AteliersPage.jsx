import { useEffect, useState } from 'react'
import { useParams, Link } from 'react-router-dom'
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
import { Plus, Edit, Trash2, ChevronRight, Clock, Users } from 'lucide-react'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import styles from './AteliersPage.module.css'

export default function AteliersPage() {
  const { id } = useParams()
  const [ateliers, setAteliers] = useState([])
  const [loading, setLoading] = useState(true)
  const [modal, setModal] = useState(false)
  const [editing, setEditing] = useState(null)
  const { register, handleSubmit, reset, formState: { errors } } = useForm()

  const load = async () => {
    try { const r = await api.get(`/evenements/${id}/ateliers`); setAteliers(r.data) }
    catch {} finally { setLoading(false) }
  }
  useEffect(() => { load() }, [id])

  const openCreate = () => { setEditing(null); reset({ categorie:'public', dureeMinutes:60 }); setModal(true) }
  const openEdit   = (a)  => { setEditing(a); reset({ ...a, horaire: a.horaire?.slice(0,16) }); setModal(true) }

  const onSubmit = async (data) => {
    try {
      if (editing) await api.put(`/ateliers/${editing.idAtelier}`, data)
      else await api.post(`/evenements/${id}/ateliers`, data)
      toast.success(editing ? 'Atelier mis à jour' : 'Atelier créé')
      setModal(false); load()
    } catch { toast.error('Erreur') }
  }
  const handleDelete = async (aid) => {
    if (!confirm('Supprimer cet atelier ?')) return
    await api.delete(`/ateliers/${aid}`)
    toast.success('Supprimé'); load()
  }

  return (
    <div>
      <PageHeader
        title="Ateliers"
        breadcrumb={<><Link to="/evenements">Événements</Link> <ChevronRight size={12}/> <Link to={`/evenements/${id}`}>Détail</Link> <ChevronRight size={12}/> Ateliers</>}
        actions={<Button icon={<Plus size={16}/>} onClick={openCreate}>Ajouter un atelier</Button>}
      />
      {loading ? (
        <div>{Array(3).fill(0).map((_, i) => <div key={i} className={`skeleton ${styles.skCard}`}/>)}</div>
      ) : ateliers.length === 0 ? (
        <EmptyState icon="🎓" title="Aucun atelier" action={<Button icon={<Plus size={16}/>} onClick={openCreate}>Créer un atelier</Button>} />
      ) : (
        <div className={styles.list}>
          {ateliers.map((a, i) => (
            <motion.div key={a.idAtelier} initial={{ opacity:0, y:12 }} animate={{ opacity:1, y:0 }} transition={{ delay: i*0.06 }}>
              <Card>
                <div className={styles.atelierRow}>
                  <div className={styles.time}>
                    <div className={styles.timeH}>{format(new Date(a.horaire), 'HH:mm')}</div>
                    <div className={styles.timeD}>{format(new Date(a.horaire), 'd MMM', { locale: fr })}</div>
                  </div>
                  <div className={styles.info}>
                    <div className={styles.title}>{a.titre}</div>
                    <div className={styles.meta}>
                      <span><Clock size={12}/> {a.dureeMinutes} min</span>
                      {a.salle && <span>📍 {a.salle}</span>}
                      {a.capacite && <span><Users size={12}/> {a.capacite} places</span>}
                      <Badge label={a.categorie} />
                    </div>
                    {a.speakers?.length > 0 && (
                      <div className={styles.speakers}>
                        {a.speakers.map(s => (
                          <span key={s.idSpeaker} className={styles.chip}>{s.nomComplet}</span>
                        ))}
                      </div>
                    )}
                  </div>
                  <div className={styles.rowActions}>
                    <Button size="sm" variant="ghost" icon={<Edit size={13}/>} onClick={() => openEdit(a)} />
                    <Button size="sm" variant="ghost" icon={<Trash2 size={13}/>} onClick={() => handleDelete(a.idAtelier)} />
                  </div>
                </div>
              </Card>
            </motion.div>
          ))}
        </div>
      )}

      <Modal open={modal} onClose={() => setModal(false)} title={editing ? 'Modifier l\'atelier' : 'Nouvel atelier'}>
        <form onSubmit={handleSubmit(onSubmit)} className={styles.form}>
          <Input label="Titre *" register={register} name="titre" required error={errors.titre?.message} />
          <div className={styles.row2}>
            <div className={styles.field}>
              <label className={styles.label}>Date & heure *</label>
              <input type="datetime-local" className={styles.input} {...register('horaire', { required: true })} />
            </div>
            <Input label="Durée (min)" type="number" register={register} name="dureeMinutes" />
          </div>
          <div className={styles.row2}>
            <Input label="Salle" register={register} name="salle" />
            <Input label="Capacité" type="number" register={register} name="capacite" />
          </div>
          <div className={styles.field}>
            <label className={styles.label}>Catégorie</label>
            <select className={styles.input} {...register('categorie')}>
              <option value="public">Public</option>
              <option value="vip">VIP</option>
              <option value="prive">Privé</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div className={styles.field}>
            <label className={styles.label}>Description</label>
            <textarea className={styles.textarea} {...register('description')} />
          </div>
          <div className={styles.modalActions}>
            <Button variant="secondary" type="button" onClick={() => setModal(false)}>Annuler</Button>
            <Button type="submit">{editing ? 'Enregistrer' : 'Créer'}</Button>
          </div>
        </form>
      </Modal>
    </div>
  )
}
