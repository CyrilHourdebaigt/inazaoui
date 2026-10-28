<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class MediaController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/admin/media", name="admin_media_index")
     */
    public function index(Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $this->doctrine->getRepository(Media::class)->findBy(
            $criteria,
            ['id' => 'ASC'],
            25,
            25 * ($page - 1)
        );
        // Je compte uniquement les médias correspondant aux critères de recherche
        // Un admin compte tous les médias, un invité uniquement les siens
        $total = $this->doctrine->getRepository(Media::class)->count($criteria);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    /**
     * @Route("/admin/media/add", name="admin_media_add")
     */
    public function add(Request $request)
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $media->setUser($this->getUser());
            }
            $media->setPath('uploads/' . md5(uniqid()) . '.' . $media->getFile()->guessExtension());
            $media->getFile()->move('uploads/', $media->getPath());
            $this->doctrine->getManager()->persist($media);
            $this->doctrine->getManager()->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/admin/media/delete/{id}", name="admin_media_delete", methods={"POST"})
     */

    public function delete(int $id, Request $request)
    {
        // Je vérifie que l'utilisateur est connecté
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Je recherche la photo à supprimer
        $media = $this->doctrine->getRepository(Media::class)->find($id);

        if (!$media) {
            throw $this->createNotFoundException('Photographie introuvable.');
        }

        // Je vérifie que l'utilisateur est admin ou propriétaire de la photo
        if (!$this->isGranted('ROLE_ADMIN') && $media->getUser()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException(
                'Vous ne pouvez pas supprimer cette photographie.'
            );
        }

        // Je vérifie le jeton de sécurité du formulaire
        if (!$this->isCsrfTokenValid(
            'delete_media_' . $media->getId(),
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        // Je conserve le chemin de la photo avant sa suppression
        $path = $media->getPath();

        // Je supprime la photographie de PostgreSQL.
        $this->doctrine->getManager()->remove($media);
        $this->doctrine->getManager()->flush();

        // Je vérifie que le chemin correspond à un fichier du dossier uploads
        $filename = basename($path);

        if ($path === 'uploads/' . $filename) {
            $filePath = $this->getParameter('kernel.project_dir')
                . '/public/uploads/' . $filename;

            // Je supprime le fichier physique uniquement s'il existe
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }

        // Je retourne à la liste des photos
        return $this->redirectToRoute('admin_media_index');
    }
}
