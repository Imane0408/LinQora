import { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { motion } from 'framer-motion'
import toast from 'react-hot-toast'
import api from '@/lib/api'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Badge from '@/components/ui/Badge'
import { Calendar, MapPin, Users, Check, Clock, ExternalLink } from 'lucide-react'
import { format } from 'date-fns'
import { fr } from 'date-fns/locale'
import styles from './EventPublicPage.module.css'

export default function EventPublicPage() {
  const { slug } = useParams()
  const [ev, setEv] = useState(null)
  const [loading, setLoading] = useState(true)
  const [success, setSuccess] = useState(null)
  const [submitting, setSubmitting] = useState(false)
  const { register, handleSubmit, formState: { errors } } = useForm()

  useEffect(() => {
    api.get(`/evenements/slug/${slug}`).then(r => setEv(r.data)).catch(() => {}).finally(() => setLoading(false))
  }, [slug])

  const onSubmit = async (data) => {
    setSubmitting(true)
    try {
      const r = await api.post(`/evenements/${ev.idEvenement}/inscrire`, data)
      setSuccess(r.data)
      toast.success('Inscription réussie !')
    } catch (err) {
      const msgs = err.response?.data?.errors
      if (msgs) Object.values(msgs).flat().forEach(m => toast.error(m))
      else toast.error(err.response?.data?.message || 'Erreur')
    } finally {
      setSubmitting(false)
    }
  }

  if (loading) return (
    <div className={styles.page}>
      <div className={styles.loading}>Chargement…</div>
    </div>
  )
  if (!ev) return (
    <div className={styles.page}>
      <div className={styles.error}>Événement introuvable ou non disponible.</div>
    </div>
  )

  return (
    <div className={styles.page}>
      <div className={styles.bg}>
        <div className={styles.orb} />
      </div>

      <div className={styles.container}>
        {/* Header événement */}
        <motion.div className={styles.eventHeader} initial={{ opacity:0, y:20 }} animate={{ opacity:1, y:0 }}>
          <div className={styles.badges}><Badge label={ev.statut} /><Badge label={ev.mode} color="blue" /></div>
          <h1 className={styles.title}>{ev.titre}</h1>
          <div className={styles.meta}>
            <span><Calendar size={15}/> {format(new Date(ev.dateDebut), 'PPPP', { locale: fr })}</span>
            <span><Clock size={15}/> {format(new Date(ev.dateDebut), 'HH:mm')} — {format(new Date(ev.dateFin), 'HH:mm')}</span>
            {ev.lieu && <span><MapPin size={15}/> {ev.lieu}</span>}
          </div>
          {ev.description && <p className={styles.desc}>{ev.description}</p>}
          {ev.lienEnLigne && <a href={ev.lienEnLigne} target="_blank" rel="noreferrer" className={styles.lienBtn}><ExternalLink size={14}/> Rejoindre en ligne</a>}
        </motion.div>

        {/* Ateliers */}
        {ev.ateliers?.length > 0 && (
          <motion.div className={styles.ateliersSection} initial={{ opacity:0 }} animate={{ opacity:1 }} transition={{ delay:0.2 }}>
            <h2 className={styles.sectionTitle}>Programme</h2>
            <div className={styles.ateliersList}>
              {ev.ateliers.map(a => (
                <div key={a.idAtelier} className={styles.atelierItem}>
                  <div className={styles.atelierTime}>{format(new Date(a.horaire), 'HH:mm')}</div>
                  <div>
                    <div className={styles.atelierTitle}>{a.titre}</div>
                    <div className={styles.atelierMeta}>
                      {a.salle && <span>📍 {a.salle}</span>}
                      <span>⏱ {a.dureeMinutes} min</span>
                      {a.speakers?.map(s => <span key={s.idSpeaker} className={styles.chip}>{s.nomComplet}</span>)}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </motion.div>
        )}

        {/* Formulaire d'inscription */}
        <motion.div className={styles.formCard} initial={{ opacity:0, y:20 }} animate={{ opacity:1, y:0 }} transition={{ delay:0.3 }}>
          {success ? (
            <div className={styles.successBox}>
              <div className={styles.successIcon}><Check size={36} /></div>
              <h3>Inscription réussie ! 🎉</h3>
              <p>Un email de confirmation a été envoyé avec votre QR code d'accès.</p>
              <div className={styles.qrBox}>
                <div className={styles.qrLabel}>Votre code QR</div>
                <div className={styles.qrCode}>{success.codeQr}</div>
              </div>
            </div>
          ) : (
            <>
              <h2 className={styles.formTitle}>S'inscrire à cet événement</h2>
              {ev.estPayant && <div className={styles.prixBadge}>💰 Événement payant — {ev.prix} MAD</div>}
              <form onSubmit={handleSubmit(onSubmit)} className={styles.form}>
                <div className={styles.row2}>
                  <Input label="Prénom *" placeholder="Alice" register={register} name="prenom" required error={errors.prenom?.message} />
                  <Input label="Nom *" placeholder="Dupont" register={register} name="nom" required error={errors.nom?.message} />
                </div>
                <Input label="Email *" type="email" placeholder="vous@exemple.com" register={register} name="email" required error={errors.email?.message} />
                <Input label="Téléphone" placeholder="+212600000000" register={register} name="telephone" />
                <Input label="Organisation" placeholder="Votre entreprise" register={register} name="organisation" />
                {ev.ateliers?.length > 0 && (
                  <div className={styles.ateliersCheck}>
                    <label className={styles.checkLabel}>Ateliers souhaités</label>
                    {ev.ateliers.map(a => (
                      <label key={a.idAtelier} className={styles.checkItem}>
                        <input type="checkbox" value={a.idAtelier} {...register('ateliers')} />
                        <span>{a.titre} — {format(new Date(a.horaire), 'HH:mm')}</span>
                      </label>
                    ))}
                  </div>
                )}
                {ev.networkingActif && (
                  <label className={styles.checkItem}>
                    <input type="checkbox" {...register('networkingActif')} />
                    <span>Activer le networking (visible des autres participants)</span>
                  </label>
                )}
                <Button type="submit" loading={submitting} fullWidth size="lg">
                  {ev.estPayant ? `S'inscrire (${ev.prix} MAD)` : 'S\'inscrire gratuitement'}
                </Button>
              </form>
            </>
          )}
        </motion.div>
      </div>
    </div>
  )
}
