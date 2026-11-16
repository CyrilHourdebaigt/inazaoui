<?php

namespace App\Tests\Controller\Admin;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaControllerTest extends WebTestCase
{
    // Je vérifie qu'un admin peut consulter toutes les photos
    public function testAdminCanAccessMediaList(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        // Je vérifie que l'admin existe
        $this->assertNotNull($admin);

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite la liste des photos
        $client->request('GET', '/admin/media');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que les photos des invités apparaissent
        $this->assertSelectorTextContains('body', 'Photo invité actif');
        $this->assertSelectorTextContains('body', 'Photo invité bloqué');

        // Je vérifie que la photo d'Ina apparaît également
        $this->assertSelectorTextContains('body', 'Photo portfolio Ina');
    }


    // Je vérifie qu'un invité ne peut consulter que ses propres photos
    public function testGuestCanOnlySeeOwnMedias(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité actif dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que l'invité existe
        $this->assertNotNull($guest);

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je visite la liste des photos
        $client->request('GET', '/admin/media');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que sa photo apparaît
        $this->assertSelectorTextContains('body', 'Photo invité actif');

        // Je vérifie que les photos des autres utilisateurs sont absentes
        $this->assertSelectorTextNotContains('body', 'Photo invité bloqué');
        $this->assertSelectorTextNotContains('body', 'Photo portfolio Ina');
    }


    // Je vérifie qu'un admin peut accéder au formulaire d'ajout d'une photo
    public function testAdminCanAccessMediaCreationForm(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        // Je vérifie que l'admin existe
        $this->assertNotNull($admin);

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite le formulaire d'ajout d'une photo
        $client->request('GET', '/admin/media/add');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que le formulaire est présent
        $this->assertSelectorExists('form');

        // Je vérifie que les champs attendus sont présents
        $this->assertSelectorExists('input[name="media[file]"]');
        $this->assertSelectorExists('input[name="media[title]"]');
        $this->assertSelectorExists('select[name="media[user]"]');
        $this->assertSelectorExists('select[name="media[album]"]');
    }


    // Je vérifie qu'un invité peut accéder au formulaire d'ajout d'une photo
    public function testGuestCanAccessMediaCreationForm(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité actif dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que l'invité existe
        $this->assertNotNull($guest);

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je visite le formulaire d'ajout d'une photo
        $client->request('GET', '/admin/media/add');

        // Je vérifie que la page est accessible
        $this->assertResponseIsSuccessful();

        // Je vérifie que les champs autorisés sont présents
        $this->assertSelectorExists('input[name="media[file]"]');
        $this->assertSelectorExists('input[name="media[title]"]');

        // Je vérifie que les champs réservés à l'admin sont absents
        $this->assertSelectorNotExists('select[name="media[user]"]');
        $this->assertSelectorNotExists('select[name="media[album]"]');
    }


    // Je vérifie qu'un invité ne peut pas envoyer un formulaire sans image
    public function testGuestCannotUploadWithoutFile(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité actif dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que l'invité existe
        $this->assertNotNull($guest);

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je récupère le formulaire d'ajout de photo
        $crawler = $client->request('GET', '/admin/media/add');
        $form = $crawler->selectButton('Ajouter')->form();

        // Je renseigne uniquement le titre, sans sélectionner de fichier
        $form['media[title]'] = 'Photo sans image';

        // J'envoie le formulaire
        $client->submit($form);

        // Je vérifie que Symfony réaffiche la page sans erreur 500
        $this->assertResponseIsSuccessful();

        // Je vérifie que le message de validation apparaît
        $this->assertSelectorTextContains(
            'body',
            'Veuillez sélectionner une image.'
        );

        // Je vérifie que la photo n'a pas été enregistrée en base
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $media = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->findOneBy(['title' => 'Photo sans image']);

        $this->assertNull($media);
    }

