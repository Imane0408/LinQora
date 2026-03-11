import { useState, useRef } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import api from '@/lib/api'
import PageHeader from '@/components/ui/PageHeader'
import Card from '@/components/ui/Card'
import Button from '@/components/ui/Button'
import Input from '@/components/ui/Input'
import Badge from '@/components/ui/Badge'
import { QrCode, CheckCircle, XCircle, AlertTriangle, Search } from 'lucide-react'
import styles from './PointagePage.module.css'

export default function PointagePage() {
  const [mode, setMode] = useState('evenement') // evenement | atelier
  const [code, setCode] = useState('')
  const [idAtelier, setIdAtelier] = useState('')
  const [result, setResult] = useState(null)
  const [loading, setLoading] = useState(false)

  const handleScan = async () => {
    if (!code.trim()) return
    setLoading(true)
    setResult(null)
    try {
      const endpoint = mode === 'evenement' ? '/pointage/evenement' : '/pointage/atelier'
      const payload = mode === 'evenement' ? { codeQr: code } : { codeQr: code, idAtelier: Number(idAtelier) }
      const r = await api.post(endpoint, payload)
      setResult({ ...r.data, type: r.data.success ? 'success' : 'doublon' })
    } catch (err) {
      const d = err.response?.data
      setResult({ success: false, alerte: d?.alerte || 'QR code invalide', type: d?.type || 'erreur', participant: d?.participant })
    } finally {
      setLoading(false)
    }
  }

  const reset = () => { setCode(''); setResult(null) }

  const ResultIcon = () => {
    if (result?.success)         return <CheckCircle size={48} color="var(--color-success)" />
    if (result?.type === 'doublon') return <AlertTriangle size={48} color="var(--color-warning)" />
    return <XCircle size={48} color="var(--color-danger)" />
  }

  return (
    <div>
      <PageHeader title="Scan QR — Pointage" subtitle="Scanner le code QR du badge participant" />

      <div className={styles.grid}>
        {/* Scanner */}
        <Card title="Scanner un QR code">
          <div className={styles.scanArea}>
            <div className={styles.qrIcon}><QrCode size={60} /></div>
            <div className={styles.modeToggle}>
              <button className={`${styles.modeBtn} ${mode === 'evenement' ? styles.active : ''}`} onClick={() => setMode('evenement')}>Entrée événement</button>
              <button className={`${styles.modeBtn} ${mode === 'atelier' ? styles.active : ''}`} onClick={() => setMode('atelier')}>Atelier</button>
            </div>
            <div className={styles.inputGroup}>
              <Input
                placeholder="Code QR ou saisie manuelle (LQ-XXXX)"
                value={code}
                onChange={e => setCode(e.target.value)}
                icon={<QrCode size={16}/>}
              />
              {mode === 'atelier' && (
                <Input
                  placeholder="ID Atelier"
                  type="number"
                  value={idAtelier}
                  onChange={e => setIdAtelier(e.target.value)}
                />
              )}
              <Button onClick={handleScan} loading={loading} fullWidth size="lg" icon={<Search size={16}/>}>
                Vérifier la présence
              </Button>
            </div>
          </div>
        </Card>

        {/* Résultat */}
        <AnimatePresence mode="wait">
          {result ? (
            <motion.div key="result" initial={{ opacity:0, scale:0.95 }} animate={{ opacity:1, scale:1 }} exit={{ opacity:0 }}>
              <Card>
                <div className={`${styles.result} ${styles[result.success ? 'success' : result.type === 'doublon' ? 'warning' : 'danger']}`}>
                  <ResultIcon />
                  <p className={styles.resultMsg}>{result.success ? '✅ ' + result.message : '⚠️ ' + result.alerte}</p>
                  {result.participant && (
                    <div className={styles.participantCard}>
                      <div className={styles.pAvatar}>{result.participant.prenom?.[0]}{result.participant.nom?.[0]}</div>
                      <div>
                        <div className={styles.pName}>{result.participant.prenom} {result.participant.nom}</div>
                        <div className={styles.pOrg}>{result.participant.organisation || result.participant.email}</div>
                      </div>
                    </div>
                  )}
                  {result.heureEntree && (
                    <div className={styles.timeStamp}>⏰ Entrée enregistrée à {new Date(result.heureEntree).toLocaleTimeString('fr-FR')}</div>
                  )}
                  {result.ateliers?.length > 0 && (
                    <div className={styles.ateliers}>
                      <p>Ateliers inscrits :</p>
                      {result.ateliers.map((a, i) => (
                        <span key={i} className={styles.atelierTag}>{a.titre} · {a.horaire}</span>
                      ))}
                    </div>
                  )}
                  <Button variant="secondary" onClick={reset} fullWidth>Scanner suivant</Button>
                </div>
              </Card>
            </motion.div>
          ) : (
            <motion.div key="empty" initial={{ opacity:0 }} animate={{ opacity:1 }}>
              <Card>
                <div className={styles.emptyResult}>
                  <div className={styles.qrPlaceholder}>
                    <QrCode size={64} />
                  </div>
                  <p>Entrez un code QR et cliquez sur Vérifier</p>
                </div>
              </Card>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </div>
  )
}
