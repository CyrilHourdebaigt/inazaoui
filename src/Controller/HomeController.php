<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class HomeController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('front/home.html.twig');
    }

    /**
     * @Route("/guests", name="guests")
     */
    public function guests()
    {
        // Je récupère uniquement les invités dont le compte est actif
        $guests = $this->doctrine->getRepository(User::class)->findBy([
            'admin' => false,
            'active' => true
        ]);

        // J'envoie les invités actifs à mon template
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    /**
     * @Route("/guest/{id}", name="guest")
     */
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

    /**
     * @Route("/portfolio/{id}", name="portfolio")
     */
    public function portfolio(?int $id = null)
    {
        $albums = $this->doctrine->getRepository(Album::class)->findAll();
        $album = $id ? $this->doctrine->getRepository(Album::class)->find($id) : null;
        $user = $this->doctrine->getRepository(User::class)->findOneByAdmin(true);

        // Si un album est sélectionné, je récupère uniquement les médias des utilisateurs actifs.
        // Sinon, je conserve les photographies d'Ina comme auparavant.
        $medias = $album
            ? $this->doctrine->getRepository(Media::class)->findByActiveUsers($album)
            : $this->doctrine->getRepository(Media::class)->findByUser($user);
        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('front/about.html.twig');
    }
}
