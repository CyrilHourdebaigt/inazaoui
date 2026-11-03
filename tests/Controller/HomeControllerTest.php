<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageIsAccessible(): void
    {
        // Je crée un navigateur pour simuler la visite d'un utilisateur
        $client = static::createClient();

        // Je demande au navigateur de visiter la page d'accueil
        $client->request('GET', '/');

        // Je vérifie que la page répond avec un code HTTP 200
        $this->assertResponseIsSuccessful();
    }


    public function testGuestsPageDisplaysOnlyActiveGuests(): void
    {
        // Je crée un navigateur pour simuler la visite d'un utilisateur
        $client = static::createClient();

        // Je visite la page publique des invités
        $client->request('GET', '/guests');

        // Je vérifie que la page s'affiche correctement
        $this->assertResponseIsSuccessful();

        // Je vérifie que l'invité actif apparaît
        $this->assertSelectorTextContains('body', 'Invité actif test');

        // Je vérifie que l'invité bloqué n'apparaît pas
        $this->assertSelectorTextNotContains('body', 'Invité bloqué test');
    }


    public function testBlockedGuestProfileReturns404(): void
    {
        // Je crée un navigateur pour simuler la visite d'un utilisateur
        $client = static::createClient();

        // Je récupère l'invité bloqué dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $blockedGuest = $entityManager
            ->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'bloque.test@example.fr']);

        // Je vérifie que mon invité de test existe
        $this->assertNotNull($blockedGuest);

        // J'essaie d'accéder directement au profil de cet invité bloqué
        $client->request('GET', '/guest/' . $blockedGuest->getId());

        // Je vérifie que Symfony refuse l'accès avec une erreur 404
        $this->assertResponseStatusCodeSame(404);
    }


    public function testActiveGuestProfileIsAccessible(): void
    {
        // Je crée un navigateur pour simuler la visite d'un utilisateur
        $client = static::createClient();

        // Je récupère mon invité actif dans la base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $activeGuest = $entityManager
            ->getRepository(\App\Entity\User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que mon invité existe
        $this->assertNotNull($activeGuest);

        // Je visite le profil de cet invité
        $client->request('GET', '/guest/' . $activeGuest->getId());

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que son nom apparaît sur la page
        $this->assertSelectorTextContains('h3', 'Invité actif test');
    }


    public function testPortfolioDisplaysOnlyActiveGuestsPhotos(): void
    {
        // Je crée un navigateur pour simuler la visite du portfolio
        $client = static::createClient();

        // Je récupère l'album créé par mes fixtures
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $album = $entityManager
            ->getRepository(\App\Entity\Album::class)
            ->findOneBy(['name' => 'Album de test']);

        // Je vérifie que l'album existe dans ma base de test
        $this->assertNotNull($album);

        // Je visite le portfolio en sélectionnant cet album
        $client->request('GET', '/portfolio/' . $album->getId());

        // Je vérifie que la page s'affiche correctement
        $this->assertResponseIsSuccessful();

        // Je vérifie que la photographie de l'invité actif apparaît
        $this->assertSelectorTextContains('body', 'Photo invité actif');

        // Je vérifie que la photographie de l'invité bloqué n'apparaît pas
        $this->assertSelectorTextNotContains('body', 'Photo invité bloqué');
    }

    public function testMainPortfolioDisplaysOnlyInaPhotos(): void
    {
        // Je crée un navigateur pour visiter le portfolio principal
        $client = static::createClient();

        // Je visite le portfolio sans sélectionner d'album
        $client->request('GET', '/portfolio');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que la photographie d'Ina apparaît
        $this->assertSelectorTextContains('body', 'Photo portfolio Ina');

        // Je vérifie que les photographies des invités n'apparaissent pas
        $this->assertSelectorTextNotContains('body', 'Photo invité actif');
        $this->assertSelectorTextNotContains('body', 'Photo invité bloqué');
    }


    public function testAboutPageIsAccessible(): void
    {
        // Je crée un navigateur pour simuler la visite d'un utilisateur
        $client = static::createClient();

        // Je visite la page de présentation
        $client->request('GET', '/about');

        // Je vérifie que la page s'affiche correctement
        $this->assertResponseIsSuccessful();
    }
}
