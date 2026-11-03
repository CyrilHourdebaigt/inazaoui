<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    public function testMediaBelongsToUserAndAlbum(): void
    {
        // Je crée un utilisateur pour représenter le propriétaire de la photographie
        $user = new User();
        $user->setName('Invité test');

        // Je crée un album
        $album = new Album();
        $album->setName('Portraits');

        // Je crée une photographie
        $media = new Media();
        $media->setTitle('Portrait de test');
        $media->setPath('uploads/portrait-test.jpg');

        // J'associe la photographie à son propriétaire et à son album
        $media->setUser($user);
        $media->setAlbum($album);

        // Je vérifie que les associations sont correctes
        $this->assertSame($user, $media->getUser());
        $this->assertSame($album, $media->getAlbum());

        // Je vérifie les informations de la photographie
        $this->assertSame('Portrait de test', $media->getTitle());
        $this->assertSame('uploads/portrait-test.jpg', $media->getPath());
    }
}
