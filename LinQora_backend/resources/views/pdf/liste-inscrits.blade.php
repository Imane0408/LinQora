<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste inscrits — {{ $evenement->titre }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }
        h1 { font-size: 18px; color: #1d4ed8; margin-bottom: 5px; }
        .meta { color: #6b7280; font-size: 10px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        thead tr { background: #2563eb; color: #fff; }
        th { padding: 8px 10px; text-align: left; font-size: 10px; }
        tbody tr:nth-child(even) { background: #f3f4f6; }
        td { padding: 7px 10px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .present { background: #dcfce7; color: #15803d; }
        .absent  { background: #fee2e2; color: #b91c1c; }
        .footer  { margin-top: 20px; text-align: center; color: #9ca3af; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ $evenement->titre }}</h1>
    <div class="meta">
        📅 {{ $evenement->dateDebut->format('d/m/Y à H:i') }}
        @if($evenement->lieu) · 📍 {{ $evenement->lieu }} @endif
        · Généré le {{ $genereeLe }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Organisation</th>
                <th>Présent</th>
                <th>Payé</th>
                <th>Code QR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inscriptions as $i => $insc)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $insc->participant->utilisateur->nom }}</td>
                <td>{{ $insc->participant->utilisateur->prenom }}</td>
                <td>{{ $insc->participant->utilisateur->email }}</td>
                <td>{{ $insc->participant->organisation ?? '—' }}</td>
                <td>
                    <span class="badge {{ $insc->presentGlobal ? 'present' : 'absent' }}">
                        {{ $insc->presentGlobal ? 'Oui' : 'Non' }}
                    </span>
                </td>
                <td>{{ $insc->aPaye ? 'Oui' : 'Non' }}</td>
                <td>{{ $insc->codeQr }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Total : {{ $inscriptions->count() }} inscrits ·
        Présents : {{ $inscriptions->where('presentGlobal', true)->count() }} ·
        LinQora © {{ date('Y') }}
    </div>
</body>
</html>
