<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003095934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE follow_request (id INT AUTO_INCREMENT NOT NULL, requester_id INT NOT NULL, requested_id INT NOT NULL, is_accepted TINYINT(1) NOT NULL, INDEX IDX_6562D72FED442CF4 (requester_id), INDEX IDX_6562D72F983624B7 (requested_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FED442CF4 FOREIGN KEY (requester_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72F983624B7 FOREIGN KEY (requested_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FED442CF4');
        $this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72F983624B7');
        $this->addSql('DROP TABLE follow_request');
    }
}
