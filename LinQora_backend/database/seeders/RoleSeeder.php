<?php
namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [Role::SUPER_ADMIN,      'Administrateur global de la plateforme LinQora'],
            [Role::ADMIN_ENTREPRISE, 'Administrateur d\'une entreprise cliente'],
            [Role::GESTIONNAIRE,     'Gestionnaire d\'événements de l\'entreprise'],
            [Role::PARTICIPANT,      'Participant aux événements'],
            [Role::OPERATEUR_SCAN,   'Opérateur de scan QR codes et pointage'],
        ];

        foreach ($roles as [$nomRole, $description]) {
            Role::firstOrCreate(
                ['nomRole' => $nomRole],
                ['description' => $description]
            );
        }

        $this->command->info('✅ Rôles créés : ' . implode(', ', array_column($roles, 0)));
    }
}
