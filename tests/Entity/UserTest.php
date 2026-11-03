<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserRoles(): void
    {
        // Je crée un utilisateur pour vérifier ses droits
        $user = new User();

        // Je vérifie qu'un invité possède le rôle ROLE_USER
        $this->assertSame(['ROLE_USER'], $user->getRoles());

        // Je transforme cet utilisateur en administrateur
        $user->setAdmin(true);

        // Je vérifie qu'il possède maintenant le rôle ROLE_ADMIN.
        $this->assertSame(['ROLE_ADMIN'], $user->getRoles());
    }


    public function testUserCanBeBlockedAndReactivated(): void
    {
        // Je crée un utilisateur pour tester son statut
        $user = new User();

        // Je vérifie que son compte est actif par défaut
        $this->assertTrue($user->isActive());

        // Je bloque son compte
        $user->setActive(false);

        // Je vérifie que son compte est désormais bloqué
        $this->assertFalse($user->isActive());

        // Je réactive son compte
        $user->setActive(true);

        // Je vérifie que son compte est de nouveau actif
        $this->assertTrue($user->isActive());
    }


    public function testUsersWithDifferentStatusAreNotEqual(): void
    {
        // Je crée deux utilisateurs avec les mêmes informations
        $firstUser = new User();
        $firstUser->setEmail('invite@example.fr');
        $firstUser->setPassword('mot-de-passe-test');

        $secondUser = new User();
        $secondUser->setEmail('invite@example.fr');
        $secondUser->setPassword('mot-de-passe-test');

        // Je vérifie que les deux utilisateurs sont considérés comme identiques
        $this->assertTrue($firstUser->isEqualTo($secondUser));

        // Je bloque le deuxième utilisateur
        $secondUser->setActive(false);

        // Je vérifie que les deux utilisateurs ne sont plus considérés comme identiques
        $this->assertFalse($firstUser->isEqualTo($secondUser));
    }


    public function testUsersWithDifferentRolesAreNotEqual(): void
    {
        // Je crée deux utilisateurs avec les mêmes informations
        $firstUser = new User();
        $firstUser->setEmail('invite@example.fr');
        $firstUser->setPassword('mot-de-passe-test');

        $secondUser = new User();
        $secondUser->setEmail('invite@example.fr');
        $secondUser->setPassword('mot-de-passe-test');

        // Je vérifie que les utilisateurs sont considérés comme identiques
        $this->assertTrue($firstUser->isEqualTo($secondUser));

        // Je donne les droits administrateur au deuxième utilisateur
        $secondUser->setAdmin(true);

        // Je vérifie que ce changement de rôle est détecté
        $this->assertFalse($firstUser->isEqualTo($secondUser));
    }
}
