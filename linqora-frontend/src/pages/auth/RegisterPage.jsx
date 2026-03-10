import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import toast from 'react-hot-toast'
import useAuthStore from '@/store/authStore'
import Input from '@/components/ui/Input'
import Button from '@/components/ui/Button'
import { Mail, Lock, User, Phone, Building2 } from 'lucide-react'
import styles from './AuthPages.module.css'
import api from '@/lib/api'

export default function RegisterPage() {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const { login } = useAuthStore()
  const { register, handleSubmit, formState: { errors }, watch } = useForm()

  const onSubmit = async (data) => {
    setLoading(true)
    try {
      await api.post('/auth/register', {
        nom: data.nom, prenom: data.prenom,
        email: data.email, password: data.password,
        password_confirmation: data.password_confirmation,
        telephone: data.telephone, organisation: data.organisation,
      })
      await login(data.email, data.password)
      toast.success('Compte créé avec succès !')
      navigate('/dashboard')
    } catch (err) {
      const msgs = err.response?.data?.errors
      if (msgs) Object.values(msgs).flat().forEach(m => toast.error(m))
      else toast.error(err.response?.data?.message || 'Erreur lors de l\'inscription')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className={styles.wrap}>
      <div className={styles.heading}>
        <h2>Créer un compte</h2>
        <p>Rejoignez la plateforme LinQora</p>
      </div>
      <form onSubmit={handleSubmit(onSubmit)} className={styles.form}>
        <div className={styles.row2}>
          <Input label="Prénom" placeholder="Alice" icon={<User size={16}/>} register={register} name="prenom" required error={errors.prenom?.message} />
          <Input label="Nom" placeholder="Dupont" icon={<User size={16}/>} register={register} name="nom" required error={errors.nom?.message} />
        </div>
        <Input label="Email" type="email" placeholder="vous@exemple.com" icon={<Mail size={16}/>} register={register} name="email" required error={errors.email?.message} />
        <Input label="Téléphone" placeholder="+212600000000" icon={<Phone size={16}/>} register={register} name="telephone" error={errors.telephone?.message} />
        <Input label="Organisation" placeholder="Votre entreprise" icon={<Building2 size={16}/>} register={register} name="organisation" />
        <Input label="Mot de passe" type="password" placeholder="Min. 8 caractères" icon={<Lock size={16}/>} register={register} name="password" required error={errors.password?.message} />
        <Input label="Confirmer le mot de passe" type="password" placeholder="••••••••" icon={<Lock size={16}/>} register={register} name="password_confirmation" required />
        <Button type="submit" loading={loading} fullWidth size="lg">Créer mon compte</Button>
      </form>
      <p className={styles.link}>
        Déjà un compte ? <Link to="/login">Se connecter</Link>
      </p>
    </div>
  )
}
