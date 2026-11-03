<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // Je récupère les éventuelles erreurs de connexion
        $error = $authenticationUtils->getLastAuthenticationError();

        // Je récupère l'identifiant utilisé lors de la dernière tentative
        $lastUsername = $authenticationUtils->getLastUsername();

        // J'affiche le formulaire de connexion
        return $this->render('admin/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
