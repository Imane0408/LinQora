<?php
namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Inscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    /** GET /api/evenements/{evenement}/export/excel */
    public function exportExcel(Evenement $evenement): Response
    {
        $inscriptions = $evenement->inscriptions()
            ->with(['participant.utilisateur', 'ateliers'])
            ->where('statut', 'confirme')
            ->get();

        // BOM UTF-8 pour Excel
        $bom = "\xEF\xBB\xBF";
        $csv = $bom . "Nom,Prénom,Email,Organisation,Téléphone,Statut,Présent,Payé,Ateliers,Date inscription\n";

        foreach ($inscriptions as $i) {
            $u        = $i->participant->utilisateur;
            $ateliers = $i->ateliers->pluck('titre')->implode(' | ');
            $csv     .= implode(',', [
                '"' . $u->nom . '"',
                '"' . $u->prenom . '"',
                '"' . $u->email . '"',
                '"' . ($i->participant->organisation ?? '') . '"',
                '"' . ($i->participant->telephone ?? '') . '"',
                $i->statut,
                $i->presentGlobal ? 'Oui' : 'Non',
                $i->aPaye ? 'Oui' : 'Non',
                '"' . $ateliers . '"',
                $i->dateInscr->format('d/m/Y H:i'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"inscriptions_{$evenement->slug}.csv\"",
        ]);
    }

    /** GET /api/evenements/{evenement}/export/pdf */
    public function exportPdf(Evenement $evenement)
    {
        // Nécessite barryvdh/laravel-dompdf (déjà dans composer.json)
        $inscriptions = $evenement->inscriptions()
            ->with(['participant.utilisateur', 'ateliers'])
            ->where('statut', 'confirme')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.liste-inscrits', [
            'evenement'    => $evenement,
            'inscriptions' => $inscriptions,
            'genereeLe'    => now()->format('d/m/Y à H:i'),
        ]);

        return $pdf->download("inscrits_{$evenement->slug}.pdf");
    }

    /** GET /api/inscriptions/{inscription}/badge */
    public function exportBadge(Inscription $inscription)
    {
        // Générer QR code PNG avec simplesoftwareio/simple-qrcode
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->generate($inscription->codeQr);

        return response($qrCode, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => "inline; filename=\"badge_{$inscription->codeQr}.png\"",
        ]);
    }
}
