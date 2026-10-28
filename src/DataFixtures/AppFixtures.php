<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Je crée un invité actif pour tester son affichage public
        $activeGuest = new User();
        $activeGuest->setName('Invité actif test');
        $activeGuest->setEmail('actif.test@example.fr');
        $activeGuest->setDescription('Profil utilisé pour les tests.');
        $activeGuest->setAdmin(false);
        $activeGuest->setActive(true);

        $manager->persist($activeGuest);

        // Je crée un invité bloqué pour tester la révocation de son accès
        $blockedGuest = new User();
        $blockedGuest->setName('Invité bloqué test');
        $blockedGuest->setEmail('bloque.test@example.fr');
        $blockedGuest->setDescription('Profil qui ne doit pas apparaître publiquement.');
        $blockedGuest->setAdmin(false);
        $blockedGuest->setActive(false);

        $manager->persist($blockedGuest);

        // J'enregistre mes deux invités dans la bdd
        $manager->flush();
    }
}
