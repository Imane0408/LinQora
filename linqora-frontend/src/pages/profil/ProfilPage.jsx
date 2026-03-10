import { useState } from 'react'
import { useForm } from 'react-hook-form'
import toast from 'react-hot-toast'
import api from '@/lib/api'
import useAuthStore from '@/store/authStore'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Input from '@/components/ui/Input'
import Button from '@/components/ui/Button'
import Badge from '@/components/ui/Badge'
import { User, Lock, Save } from 'lucide-react'
import styles from './ProfilPage.module.css'

export default function ProfilPage() {
  const { user, refreshMe } = useAuthStore()
  const [loadingProfil, setLoadingProfil] = useState(false)
  const [loadingPass, setLoadingPass] = useState(false)

  const { register: rProfil, handleSubmit: hProfil } = useForm({
    defaultValues: { nom: user?.nom, prenom: user?.prenom }
  })
  const { register: rPass, handleSubmit: hPass, reset: resetPass } = useForm()

  const onProfil = async (data) => {
    setLoadingProfil(true)
    try {
      await api.put('/auth/profil', data)
      await refreshMe()
      toast.success('Profil mis à jour')
    } catch { toast.error('Erreur') }
    finally { setLoadingProfil(false) }
  }

  const onPassword = async (data) => {
    setLoadingPass(true)
    try {
      await api.put('/auth/password', data)
      toast.success('Mot de passe mis à jour')
      resetPass()
    } catch (err) { toast.error(err.response?.data?.message || 'Erreur') }
    finally { setLoadingPass(false) }
  }

  return (
    <div>
      <PageHeader title="Mon profil" subtitle="Gérez vos informations personnelles" />
      <div className={styles.grid}>
        {/* Avatar */}
        <Card>
          <div className={styles.avatarSection}>
            <div className={styles.bigAvatar}>{user?.prenom?.[0]}{user?.nom?.[0]}</div>
            <div className={styles.userInfo}>
              <div className={styles.userName}>{user?.prenom} {user?.nom}</div>
              <div className={styles.userEmail}>{user?.email}</div>
              <Badge label={user?.role} />
            </div>
            {user?.entreprise && (
              <div className={styles.entreprise}>
                <span>🏢</span> {user.entreprise.nom}
              </div>
            )}
          </div>
        </Card>

        {/* Formulaires */}
        <div className={styles.forms}>
          <Card title="Informations personnelles" action={<User size={16}/>}>
            <form onSubmit={hProfil(onProfil)} className={styles.form}>
              <div className={styles.row2}>
                <Input label="Prénom" register={rProfil} name="prenom" />
                <Input label="Nom"    register={rProfil} name="nom" />
              </div>
              <Button type="submit" loading={loadingProfil} icon={<Save size={14}/>}>Enregistrer</Button>
            </form>
          </Card>

          <Card title="Changer le mot de passe" action={<Lock size={16}/>}>
            <form onSubmit={hPass(onPassword)} className={styles.form}>
              <Input label="Mot de passe actuel" type="password" register={rPass} name="current_password" required />
              <Input label="Nouveau mot de passe" type="password" register={rPass} name="password" required />
              <Input label="Confirmer" type="password" register={rPass} name="password_confirmation" required />
              <Button type="submit" loading={loadingPass} icon={<Save size={14}/>}>Mettre à jour</Button>
            </form>
          </Card>
        </div>
      </div>
    </div>
  )
}
