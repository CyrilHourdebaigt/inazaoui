<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    // Je vérifie qu'un invité bloqué ne peut pas se connecter
    public function testBlockedGuestCannotLogin(): void
    {
        // Je crée un navigateur pour simuler la connexion
        $client = static::createClient();

        // Je consulte la page de connexion
        $crawler = $client->request('GET', '/login');

        // Je récupère le formulaire de connexion
        $form = $crawler->selectButton('Connexion')->form();

        // Je renseigne les identifiants de mon invité bloqué
        $form['_username'] = 'bloque.test@example.fr';
        $form['_password'] = 'TestPassword123!';

        // Je soumets le formulaire
        $client->submit($form);

        // Je suis la redirection vers la page de connexion
        $client->followRedirect();

        // Je vérifie que Symfony affiche le message de refus
        $this->assertSelectorTextContains(
            '.alert-danger',
            'Votre compte a été désactivé'
        );
    }


    // Je vérifie qu'un invité actif peut se connecter
    public function testActiveGuestCanLogin(): void
    {
        // Je crée un navigateur pour simuler la connexion
        $client = static::createClient();

        // Je consulte la page de connexion
        $crawler = $client->request('GET', '/login');

        // Je récupère le formulaire de connexion
        $form = $crawler->selectButton('Connexion')->form();

        // Je renseigne les identifiants de mon invité actif
        $form['_username'] = 'actif.test@example.fr';
        $form['_password'] = 'TestPassword123!';

        // Je soumets le formulaire
        $client->submit($form);

        // Je vérifie que Symfony accepte la connexion
        $this->assertResponseRedirects();

        // Je consulte une page réservée aux utilisateurs connectés
        $client->request('GET', '/admin/media');

        // Je vérifie que l'invité peut accéder à son espace
        $this->assertResponseIsSuccessful();
    }
}