    // Je vérifie qu'un invité peut supprimer sa propre photo
    public function testGuestCanDeleteOwnMedia(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité actif dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        // Je vérifie que l'invité existe
        $this->assertNotNull($guest);

        // Je récupère sa photo de test
        $media = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->findOneBy(['title' => 'Photo invité actif']);

        // Je vérifie que la photo existe et appartient à cet invité
        $this->assertNotNull($media);
        $this->assertSame($guest->getId(), $media->getUser()->getId());

        // Je conserve l'identifiant de la photo
        $mediaId = $media->getId();

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je visite la liste de ses photo
        $crawler = $client->request('GET', '/admin/media');

        // Je récupère le formulaire de suppression de sa photo
        $form = $crawler
            ->filter('form[action="/admin/media/delete/' . $mediaId . '"]')
            ->form();

        // J'envoie le formulaire avec son jeton CSRF
        $client->submit($form);

        // Je vérifie que l'application me redirige vers la liste des médias
        $this->assertResponseRedirects('/admin/media');

        // Je récupère Doctrine après la suppression
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je recherche la photo supprimée
        $deletedMedia = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->find($mediaId);

        // Je vérifie que la photo n'existe plus
        $this->assertNull($deletedMedia);
    }


    // Je vérifie qu'un invité ne peut pas supprimer la photo d'un autre utilisateur
    public function testGuestCannotDeleteAnotherUsersMedia(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité et une photo appartenant à Ina
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        $media = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->findOneBy(['title' => 'Photo portfolio Ina']);

        // Je vérifie que l'invité et la photo existent
        $this->assertNotNull($guest);
        $this->assertNotNull($media);

        // Je conserve l'identifiant de la photo
        $mediaId = $media->getId();

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je tente de supprimer directement la photo d'Ina
        $client->request('POST', '/admin/media/delete/' . $mediaId);

        // Je vérifie que l'accès est interdit
        $this->assertResponseStatusCodeSame(403);

        // Je récupère Doctrine après la requête
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        // Je vérifie que la photo existe toujours
        $savedMedia = $entityManager
            ->getRepository(\App\Entity\Media::class)
            ->find($mediaId);

        $this->assertNotNull($savedMedia);
    }


    // Je vérifie qu'un admin peut consulter une photo sans utilisateur
    public function testAdminCanSeeMediaWithoutUser(): void
    {
        // Je crée un navigateur pour simuler l'admin
        $client = static::createClient();

        // Je récupère l'admin dans ma base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $admin = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'ina.test@example.fr']);

        // Je vérifie que l'admin existe
        $this->assertNotNull($admin);

        // Je crée une photo sans utilisateur
        $media = new \App\Entity\Media();
        $media->setTitle('Photo sans utilisateur');
        $media->setPath('uploads/photo-sans-utilisateur.jpg');

        // Je l'enregistre dans la base de test
        $entityManager->persist($media);
        $entityManager->flush();

        // Je connecte l'admin
        $client->loginUser($admin);

        // Je visite la liste des photos
        $client->request('GET', '/admin/media');

        // Je vérifie que la page s'affiche sans erreur 500
        $this->assertResponseIsSuccessful();

        // Je vérifie que le nom de la photo apparaît
        $this->assertSelectorTextContains('body', 'Photo sans utilisateur');

        // Je vérifie que l'absence d'utilisateur est bien indiquée
        $this->assertSelectorTextContains('body', 'Non attribué');
    }



    // Je vérifie qu'un invité peut ajouter une image PNG valide
    public function testGuestCanUploadValidImage(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité actif dans la base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        $this->assertNotNull($guest);

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je récupère le formulaire d'ajout
        $crawler = $client->request('GET', '/admin/media/add');
        $form = $crawler->selectButton('Ajouter')->form();

        // Je prépare notre image PNG de test
        $imagePath = static::getContainer()->getParameter('kernel.project_dir')
            . '/tests/Fixtures/test-image.png';

        // Je prépare la variable qui contiendra le chemin du fichier créé
        $filePath = null;

        try {
            // Je renseigne le formulaire avec notre image de test
            $form['media[title]'] = 'Image PNG de test';
            $form['media[file]']->upload(new UploadedFile(
                $imagePath,
                'test-image.png',
                'image/png',
                null,
                true
            ));

            // Je soumets le formulaire
            $client->submit($form);

            // Je vérifie que l'application redirige vers la liste des photos
            $this->assertResponseRedirects('/admin/media');

            // Je récupère la photo enregistrée
            $entityManager = static::getContainer()->get('doctrine')->getManager();

            $media = $entityManager
                ->getRepository(\App\Entity\Media::class)
                ->findOneBy(['title' => 'Image PNG de test']);

            // Je vérifie que la photo est enregistrée
            $this->assertNotNull($media);

            // Je vérifie qu'elle appartient bien à l'invité connecté
            $this->assertSame($guest->getId(), $media->getUser()->getId());

            // Je récupère le chemin du fichier créé dans public/uploads
            $filePath = static::getContainer()->getParameter('kernel.project_dir')
                . '/public/' . $media->getPath();

            // Je vérifie que le fichier existe bien sur le disque
            $this->assertFileExists($filePath);
        } finally {
            // Je supprime le fichier créé, même si une assertion échoue
            if ($filePath !== null && is_file($filePath)) {
                unlink($filePath);
            }
        }
    }

