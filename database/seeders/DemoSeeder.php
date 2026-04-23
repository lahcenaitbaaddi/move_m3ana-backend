<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use App\Models\Review;
use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Créer des données de démonstration pour le développement.
     */
    public function run(): void
    {
        // ─── Utilisateurs de démonstration ────────────────────────────
        $user1 = User::updateOrCreate(
            ['email' => 'lahcen@gmail.com'],
            [
                'name'              => 'Lahcen',
                'password'          => Hash::make('123456'),
                'role'              => 'user',
                'phone'             => '0612345678',
                'sport_preferences' => ['football', 'natation'],
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'fatima@gmail.com'],
            [
                'name'              => 'Fatima',
                'password'          => Hash::make('123456'),
                'role'              => 'user',
                'phone'             => '0698765432',
                'sport_preferences' => ['fitness', 'yoga'],
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // ─── Propriétaires de clubs ──────────────────────────────────
        $clubOwner1 = User::updateOrCreate(
            ['email' => 'club.fitness@tanger.com'],
            [
                'name'              => 'Club Fitness Tanger',
                'password'          => Hash::make('123456'),
                'role'              => 'club',
                'phone'             => '0539123456',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        $clubOwner2 = User::updateOrCreate(
            ['email' => 'club.football@tanger.com'],
            [
                'name'              => 'Club Football Tanger',
                'password'          => Hash::make('123456'),
                'role'              => 'club',
                'phone'             => '0539654321',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // ─── Clubs ───────────────────────────────────────────────────
        $club1 = Club::updateOrCreate(
            ['name' => 'Tanger Fitness'],
            [
                'user_id'      => $clubOwner1->id,
                'sport'        => 'fitness',
                'description'  => 'Le meilleur club de fitness à Tanger. Équipements modernes, coaching personnalisé et ambiance motivante.',
                'address'      => 'Av. Mohammed V, Tanger',
                'city'         => 'Tanger',
                'phone'        => '0539123456',
                'email'        => 'contact@tangerfitness.ma',
                'tarifs'       => '200dh/mois',
                'horaires'     => 'Lun-Sam: 6h-22h, Dim: 8h-18h',
                'latitude'     => 35.7595,
                'longitude'    => -5.8340,
                'is_approved'  => true,
                'is_active'    => true,
                'social_links' => ['facebook' => 'https://facebook.com/tangerfitness', 'instagram' => 'https://instagram.com/tangerfitness'],
            ]
        );

        $club2 = Club::updateOrCreate(
            ['name' => 'FC Tanger Academy'],
            [
                'user_id'      => $clubOwner2->id,
                'sport'        => 'football',
                'description'  => 'Académie de football pour tous les niveaux. Formation professionnelle et matchs réguliers.',
                'address'      => 'Stade Marchan, Tanger',
                'city'         => 'Tanger',
                'phone'        => '0539654321',
                'email'        => 'contact@fctanger.ma',
                'tarifs'       => '150dh/mois',
                'horaires'     => 'Lun-Sam: 8h-20h',
                'latitude'     => 35.7831,
                'longitude'    => -5.8132,
                'is_approved'  => true,
                'is_active'    => true,
                'social_links' => ['facebook' => 'https://facebook.com/fctanger'],
            ]
        );

        $club3 = Club::updateOrCreate(
            ['name' => 'Tanger Swimming Club'],
            [
                'user_id'      => $clubOwner1->id,
                'sport'        => 'natation',
                'description'  => 'Club de natation avec piscine olympique. Cours pour enfants et adultes.',
                'address'      => 'Route de Malabata, Tanger',
                'city'         => 'Tanger',
                'phone'        => '0539111222',
                'email'        => 'contact@tangerswim.ma',
                'tarifs'       => '300dh/mois',
                'horaires'     => 'Tous les jours: 7h-21h',
                'latitude'     => 35.7900,
                'longitude'    => -5.7900,
                'is_approved'  => false, // En attente d'approbation
                'is_active'    => true,
            ]
        );

        // ─── Événements ──────────────────────────────────────────────
        Event::updateOrCreate(
            ['title' => 'Tournoi Football Inter-Quartiers'],
            [
                'club_id'     => $club2->id,
                'description' => 'Grand tournoi de football ouvert à tous les quartiers de Tanger. Inscriptions ouvertes !',
                'date'        => now()->addDays(30),
                'location'    => 'Stade Marchan, Tanger',
                'capacity'    => 100,
            ]
        );

        Event::updateOrCreate(
            ['title' => 'Marathon Fitness Challenge'],
            [
                'club_id'     => $club1->id,
                'description' => 'Défi fitness de 4 heures avec des exercices variés. Prizes pour les meilleurs !',
                'date'        => now()->addDays(15),
                'location'    => 'Tanger Fitness, Av. Mohammed V',
                'capacity'    => 50,
            ]
        );

        Event::updateOrCreate(
            ['title' => 'Cours de Yoga en Plein Air'],
            [
                'club_id'     => $club1->id,
                'description' => 'Session de yoga gratuite sur la plage de Tanger. Tous niveaux bienvenus.',
                'date'        => now()->addDays(7),
                'location'    => 'Plage Municipale, Tanger',
                'capacity'    => 30,
            ]
        );

        // ─── Abonnements ─────────────────────────────────────────────
        Subscription::updateOrCreate(
            ['user_id' => $user1->id, 'club_id' => $club1->id]
        );
        Subscription::updateOrCreate(
            ['user_id' => $user1->id, 'club_id' => $club2->id]
        );
        Subscription::updateOrCreate(
            ['user_id' => $user2->id, 'club_id' => $club1->id]
        );

        // ─── Avis ────────────────────────────────────────────────────
        Review::updateOrCreate(
            ['user_id' => $user1->id, 'club_id' => $club1->id],
            [
                'rating'  => 5,
                'comment' => 'Excellent club ! Équipements top et coachs très professionnels.',
            ]
        );
        Review::updateOrCreate(
            ['user_id' => $user2->id, 'club_id' => $club1->id],
            [
                'rating'  => 4,
                'comment' => 'Très bon club, ambiance agréable. Un peu cher mais ça vaut le coup.',
            ]
        );
        Review::updateOrCreate(
            ['user_id' => $user1->id, 'club_id' => $club2->id],
            [
                'rating'  => 4,
                'comment' => 'Bonne académie de football. Les entraîneurs sont compétents.',
            ]
        );

        $this->command->info('✅ Données de démonstration créées avec succès.');
    }
}
