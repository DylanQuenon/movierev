<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003091000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collections_media DROP FOREIGN KEY FK_1A2CE68E514956FD');
        $this->addSql('ALTER TABLE collections_media ADD CONSTRAINT FK_1A2CE68E514956FD FOREIGN KEY (collection_id) REFERENCES collections (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collections_media DROP FOREIGN KEY FK_1A2CE68E514956FD');
        $this->addSql('ALTER TABLE collections_media ADD CONSTRAINT FK_1A2CE68E514956FD FOREIGN KEY (collection_id) REFERENCES collections (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