    // Je vérifie qu'un invité ne peut pas envoyer un fichier au mauvais format
    public function testGuestCannotUploadInvalidFileType(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité actif dans la base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        $this->assertNotNull($guest);

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je récupère le formulaire d'ajout
        $crawler = $client->request('GET', '/admin/media/add');
        $form = $crawler->selectButton('Ajouter')->form();

        // Je crée un fichier texte temporaire
        $filePath = tempnam(sys_get_temp_dir(), 'test_upload_');
        file_put_contents($filePath, 'Ceci est un fichier texte.');

        try {
            // Je simule l'envoi du fichier texte
            $form['media[title]'] = 'Fichier invalide';
            $form['media[file]']->upload(new UploadedFile(
                $filePath,
                'document.txt',
                'text/plain',
                null,
                true
            ));

            // Je soumets le formulaire
            $client->submit($form);

            // Je vérifie que Symfony réaffiche le formulaire sans erreur 500
            $this->assertResponseIsSuccessful();

            // Je vérifie que le message d'erreur apparaît
            $this->assertSelectorTextContains(
                'body',
                'Veuillez sélectionner une image valide au format JPEG, PNG ou WEBP.'
            );

            // Je vérifie que le fichier n'a pas été enregistré en base
            $entityManager = static::getContainer()->get('doctrine')->getManager();

            $media = $entityManager
                ->getRepository(\App\Entity\Media::class)
                ->findOneBy(['title' => 'Fichier invalide']);

            $this->assertNull($media);
        } finally {
            // Je supprime le fichier temporaire s'il existe encore
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }


    // Je vérifie qu'un invité ne peut pas envoyer une image de plus de 2 Mo
    public function testGuestCannotUploadImageOver2MB(): void
    {
        // Je crée un navigateur pour simuler un invité
        $client = static::createClient();

        // Je récupère l'invité actif dans la base de test
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $guest = $entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'actif.test@example.fr']);

        $this->assertNotNull($guest);

        // Je connecte l'invité
        $client->loginUser($guest);

        // Je récupère le formulaire d'ajout
        $crawler = $client->request('GET', '/admin/media/add');
        $form = $crawler->selectButton('Ajouter')->form();

        // Je récupère notre image PNG valide
        $imagePath = static::getContainer()->getParameter('kernel.project_dir')
            . '/tests/Fixtures/test-image.png';

        // Je crée une copie temporaire de l'image
        $filePath = tempnam(sys_get_temp_dir(), 'test_large_image_');
        copy($imagePath, $filePath);

        // J'agrandis le fichier au-delà de 2 Mo sans modifier son en-tête PNG
        $file = fopen($filePath, 'ab');
        ftruncate($file, 3 * 1024 * 1024);
        fclose($file);

        try {
            // Je simule l'envoi de cette image trop volumineuse
            $form['media[title]'] = 'Image trop volumineuse';
            $form['media[file]']->upload(new UploadedFile(
                $filePath,
                'grande-image.png',
                'image/png',
                null,
                true
            ));

            // Je soumets le formulaire
            $client->submit($form);

            // Je vérifie que Symfony réaffiche le formulaire
            $this->assertResponseIsSuccessful();

            // Je vérifie que le message concernant la taille apparaît
            $this->assertSelectorTextContains(
                'body',
                "L'image ne doit pas dépasser 2 Mo."
            );

            // Je vérifie qu'aucune photo n'a été enregistrée
            $entityManager = static::getContainer()->get('doctrine')->getManager();

            $media = $entityManager
                ->getRepository(\App\Entity\Media::class)
                ->findOneBy(['title' => 'Image trop volumineuse']);

            $this->assertNull($media);
        } finally {
            // Je supprime le fichier temporaire après le test
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }
}
