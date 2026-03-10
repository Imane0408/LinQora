<?php
namespace Database\Seeders;

use App\Models\Entreprise;
use App\Models\Participant;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class EntrepriseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Super Admin global ─────────────────────────────────────────────
        $roleSA = Role::where('nomRole', Role::SUPER_ADMIN)->first();
        Utilisateur::firstOrCreate(
            ['email' => 'superadmin@linqora.com'],
            [
                'nom'        => 'Admin',
                'prenom'     => 'Super',
                'motDePasse' => bcrypt('LinQora@2024!'),
                'idRole'     => $roleSA->idRole,
                'actif'      => true,
            ]
        );

        // ── 2. Entreprise de démonstration ────────────────────────────────────
        $entreprise = Entreprise::firstOrCreate(
            ['nom' => 'LinQora Démo'],
            [
                'email'     => 'contact@demo.linqora.com',
                'telephone' => '+212600000000',
                'adresse'   => 'Casablanca, Maroc',
                'actif'     => true,
            ]
        );

        // ── 3. Admin Entreprise ───────────────────────────────────────────────
        $roleAdmin = Role::where('nomRole', Role::ADMIN_ENTREPRISE)->first();
        Utilisateur::firstOrCreate(
            ['email' => 'admin@demo.linqora.com'],
            [
                'nom'          => 'Demo',
                'prenom'       => 'Admin',
                'motDePasse'   => bcrypt('Demo@2024!'),
                'idRole'       => $roleAdmin->idRole,
                'idEntreprise' => $entreprise->idEntreprise,
                'actif'        => true,
            ]
        );

        // ── 4. Gestionnaire ───────────────────────────────────────────────────
        $roleGest = Role::where('nomRole', Role::GESTIONNAIRE)->first();
        Utilisateur::firstOrCreate(
            ['email' => 'gestionnaire@demo.linqora.com'],
            [
                'nom'          => 'Dupont',
                'prenom'       => 'Alice',
                'motDePasse'   => bcrypt('Gest@2024!'),
                'idRole'       => $roleGest->idRole,
                'idEntreprise' => $entreprise->idEntreprise,
                'actif'        => true,
            ]
        );

        // ── 5. Opérateur Scan ─────────────────────────────────────────────────
        $roleOp = Role::where('nomRole', Role::OPERATEUR_SCAN)->first();
        Utilisateur::firstOrCreate(
            ['email' => 'scan@demo.linqora.com'],
            [
                'nom'          => 'Martin',
                'prenom'       => 'Bob',
                'motDePasse'   => bcrypt('Scan@2024!'),
                'idRole'       => $roleOp->idRole,
                'idEntreprise' => $entreprise->idEntreprise,
                'actif'        => true,
            ]
        );

        // ── 6. Participant de démonstration ───────────────────────────────────
        $roleParticipant = Role::where('nomRole', Role::PARTICIPANT)->first();
        $userPart = Utilisateur::firstOrCreate(
            ['email' => 'participant@demo.linqora.com'],
            [
                'nom'        => 'Benali',
                'prenom'     => 'Yassine',
                'motDePasse' => bcrypt('Part@2024!'),
                'idRole'     => $roleParticipant->idRole,
                'actif'      => true,
            ]
        );
        Participant::firstOrCreate(
            ['idUtilisateur' => $userPart->idUtilisateur],
            ['telephone' => '+212655000001', 'organisation' => 'StartupMaroc']
        );

        // ── Affichage récapitulatif ───────────────────────────────────────────
        $this->command->info('');
        $this->command->info('✅ Tous les comptes ont été créés avec succès !');
        $this->command->info('');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                [Role::SUPER_ADMIN,      'superadmin@linqora.com',         'LinQora@2024!'],
                [Role::ADMIN_ENTREPRISE, 'admin@demo.linqora.com',          'Demo@2024!'],
                [Role::GESTIONNAIRE,     'gestionnaire@demo.linqora.com',   'Gest@2024!'],
                [Role::OPERATEUR_SCAN,   'scan@demo.linqora.com',           'Scan@2024!'],
                [Role::PARTICIPANT,      'participant@demo.linqora.com',     'Part@2024!'],
            ]
        );
    }
}
