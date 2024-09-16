<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240915133057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media_genre (media_id INT NOT NULL, genre_id INT NOT NULL, INDEX IDX_9C49F749EA9FDD75 (media_id), INDEX IDX_9C49F7494296D31F (genre_id), PRIMARY KEY(media_id, genre_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE media_genre ADD CONSTRAINT FK_9C49F749EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_genre ADD CONSTRAINT FK_9C49F7494296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media_genre DROP FOREIGN KEY FK_9C49F749EA9FDD75');
        $this->addSql('ALTER TABLE media_genre DROP FOREIGN KEY FK_9C49F7494296D31F');
        $this->addSql('DROP TABLE media_genre');
    }
}
