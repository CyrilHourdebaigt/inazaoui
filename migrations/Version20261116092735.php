<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261116092735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Conserver les photographies lors de la suppression de leur album';
    }

    public function up(Schema $schema): void
    {
        // Je supprime l'ancienne contrainte de clé étrangère
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10C1137ABCF');

        // Je conserve les photographies en supprimant uniquement leur association à l'album
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C1137ABCF FOREIGN KEY (album_id) REFERENCES album (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // Je supprime la contrainte créée par cette migration
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10C1137ABCF');

        // Je rétablis l'ancienne contrainte de clé étrangère
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C1137ABCF FOREIGN KEY (album_id) REFERENCES album (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
