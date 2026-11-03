<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GuestControllerTest extends WebTestCase
{
    // Je vérifie qu'un visiteur non connecté ne peut pas accéder à la gestion des invités
    public function testVisitorCannotAccessGuestAdministration(): void
    {
        // Je crée un navigateur pour simuler un visiteur non connecté
        $client = static::createClient();

        // Je tente d'accéder à la gestion des invités
        $client->request('GET', '/admin/guest');

        // Je vérifie que le visiteur est redirigé vers la connexion
        $this->assertResponseRedirects();
    }

    // Je vérifie qu'un visiteur non connecté ne peut pas accéder à la création d'un invité
    public function testVisitorCannotAccessGuestCreation(): void
    {
        // Je crée un navigateur pour simuler un visiteur non connecté
        $client = static::createClient();

        // Je tente d'accéder au formulaire de création
        $client->request('GET', '/admin/guest/add');

        // Je vérifie que le visiteur est redirigé
        $this->assertResponseRedirects();
    }

    // Je vérifie qu'un admin peut accéder à la gestion des invités
    public function testAdminCanAccessGuestAdministration(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        // Je vérifie que l'admin existe
        $this->assertNotNull($admin);

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite la gestion des invités
        $client->request('GET', '/admin/guest');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que notre invité actif apparaît
        $this->assertSelectorTextContains('body', 'Invité actif test');
    }

    // Je vérifie qu'un invité connecté ne peut pas accéder à la gestion des invités
    public function testGuestCannotAccessGuestAdministration(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité actif dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que l'invité existe
        $this->assertNotNull($guest);

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je tente d'accéder à la gestion des invités
        $client->request('GET', '/admin/guest');

        // Je vérifie que l'accès est interdit
        $this->assertResponseStatusCodeSame(403);
    }

    // Je vérifie qu'un admin peut accéder au formulaire de création d'un invité
    public function testAdminCanAccessGuestCreationForm(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        // Je vérifie que l'admin existe
        $this->assertNotNull($admin);

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite le formulaire de création d'un invité
        $client->request('GET', '/admin/guest/add');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que le formulaire est présent
        $this->assertSelectorExists('form');
    }

    // Je vérifie qu'un admin peut créer un invité avec les bons droits et un mot de passe haché
    public function testAdminCanCreateGuest(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        // Je vérifie que l'admin existe
        $this->assertNotNull($admin);

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite le formulaire de création d'un invité
        $crawler = $client->request('GET', '/admin/guest/add');

        // Je récupère le formulaire
        $form = $crawler->filter('form')->form();

        // Je renseigne les informations du nouvel invité
        $form['guest[name]'] = 'Nouvel invité test';
        $form['guest[email]'] = 'nouveau.test@example.fr';
        $form['guest[password]'] = 'MotDePasse123';

        // J'envoie le formulaire
        $client->submit($form);

        // Je vérifie que la création entraîne une redirection
        $this->assertResponseRedirects('/admin/guest');

        // Je récupère Doctrine après l'envoi du formulaire
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je recherche le nouvel invité dans ma base de test
        $newGuest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'nouveau.test@example.fr']);

        // Je vérifie que l'invité a bien été enregistré
        $this->assertNotNull($newGuest);

        // Je vérifie que l'invité n'est pas admin
        $this->assertFalse($newGuest->isAdmin());

        // Je vérifie que le mot de passe n'est pas enregistré en clair
        $this->assertNotSame('MotDePasse123', $newGuest->getPassword());

        // Je laisse DAMA annuler les modifications à la fin du test
    }

    // Je vérifie qu'un admin peut bloquer un invité depuis la gestion des invités
    public function testAdminCanBlockGuest(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin et l'invité actif dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que les deux utilisateurs existent
        $this->assertNotNull($admin);
        $this->assertNotNull($guest);

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite la liste des invités
        $crawler = $client->request('GET', '/admin/guest');

        // Je récupère le formulaire de blocage de notre invité actif
        $form = $crawler
            ->filter('form[action="/admin/guest/toggle/' . $guest->getId() . '"]')
            ->form();

        // J'envoie le formulaire avec son jeton CSRF
        $client->submit($form);

        // Je vérifie que je suis redirigé vers la liste des invités
        $this->assertResponseRedirects('/admin/guest');

        // Je récupère Doctrine après l'envoi du formulaire
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je récupère à nouveau l'invité dans ma base de test
        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que l'invité existe toujours
        $this->assertNotNull($guest);

        // Je vérifie que le compte est maintenant bloqué
        $this->assertFalse($guest->isActive());
    }


    // Je vérifie qu'un admin peut réactiver un invité bloqué
    public function testAdminCanReactivateGuest(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin et l'invité bloqué dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'bloque.test@example.fr']);

        // Je vérifie que les deux utilisateurs existent
        $this->assertNotNull($admin);
        $this->assertNotNull($guest);

        // Je vérifie que l'invité est initialement bloqué
        $this->assertFalse($guest->isActive());

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite la liste des invités
        $crawler = $client->request('GET', '/admin/guest');

        // Je récupère le formulaire de réactivation de cet invité
        $form = $crawler
            ->filter('form[action="/admin/guest/toggle/' . $guest->getId() . '"]')
            ->form();

        // J'envoie le formulaire pour réactiver l'invité
        $client->submit($form);

        // Je vérifie que je suis redirigé vers la liste des invités
        $this->assertResponseRedirects('/admin/guest');

        // Je récupère Doctrine après l'envoi du formulaire
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je récupère à nouveau l'invité dans ma base de test
        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'bloque.test@example.fr']);

        // Je vérifie que l'invité existe toujours
        $this->assertNotNull($guest);

        // Je vérifie que son compte est maintenant actif
        $this->assertTrue($guest->isActive());
    }


    // Je vérifie qu'un admin peut supprimer un invité et ses photographies
    public function testAdminCanDeleteGuest(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin et l'invité à supprimer
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que les deux utilisateurs existent
        $this->assertNotNull($admin);
        $this->assertNotNull($guest);

        // Je récupère l'identifiant de l'invité avant sa suppression
        $guestId = $guest->getId();

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite la liste des invités
        $crawler = $client->request('GET', '/admin/guest');

        // Je sélectionne le formulaire de suppression du bon invité
        $form = $crawler
            ->filter('form[action="/admin/guest/delete/' . $guestId . '"]')
            ->form();

        // J'envoie le formulaire avec son jeton CSRF
        $client->submit($form);

        // Je vérifie que je suis redirigé vers la liste des invités
        $this->assertResponseRedirects('/admin/guest');

        // Je récupère Doctrine après la suppression
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je recherche l'invité supprimé
        $deletedGuest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que l'invité n'existe plus
        $this->assertNull($deletedGuest);

        // Je vérifie que sa photographie a également été supprimée
        $deletedMedia = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->findOneBy(['title' => 'Photo invité actif']);

        $this->assertNull($deletedMedia);
    }
}
