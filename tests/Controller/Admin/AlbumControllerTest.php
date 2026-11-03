<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlbumControllerTest extends WebTestCase
{
    // Je vérifie qu'un admin peut consulter la liste des albums
    public function testAdminCanAccessAlbumList(): void
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

        // Je visite la liste des albums
        $client->request('GET', '/admin/album');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que notre album de test apparaît
        $this->assertSelectorTextContains('body', 'Album de test');
    }


    // Je vérifie qu'un invité ne peut pas accéder à la gestion des albums
    public function testGuestCannotAccessAlbumList(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère un invité actif dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que l'invité existe
        $this->assertNotNull($guest);

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je tente d'accéder à la gestion des albums
        $client->request('GET', '/admin/album');

        // Je vérifie que l'accès est interdit
        $this->assertResponseStatusCodeSame(403);
    }


    // Je vérifie qu'un admin peut créer un nouvel album
    public function testAdminCanCreateAlbum(): void
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

        // Je visite le formulaire de création d'un album
        $crawler = $client->request('GET', '/admin/album/add');

        // Je récupère le formulaire
        $form = $crawler->filter('form')->form();

        // Je renseigne le nom de mon nouvel album
        $form['album[name]'] = 'Nouvel album test';

        // J'envoie le formulaire
        $client->submit($form);

        // Je vérifie que je suis redirigé vers la liste des albums
        $this->assertResponseRedirects('/admin/album');

        // Je récupère Doctrine après l'envoi du formulaire
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je recherche mon nouvel album dans la base de test
        $newAlbum = $entityManager
            ->getRepository(\App\Entity\Album::class)
            ->findOneBy(['name' => 'Nouvel album test']);

        // Je vérifie que mon album a bien été enregistré
        $this->assertNotNull($newAlbum);
    }


    // Je vérifie qu'un admin peut modifier le nom d'un album
    public function testAdminCanUpdateAlbum(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin et l'album dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        $album = $entityManager
            ->getRepository(\App\Entity\Album::class)
            ->findOneBy(['name' => 'Album de test']);

        // Je vérifie que l'admin et l'album existent
        $this->assertNotNull($admin);
        $this->assertNotNull($album);

        // Je récupère l'identifiant de l'album
        $albumId = $album->getId();

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite le formulaire de modification de l'album
        $crawler = $client->request('GET', '/admin/album/update/' . $albumId);

        // Je récupère le formulaire
        $form = $crawler->filter('form')->form();

        // Je modifie le nom de l'album
        $form['album[name]'] = 'Album modifié test';

        // J'envoie le formulaire
        $client->submit($form);

        // Je vérifie que je suis redirigé vers la liste des albums
        $this->assertResponseRedirects('/admin/album');

        // Je récupère Doctrine après l'envoi du formulaire
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je recherche l'album avec son nouveau nom
        $updatedAlbum = $entityManager
            ->getRepository(\App\Entity\Album::class)
            ->findOneBy(['name' => 'Album modifié test']);

        // Je vérifie que la modification a bien été enregistrée
        $this->assertNotNull($updatedAlbum);
    }


    // Je vérifie qu'un admin peut supprimer un album vide
    public function testAdminCanDeleteEmptyAlbum(): void
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

        // Je crée un album vide pour ce test
        $album = new \App\Entity\Album();
        $album->setName('Album à supprimer');

        $entityManager->persist($album);
        $entityManager->flush();

        // Je récupère l'identifiant de mon album
        $albumId = $album->getId();

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite la liste des albums
        $crawler = $client->request('GET', '/admin/album');

        // Je récupère le formulaire de suppression de mon album
        $form = $crawler
            ->filter('form[action="/admin/album/delete/' . $albumId . '"]')
            ->form();

        // J'envoie le formulaire avec son jeton CSRF
        $client->submit($form);

        // Je vérifie que je suis redirigé vers la liste des albums
        $this->assertResponseRedirects('/admin/album');

        // Je récupère Doctrine après la suppression
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je recherche l'album supprimé
        $deletedAlbum = $entityManager
            ->getRepository(\App\Entity\Album::class)
            ->find($albumId);

        // Je vérifie que l'album n'existe plus
        $this->assertNull($deletedAlbum);
    }
}
