<?php

namespace App\Tests\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserCheckerTest extends TestCase
{
    // Je vérifie qu'un utilisateur actif est autorisé
    public function testActiveUserIsAccepted(): void
    {
        // Je crée un utilisateur actif
        $user = new User();
        $user->setActive(true);

        // Je crée le vérificateur de compte
        $checker = new UserChecker();

        // Je vérifie que la méthode ne déclenche aucune exception
        $checker->checkPreAuth($user);

        $this->assertTrue($user->isActive());
    }

    // Je vérifie qu'un utilisateur désactivé est refusé
    public function testInactiveUserIsRejected(): void
    {
        // Je crée un utilisateur désactivé
        $user = new User();
        $user->setActive(false);

        // Je crée le vérificateur de compte
        $checker = new UserChecker();

        // Je vérifie que la connexion est refusée
        $this->expectException(CustomUserMessageAccountStatusException::class);

        $checker->checkPreAuth($user);
    }

    // Je vérifie que la méthode appelée après l'authentification ne bloque pas l'utilisateur
    public function testPostAuthAcceptsUser(): void
    {
        // Je crée un utilisateur
        $user = new User();

        // Je crée le vérificateur de compte
        $checker = new UserChecker();

        // Je lance la vérification après l'authentification
        $checker->checkPostAuth($user);

        // Je vérifie que l'exécution arrive jusqu'ici
        $this->assertTrue(true);
    }
}
