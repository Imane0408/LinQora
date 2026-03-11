import { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import toast from 'react-hot-toast'
import api from '@/lib/api'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Input from '@/components/ui/Input'
import Button from '@/components/ui/Button'
import { ChevronRight } from 'lucide-react'
import styles from './EvenementForm.module.css'

export default function EvenementCreatePage() {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const { register, handleSubmit, watch, formState: { errors } } = useForm({
    defaultValues: { mode: 'presentiel', estPayant: false, networkingActif: false, statut: 'brouillon' }
  })

  const estPayant = watch('estPayant')

  const onSubmit = async (data) => {
    setLoading(true)
    try {
      const r = await api.post('/evenements', data)
      toast.success('Événement créé !')
      navigate(`/evenements/${r.data.idEvenement}`)
    } catch (err) {
      const errs = err.response?.data?.errors
      if (errs) Object.values(errs).flat().forEach(m => toast.error(m))
      else toast.error('Erreur lors de la création')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <PageHeader
        title="Créer un événement"
        breadcrumb={<><Link to="/evenements">Événements</Link> <ChevronRight size={12}/> Nouveau</>}
      />
      <form onSubmit={handleSubmit(onSubmit)}>
        <div className={styles.grid}>
          <Card title="Informations générales">
            <div className={styles.fields}>
              <Input label="Titre de l'événement *" placeholder="Ex: Conférence Tech 2025" register={register} name="titre" required error={errors.titre?.message} />
              <div className={styles.field}>
                <label className={styles.label}>Description</label>
                <textarea className={styles.textarea} placeholder="Décrivez votre événement..." {...register('description')} />
              </div>
              <div className={styles.row2}>
                <div className={styles.field}>
                  <label className={styles.label}>Mode *</label>
                  <select className={styles.select} {...register('mode', { required: true })}>
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
                  </select>
                </div>
              </div>
              <div className={styles.row2}>
                <div className={styles.field}>
                  <label className={styles.label}>Date de début *</label>
                  <input type="datetime-local" className={styles.input} {...register('dateDebut', { required: true })} />
                </div>
                <div className={styles.field}>
                  <label className={styles.label}>Date de fin *</label>
                  <input type="datetime-local" className={styles.input} {...register('dateFin', { required: true })} />
                </div>
              </div>
              <Input label="Lieu" placeholder="Adresse / salle" register={register} name="lieu" />
              <Input label="Lien en ligne" placeholder="https://meet.google.com/..." register={register} name="lienEnLigne" />
              <Input label="Capacité maximale" type="number" placeholder="200" register={register} name="capaciteMax" />
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
                {estPayant && (
                  <Input label="Prix (MAD)" type="number" placeholder="0.00" register={register} name="prix" />
                )}
                <label className={styles.toggle}>
                  <input type="checkbox" {...register('networkingActif')} />
                  <span className={styles.toggleSlider}/>
                  <span>Activer le networking</span>
                </label>
              </div>
            </Card>
            <div className={styles.submitRow}>
              <Link to="/evenements"><Button variant="secondary" type="button">Annuler</Button></Link>
              <Button type="submit" loading={loading}>Créer l'événement</Button>
            </div>
          </div>
        </div>
      </form>
    </div>
  )
}
