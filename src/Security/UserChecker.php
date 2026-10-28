<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        // Je vérifie que l'utilisateur correspond à mon entité User
        if (!$user instanceof User) {
            return;
        }

        // Je refuse la connexion si le compte est désactivé
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte a été désactivé. Veuillez contacter l’administratrice.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Je n'ai pas de vérification supplémentaire à effectuer
    }
}