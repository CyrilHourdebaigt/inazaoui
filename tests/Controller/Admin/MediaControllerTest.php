<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MediaControllerTest extends WebTestCase
{
    // Je vérifie qu'un admin peut consulter toutes les photos
    public function testAdminCanAccessMediaList(): void
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

        // Je visite la liste des photos
        $client->request('GET', '/admin/media');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que les photos des invités apparaissent
        $this->assertSelectorTextContains('body', 'Photo invité actif');
        $this->assertSelectorTextContains('body', 'Photo invité bloqué');

        // Je vérifie que la photo d'Ina apparaît également
        $this->assertSelectorTextContains('body', 'Photo portfolio Ina');
    }


    // Je vérifie qu'un invité ne peut consulter que ses propres photos
    public function testGuestCanOnlySeeOwnMedias(): void
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

        // Je visite la liste des photos
        $client->request('GET', '/admin/media');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que sa photo apparaît
        $this->assertSelectorTextContains('body', 'Photo invité actif');

        // Je vérifie que les photos des autres utilisateurs sont absentes
        $this->assertSelectorTextNotContains('body', 'Photo invité bloqué');
        $this->assertSelectorTextNotContains('body', 'Photo portfolio Ina');
    }


    // Je vérifie qu'un admin peut accéder au formulaire d'ajout d'une photo
    public function testAdminCanAccessMediaCreationForm(): void
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

        // Je visite le formulaire d'ajout d'une photo
        $client->request('GET', '/admin/media/add');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que le formulaire est présent
        $this->assertSelectorExists('form');

        // Je vérifie que les champs attendus sont présents
        $this->assertSelectorExists('input[name="media[file]"]');
        $this->assertSelectorExists('input[name="media[title]"]');
        $this->assertSelectorExists('select[name="media[user]"]');
        $this->assertSelectorExists('select[name="media[album]"]');
    }


    // Je vérifie qu'un invité peut supprimer sa propre photo
    public function testGuestCanDeleteOwnMedia(): void
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

        // Je récupère sa photo de test
        $media = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->findOneBy(['title' => 'Photo invité actif']);

        // Je vérifie que la photo existe et appartient à cet invité
        $this->assertNotNull($media);
        $this->assertSame($guest->getId(), $media->getUser()->getId());

        // Je conserve l'identifiant de la photo
        $mediaId = $media->getId();

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je visite la liste de ses photo
        $crawler = $client->request('GET', '/admin/media');

        // Je récupère le formulaire de suppression de sa photo
        $form = $crawler
            ->filter('form[action="/admin/media/delete/' . $mediaId . '"]')
            ->form();

        // J'envoie le formulaire avec son jeton CSRF
        $client->submit($form);

        // Je vérifie que l'application me redirige vers la liste des médias
        $this->assertResponseRedirects('/admin/media');

        // Je récupère Doctrine après la suppression
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je recherche la photo supprimée
        $deletedMedia = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->find($mediaId);

        // Je vérifie que la photo n'existe plus
        $this->assertNull($deletedMedia);
    }


    // Je vérifie qu'un invité ne peut pas supprimer la photo d'un autre utilisateur
    public function testGuestCannotDeleteAnotherUsersMedia(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité et une photo appartenant à Ina
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        $media = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->findOneBy(['title' => 'Photo portfolio Ina']);

        // Je vérifie que l'invité et la photo existent
        $this->assertNotNull($guest);
        $this->assertNotNull($media);

        // Je conserve l'identifiant de la photo
        $mediaId = $media->getId();

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je tente de supprimer directement la photo d'Ina
        $client->request('POST', '/admin/media/delete/' . $mediaId);

        // Je vérifie que l'accès est interdit
        $this->assertResponseStatusCodeSame(403);

        // Je récupère Doctrine après la requête
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je vérifie que la photo existe toujours
        $savedMedia = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->find($mediaId);

        $this->assertNotNull($savedMedia);
    }
}
