import { useEffect, useState } from 'react'
import { motion } from 'framer-motion'
import api from '@/lib/api'
import toast from 'react-hot-toast'
import { useForm } from 'react-hook-form'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Modal from '@/components/ui/Modal'
import EmptyState from '@/components/ui/EmptyState'
import { Plus, Edit, Trash2, Mic2, Linkedin, Twitter } from 'lucide-react'
import styles from './SpeakersPage.module.css'

export default function SpeakersPage() {
  const [speakers, setSpeakers] = useState([])
  const [loading, setLoading] = useState(true)
  const [modal, setModal] = useState(false)
  const [editing, setEditing] = useState(null)
  const { register, handleSubmit, reset, formState: { errors } } = useForm()

  const load = async () => {
    try { const r = await api.get('/speakers'); setSpeakers(r.data.data || []) }
    catch {} finally { setLoading(false) }
  }
  useEffect(() => { load() }, [])

  const openCreate = () => { setEditing(null); reset({}); setModal(true) }
  const openEdit   = (s)  => { setEditing(s); reset(s); setModal(true) }

  const onSubmit = async (data) => {
    try {
      if (editing) await api.put(`/speakers/${editing.idSpeaker}`, data)
      else await api.post('/speakers', data)
      toast.success(editing ? 'Speaker mis à jour' : 'Speaker créé')
      setModal(false); load()
    } catch { toast.error('Erreur') }
  }

  const handleDelete = async (id) => {
    if (!confirm('Supprimer ce speaker ?')) return
    await api.delete(`/speakers/${id}`)
    toast.success('Supprimé'); load()
  }

  return (
    <div>
      <PageHeader title="Speakers" subtitle="Gérez vos intervenants" actions={<Button icon={<Plus size={16}/>} onClick={openCreate}>Ajouter</Button>} />

      {loading ? (
        <div className={styles.grid}>{Array(4).fill(0).map((_, i) => <div key={i} className={`skeleton ${styles.skCard}`}/>)}</div>
      ) : speakers.length === 0 ? (
        <EmptyState icon={<Mic2 size={48}/>} title="Aucun speaker" action={<Button icon={<Plus size={16}/>} onClick={openCreate}>Ajouter un speaker</Button>} />
      ) : (
        <div className={styles.grid}>
          {speakers.map((s, i) => (
            <motion.div key={s.idSpeaker} initial={{ opacity:0, y:16 }} animate={{ opacity:1, y:0 }} transition={{ delay: i*0.05 }}>
              <Card>
                <div className={styles.speakerCard}>
                  <div className={styles.avatar}>{s.nomComplet?.[0]}</div>
                  <div className={styles.info}>
                    <div className={styles.name}>{s.nomComplet}</div>
                    {s.titre && <div className={styles.titre}>{s.titre}</div>}
                    {s.organisation && <div className={styles.org}>{s.organisation}</div>}
                    <div className={styles.links}>
                      {s.linkedin && <a href={s.linkedin} target="_blank" rel="noreferrer" className={styles.link}><Linkedin size={14}/></a>}
                      {s.twitter  && <span className={styles.link}><Twitter size={14}/> {s.twitter}</span>}
                    </div>
                    <div className={styles.atelierCount}>{s.ateliers_count || 0} ateliers</div>
                  </div>
                  <div className={styles.cardActions}>
                    <Button size="sm" variant="ghost" icon={<Edit size={13}/>} onClick={() => openEdit(s)} />
                    <Button size="sm" variant="ghost" icon={<Trash2 size={13}/>} onClick={() => handleDelete(s.idSpeaker)} />
                  </div>
                </div>
              </Card>
            </motion.div>
          ))}
        </div>
      )}

      <Modal open={modal} onClose={() => setModal(false)} title={editing ? 'Modifier le speaker' : 'Nouveau speaker'}>
        <form onSubmit={handleSubmit(onSubmit)} className={styles.form}>
          <Input label="Nom complet *" register={register} name="nomComplet" required error={errors.nomComplet?.message} />
          <Input label="Titre" placeholder="CEO, Directeur..." register={register} name="titre" />
          <Input label="Organisation" register={register} name="organisation" />
          <Input label="LinkedIn" placeholder="https://linkedin.com/in/..." register={register} name="linkedin" />
          <Input label="Twitter / X" placeholder="@handle" register={register} name="twitter" />
          <Input label="Email" type="email" register={register} name="email" />
          <div className={styles.modalLabel}>Bio</div>
          <textarea className={styles.textarea} {...register('bio')} placeholder="Présentation du speaker..." />
          <div className={styles.modalActions}>
            <Button variant="secondary" type="button" onClick={() => setModal(false)}>Annuler</Button>
            <Button type="submit">{editing ? 'Enregistrer' : 'Créer'}</Button>
          </div>
        </form>
      </Modal>
    </div>
  )
}
