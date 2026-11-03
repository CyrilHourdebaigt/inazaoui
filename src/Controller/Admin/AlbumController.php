<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Form\AlbumType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AlbumController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/admin/album', name: 'admin_album_index')]
    public function index()
    {
        $albums = $this->doctrine->getRepository(Album::class)->findAll();

        return $this->render('admin/album/index.html.twig', ['albums' => $albums]);
    }

    #[Route('/admin/album/add', name: 'admin_album_add')]
    public function add(Request $request)
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->getManager()->persist($album);
            $this->doctrine->getManager()->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/admin/album/update/{id}', name: 'admin_album_update')]
    public function update(Request $request, int $id)
    {
        $album = $this->doctrine->getRepository(Album::class)->find($id);
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->getManager()->flush();

            return $this->redirectToRoute('admin_album_index');
        }

        return $this->render('admin/album/update.html.twig', ['form' => $form->createView()]);
    }


    #[Route('/admin/album/delete/{id}', name: 'admin_album_delete', methods: ['POST'])]
    public function delete(int $id, Request $request)
    {
        // Je récupère l'album à supprimer
        $album = $this->doctrine->getRepository(Album::class)->find($id);

        // Je vérifie que l'album existe
        if (!$album) {
            throw $this->createNotFoundException('Album introuvable');
        }

        // Je vérifie que le jeton CSRF est valide
        if (!$this->isCsrfTokenValid(
            'delete_album_' . $album->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide');
        }

        // Je supprime l'album de la bdd
        $this->doctrine->getManager()->remove($album);
        $this->doctrine->getManager()->flush();

        // Je retourne à la liste des albums
        return $this->redirectToRoute('admin_album_index');
    }
}
