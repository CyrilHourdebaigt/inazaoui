<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\GuestType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
final class GuestController extends AbstractController
{
    #[Route('/admin/guest', name: 'app_admin_guest')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Je récupère uniquement les utilisateurs qui ne sont pas admin
        $guests = $entityManager->getRepository(User::class)->findBy(
            ['admin' => false],
            ['id' => 'ASC']
        );

        // J'envoie la liste des invités à mon template
        return $this->render('admin/guest/index.html.twig', [
            'guests' => $guests,
        ]);
    }

    #[Route('/admin/guest/add', name: 'admin_guest_add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Je crée un nouvel utilisateur
        $guest = new User();

        // Je crée le formulaire associé à cet utilisateur
        $form = $this->createForm(GuestType::class, $guest);
        $form->handleRequest($request);

        // Je vérifie si le formulaire a été envoyé et s'il est valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Je récupère le mot de passe saisi dans le formulaire
            $plainPassword = $form->get('password')->getData();

            // Je hash le mot de passe avant de l'enregistrer
            $hashedPassword = $passwordHasher->hashPassword($guest, $plainPassword);
            $guest->setPassword($hashedPassword);

            // Je m'assure que le nouvel utilisateur n'est pas admin
            $guest->setAdmin(false);

            // J'enregistre le nouvel invité dans la bdd
            $entityManager->persist($guest);
            $entityManager->flush();

            // Je retourne à la liste des invités
            return $this->redirectToRoute('app_admin_guest');
        }

        // J'affiche le formulaire de création d'un invité
        return $this->render('admin/guest/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/guest/toggle/{id}', name: 'admin_guest_toggle', methods: ['POST'])]
    public function toggle(User $guest, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Je vérifie que la demande provient bien de mon formulaire
        if (!$this->isCsrfTokenValid('toggle_guest_' . $guest->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        // Je vérifie que l'utilisateur n'est pas admin
        if ($guest->isAdmin()) {
            throw $this->createAccessDeniedException('Impossible de bloquer un administrateur.');
        }

        // J'inverse l'état du compte : actif devient bloqué, et inversement
        $guest->setActive(!$guest->isActive());

        // J'enregistre le changement
        $entityManager->flush();

        // Je retourne à la liste des invités
        return $this->redirectToRoute('app_admin_guest');
    }

    #[Route('/admin/guest/delete/{id}', name: 'admin_guest_delete', methods: ['POST'])]
    public function delete(
        User $guest,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Je vérifie que la demande provient bien du formulaire
        if (!$this->isCsrfTokenValid(
            'delete_guest_' . $guest->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        // Je protège le compte admin contre une suppression
        if ($guest->isAdmin()) {
            throw $this->createAccessDeniedException(
                'Impossible de supprimer un administrateur.'
            );
        }

        // Je récupère les chemins des photos de l'invité
        $files = [];

        foreach ($guest->getMedias() as $media) {
            $files[] = $media->getPath();
        }

        // Je supprime l'invité et ses médias associés dans la bdd
        $entityManager->remove($guest);
        $entityManager->flush();

        // Je supprime les fichiers physiques après la suppression en base
        foreach ($files as $path) {
            // Je limite la suppression aux fichiers du dossier public/uploads
            $filename = basename($path);

            if ($path !== 'uploads/' . $filename) {
                continue;
            }

            $filePath = $this->getParameter('kernel.project_dir')
                . '/public/uploads/' . $filename;

            // Je vérifie que le fichier existe avant de le supprimer
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        // Je retourne à la liste des invités
        return $this->redirectToRoute('app_admin_guest');
    }
}
