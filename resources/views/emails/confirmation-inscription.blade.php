<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'inscription — LinQora</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f4f8; padding: 20px; }
        .wrapper { max-width: 620px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        /* Header */
        .header { background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%); padding: 45px 35px; text-align: center; }
        .header .logo { font-size: 28px; font-weight: 800; color: #fff; letter-spacing: 2px; margin-bottom: 8px; }
        .header h1 { color: #fff; font-size: 22px; font-weight: 600; }
        .header p { color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 6px; }
        /* Body */
        .body { padding: 40px 35px; }
        .greeting { font-size: 17px; color: #1e293b; margin-bottom: 20px; }
        /* Event card */
        .event-card { background: linear-gradient(135deg, #eff6ff, #f5f3ff); border-radius: 12px; padding: 24px; margin: 24px 0; border: 1px solid #c7d2fe; }
        .event-card h2 { color: #1d4ed8; font-size: 20px; margin-bottom: 14px; }
        .event-card .info-row { display: flex; align-items: center; gap: 10px; margin: 8px 0; color: #374151; font-size: 14px; }
        .event-card .icon { font-size: 16px; }
        /* Status badge */
        .badge { display: inline-block; padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-confirme { background: #dcfce7; color: #15803d; }
        .badge-attente  { background: #fef9c3; color: #854d0e; }
        /* QR Code section */
        .qr-section { text-align: center; margin: 30px 0; padding: 30px; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1; }
        .qr-label { color: #64748b; font-size: 13px; margin-bottom: 16px; }
        .qr-code { font-family: 'Courier New', monospace; font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: 4px; background: #ffffff; padding: 14px 28px; border-radius: 10px; border: 2px solid #e2e8f0; display: inline-block; }
        .qr-hint { color: #94a3b8; font-size: 12px; margin-top: 12px; }
        /* Ateliers */
        .ateliers { margin-top: 28px; }
        .ateliers h3 { font-size: 16px; color: #374151; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb; }
        .atelier-item { display: flex; align-items: flex-start; gap: 12px; background: #f0fdf4; border-left: 4px solid #22c55e; border-radius: 8px; padding: 12px 16px; margin: 10px 0; }
        .atelier-item .atelier-info strong { color: #166534; font-size: 14px; }
        .atelier-item .atelier-meta { color: #6b7280; font-size: 12px; margin-top: 3px; }
        /* Footer */
        .footer { background: #f8fafc; text-align: center; padding: 24px 35px; border-top: 1px solid #e5e7eb; }
        .footer p { color: #94a3b8; font-size: 12px; line-height: 1.6; }
        .footer .brand { font-weight: 700; color: #6366f1; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <!-- Header -->
        <div class="header">
            <div class="logo">LinQora</div>
            <h1>🎉 Inscription confirmée !</h1>
            <p>Votre place est réservée</p>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">
                Bonjour <strong>{{ $prenom }} {{ $nom }}</strong>,
            </p>
            <p style="color:#4b5563; line-height:1.7; margin-bottom:5px;">
                Votre inscription à l'événement suivant a bien été enregistrée.
                Retrouvez ci-dessous tous les détails ainsi que votre QR code d'accès.
            </p>

            <!-- Event Card -->
            <div class="event-card">
                <h2>{{ $evenement->titre }}</h2>
                <div class="info-row">
                    <span class="icon">📅</span>
                    <span>{{ $evenement->dateDebut->format('l d F Y') }} à {{ $evenement->dateDebut->format('H:i') }}</span>
                </div>
                @if($evenement->dateFin)
                <div class="info-row">
                    <span class="icon">⏱️</span>
                    <span>Jusqu'au {{ $evenement->dateFin->format('d F Y à H:i') }}</span>
                </div>
                @endif
                @if($evenement->lieu)
                <div class="info-row">
                    <span class="icon">📍</span>
                    <span>{{ $evenement->lieu }}</span>
                </div>
                @endif
                @if($evenement->lienEnLigne)
                <div class="info-row">
                    <span class="icon">🔗</span>
                    <span><a href="{{ $evenement->lienEnLigne }}" style="color:#2563eb;">Rejoindre en ligne</a></span>
                </div>
                @endif
                <div class="info-row" style="margin-top:14px;">
                    <span class="icon">📋</span>
                    <span>
                        <span class="badge {{ $statut === 'confirme' ? 'badge-confirme' : 'badge-attente' }}">
                            {{ $statut === 'confirme' ? '✓ Confirmée' : '⏳ En attente' }}
                        </span>
                    </span>
                </div>
            </div>

            <!-- QR Code -->
            <div class="qr-section">
                <p class="qr-label">🎫 Votre QR code d'accès personnel</p>
                <div class="qr-code">{{ $codeQr }}</div>
                <p class="qr-hint">Présentez ce code à l'entrée de l'événement et à chaque atelier</p>
            </div>

            <!-- Ateliers -->
            @if($ateliers && $ateliers->isNotEmpty())
            <div class="ateliers">
                <h3>🎯 Vos ateliers sélectionnés ({{ $ateliers->count() }})</h3>
                @foreach($ateliers as $atelier)
                <div class="atelier-item">
                    <span style="font-size:20px;">🎓</span>
                    <div class="atelier-info">
                        <strong>{{ $atelier->titre }}</strong>
                        <div class="atelier-meta">
                            🕐 {{ $atelier->horaire->format('d/m/Y à H:i') }}
                            @if($atelier->salle) · 📌 Salle {{ $atelier->salle }} @endif
                            @if($atelier->dureeMinutes) · ⏱ {{ $atelier->dureeMinutes }} min @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Cet email a été envoyé automatiquement par <span class="brand">LinQora</span>.</p>
            <p>Ne pas répondre à cet email · © {{ date('Y') }} LinQora · Tous droits réservés</p>
        </div>
    </div>
</div>
</body>
</html>
