import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import toast from 'react-hot-toast'
import useAuthStore from '@/store/authStore'
import Input from '@/components/ui/Input'
import Button from '@/components/ui/Button'
import { Mail, Lock } from 'lucide-react'
import styles from './AuthPages.module.css'

export default function LoginPage() {
  const { login } = useAuthStore()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const { register, handleSubmit, formState: { errors } } = useForm()

  const onSubmit = async (data) => {
    setLoading(true)
    try {
      await login(data.email, data.password)
      toast.success('Connexion réussie !')
      navigate('/dashboard')
    } catch (err) {
      toast.error(err.response?.data?.message || 'Identifiants invalides')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className={styles.wrap}>
      <div className={styles.heading}>
        <h2>Bon retour 👋</h2>
        <p>Connectez-vous à votre espace LinQora</p>
      </div>
      <form onSubmit={handleSubmit(onSubmit)} className={styles.form}>
        <Input
          label="Adresse email"
          type="email"
          placeholder="vous@exemple.com"
          icon={<Mail size={16} />}
          register={register}
          name="email"
          required
          error={errors.email?.message}
        />
        <Input
          label="Mot de passe"
          type="password"
          placeholder="••••••••"
          icon={<Lock size={16} />}
          register={register}
          name="password"
          required
          error={errors.password?.message}
        />
        <Button type="submit" loading={loading} fullWidth size="lg">
          Se connecter
        </Button>
      </form>
      <p className={styles.link}>
        Pas encore de compte ? <Link to="/register">S'inscrire</Link>
      </p>
    </div>
  )
}
