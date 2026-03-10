<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel événement — LinQora</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f4f8; padding: 20px; }
        .wrapper { max-width: 620px; margin: 0 auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        .header { background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%); padding: 45px 35px; text-align: center; }
        .header .logo { font-size: 28px; font-weight: 800; color: #fff; letter-spacing: 2px; margin-bottom: 8px; }
        .header h1 { color: #fff; font-size: 22px; font-weight: 600; }
        .body { padding: 40px 35px; }
        .greeting { font-size: 17px; color: #1e293b; margin-bottom: 20px; }
        .info-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 20px 24px; margin: 20px 0; }
        .info-box p { color: #374151; font-size: 14px; margin: 6px 0; }
        .qr-box { text-align: center; margin: 24px 0; padding: 24px; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1; }
        .qr-code { font-family: 'Courier New', monospace; font-size: 22px; font-weight: 800; color: #0f172a; letter-spacing: 3px; background: #fff; padding: 12px 24px; border-radius: 8px; border: 2px solid #e2e8f0; display: inline-block; }
        .footer { background: #f8fafc; text-align: center; padding: 24px; border-top: 1px solid #e5e7eb; }
        .footer p { color: #94a3b8; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="header">
            <div class="logo">LinQora</div>
            <h1>⏰ Rappel — Événement à venir !</h1>
        </div>
        <div class="body">
            <p class="greeting">Bonjour <strong>{{ $participant->prenom }} {{ $participant->nom }}</strong>,</p>
            <p style="color:#4b5563; line-height:1.7; margin-bottom:20px;">
                Nous vous rappelons que vous êtes inscrit(e) à l'événement suivant qui approche.
                N'oubliez pas votre QR code d'accès !
            </p>
            <div class="info-box">
                <p>🎯 <strong>{{ $evenement->titre }}</strong></p>
                <p>📅 <strong>{{ $evenement->dateDebut->format('l d F Y à H:i') }}</strong></p>
                @if($evenement->lieu)
                <p>📍 {{ $evenement->lieu }}</p>
                @endif
                @if($evenement->lienEnLigne)
                <p>🔗 <a href="{{ $evenement->lienEnLigne }}" style="color:#2563eb;">Lien de connexion</a></p>
                @endif
            </div>
            <div class="qr-box">
                <p style="color:#64748b; font-size:13px; margin-bottom:14px;">Votre QR code d'accès</p>
                <div class="qr-code">{{ $inscription->codeQr }}</div>
                <p style="color:#94a3b8; font-size:12px; margin-top:10px;">Gardez ce code à portée de main</p>
            </div>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} LinQora · Cet email a été envoyé automatiquement</p>
        </div>
    </div>
</div>
</body>
</html>
