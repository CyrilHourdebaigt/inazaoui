<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\MediaRepository;
use App\Repository\UserRepository;

class HomeController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'home')]
    public function home()
    {
        return $this->render('front/home.html.twig');
    }

    #[Route('/guests', name: 'guests')]
    public function guests()
    {
    // Je récupère les invités actifs avec leur nombre de photos
        /** @var UserRepository $userRepository */
        $userRepository = $this->doctrine->getRepository(User::class);

        $guests = $userRepository->findActiveGuestsWithMediaCount();

        // J'envoie les invités et leur nombre de photos à mon template
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }


    #[Route('/guest/{id}', name: 'guest')]
    public function guest(int $id)
    {
        // Je recherche uniquement un invité dont le compte est actif
        $guest = $this->doctrine->getRepository(User::class)->findOneBy([
            'id' => $id,
            'admin' => false,
            'active' => true
        ]);

        // Je retourne une erreur 404 si l'invité n'existe pas ou s'il est bloqué
        if (!$guest) {
            throw $this->createNotFoundException('Cet invité est introuvable ou son compte est désactivé.');
        }

        // J'affiche le profil de l'invité actif
        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    #[Route('/portfolio/{id}', name: 'portfolio')]
    public function portfolio(?int $id = null)
    {
        $albums = $this->doctrine->getRepository(Album::class)->findAll();
        $album = $id ? $this->doctrine->getRepository(Album::class)->find($id) : null;
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['admin' => true]);
 
        // Je récupère le repository des photographies
        /** @var MediaRepository $mediaRepository */
        $mediaRepository = $this->doctrine->getRepository(Media::class);

        // Je récupère les photographies selon l'album sélectionné
        $medias = $album
            ? $mediaRepository->findByActiveUsers($album)
            : $mediaRepository->findBy(['user' => $user]);

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about()
    {
        return $this->render('front/about.html.twig');
    }
}
