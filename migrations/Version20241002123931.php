<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241002123931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE collections_media (id INT AUTO_INCREMENT NOT NULL, collection_id INT DEFAULT NULL, medias_id INT DEFAULT NULL, position INT NOT NULL, INDEX IDX_1A2CE68E514956FD (collection_id), INDEX IDX_1A2CE68EC7F4A74B (medias_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE collections_media ADD CONSTRAINT FK_1A2CE68E514956FD FOREIGN KEY (collection_id) REFERENCES collections (id)');
        $this->addSql('ALTER TABLE collections_media ADD CONSTRAINT FK_1A2CE68EC7F4A74B FOREIGN KEY (medias_id) REFERENCES media (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collections_media DROP FOREIGN KEY FK_1A2CE68E514956FD');
        $this->addSql('ALTER TABLE collections_media DROP FOREIGN KEY FK_1A2CE68EC7F4A74B');
        $this->addSql('DROP TABLE collections_media');
    }
}
