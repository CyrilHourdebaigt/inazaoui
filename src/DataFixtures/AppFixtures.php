<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }


    public function load(ObjectManager $manager): void
    {
        // Je crée l'administratrice pour tester le portfolio principal
        $admin = new User();
        $admin->setName('Ina Zaoui');
        $admin->setEmail('ina.test@example.fr');
        $admin->setDescription('Administratrice du site de test.');
        $admin->setAdmin(true);
        $admin->setActive(true);

        $manager->persist($admin);

        // Je crée un invité actif pour tester son affichage public
        $activeGuest = new User();
        $activeGuest->setName('Invité actif test');
        $activeGuest->setEmail('actif.test@example.fr');
        $activeGuest->setDescription('Profil utilisé pour les tests.');
        $activeGuest->setAdmin(false);
        $activeGuest->setActive(true);
        // Je définis un mot de passe haché pour mon invité actif de test
        $activeGuest->setPassword(
            $this->passwordHasher->hashPassword($activeGuest, 'TestPassword123!')
        );


        $manager->persist($activeGuest);

        // Je crée un invité bloqué pour tester la révocation de son accès
        $blockedGuest = new User();
        $blockedGuest->setName('Invité bloqué test');
        $blockedGuest->setEmail('bloque.test@example.fr');
        $blockedGuest->setDescription('Profil qui ne doit pas apparaître publiquement.');
        $blockedGuest->setAdmin(false);
        $blockedGuest->setActive(false);
        // Je définis un mot de passe haché pour mon invité bloqué de test
        $blockedGuest->setPassword(
            $this->passwordHasher->hashPassword($blockedGuest, 'TestPassword123!')
        );


        $manager->persist($blockedGuest);


        // Je crée un album pour tester le portfolio
        $album = new Album();
        $album->setName('Album de test');

        $manager->persist($album);

        // Je crée une photographie appartenant à l'invité actif
        $activeMedia = new Media();
        $activeMedia->setTitle('Photo invité actif');
        $activeMedia->setPath('uploads/photo-actif-test.jpg');
        $activeMedia->setUser($activeGuest);
        $activeMedia->setAlbum($album);

        $manager->persist($activeMedia);

        // Je crée une photographie appartenant à l'invité bloqué
        $blockedMedia = new Media();
        $blockedMedia->setTitle('Photo invité bloqué');
        $blockedMedia->setPath('uploads/photo-bloque-test.jpg');
        $blockedMedia->setUser($blockedGuest);
        $blockedMedia->setAlbum($album);

        $manager->persist($blockedMedia);

        // Je crée une photographie appartenant à Ina pour tester le portfolio principal
        $adminMedia = new Media();
        $adminMedia->setTitle('Photo portfolio Ina');
        $adminMedia->setPath('uploads/photo-ina-test.jpg');
        $adminMedia->setUser($admin);
        $adminMedia->setAlbum($album);

        $manager->persist($adminMedia);

        // J'enregistre mes invités, mon album et mes photographies dans la bdd
        $manager->flush();
    }
}
