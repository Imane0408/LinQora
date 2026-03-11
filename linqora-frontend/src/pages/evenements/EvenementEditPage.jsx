import { useEffect, useState } from 'react'
import { useParams, useNavigate, Link } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import toast from 'react-hot-toast'
import api from '@/lib/api'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Input from '@/components/ui/Input'
import Button from '@/components/ui/Button'
import { ChevronRight } from 'lucide-react'
import styles from './EvenementForm.module.css'

export default function EvenementEditPage() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const { register, handleSubmit, reset, watch, formState: { errors } } = useForm()
  const estPayant = watch('estPayant')

  useEffect(() => {
    api.get(`/evenements/${id}`).then(r => {
      const ev = r.data
      reset({
        ...ev,
        dateDebut: ev.dateDebut?.slice(0,16),
        dateFin:   ev.dateFin?.slice(0,16),
      })
    })
  }, [id])

  const onSubmit = async (data) => {
    setLoading(true)
    try {
      await api.put(`/evenements/${id}`, data)
      toast.success('Événement mis à jour !')
      navigate(`/evenements/${id}`)
    } catch (err) {
      toast.error(err.response?.data?.message || 'Erreur lors de la mise à jour')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <PageHeader
        title="Modifier l'événement"
        breadcrumb={<><Link to="/evenements">Événements</Link> <ChevronRight size={12}/> Modifier</>}
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <div className={styles.grid}>
          <Card title="Informations générales">
            <div className={styles.fields}>
              <Input label="Titre *" register={register} name="titre" required />
              <div className={styles.field}>
                <label className={styles.label}>Description</label>
                <textarea className={styles.textarea} {...register('description')} />
              </div>
              <div className={styles.row2}>
                <div className={styles.field}>
                  <label className={styles.label}>Mode</label>
                  <select className={styles.select} {...register('mode')}>
                    <option value="presentiel">Présentiel</option>
                    <option value="en_ligne">En ligne</option>
                    <option value="hybride">Hybride</option>
                  </select>
                </div>
                <div className={styles.field}>
                  <label className={styles.label}>Statut</label>
                  <select className={styles.select} {...register('statut')}>
                    <option value="brouillon">Brouillon</option>
                    <option value="publie">Publié</option>
                    <option value="archive">Archivé</option>
                    <option value="annule">Annulé</option>
                  </select>
                </div>
              </div>
              <div className={styles.row2}>
                <div className={styles.field}>
                  <label className={styles.label}>Date de début</label>
                  <input type="datetime-local" className={styles.input} {...register('dateDebut')} />
                </div>
                <div className={styles.field}>
                  <label className={styles.label}>Date de fin</label>
                  <input type="datetime-local" className={styles.input} {...register('dateFin')} />
                </div>
              </div>
              <Input label="Lieu" register={register} name="lieu" />
              <Input label="Lien en ligne" register={register} name="lienEnLigne" />
              <Input label="Capacité max" type="number" register={register} name="capaciteMax" />
            </div>
          </Card>
          <div>
            <Card title="Options">
              <div className={styles.fields}>
                <label className={styles.toggle}>
                  <input type="checkbox" {...register('estPayant')} />
                  <span className={styles.toggleSlider}/>
                  <span>Événement payant</span>
                </label>
                {estPayant && <Input label="Prix" type="number" register={register} name="prix" />}
                <label className={styles.toggle}>
                  <input type="checkbox" {...register('networkingActif')} />
                  <span className={styles.toggleSlider}/>
                  <span>Networking actif</span>
                </label>
              </div>
            </Card>
            <div className={styles.submitRow}>
              <Link to={`/evenements/${id}`}><Button variant="secondary" type="button">Annuler</Button></Link>
              <Button type="submit" loading={loading}>Enregistrer</Button>
            </div>
          </div>
        </div>
      </form>
    </div>
  )
}
