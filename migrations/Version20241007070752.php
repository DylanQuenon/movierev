<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241007070752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, review_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, related_user_id INT NOT NULL, type VARCHAR(255) NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CA3E2E969B (review_id), INDEX IDX_BF5476CAF8697D13 (comment_id), INDEX IDX_BF5476CA98771930 (related_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA3E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA98771930 FOREIGN KEY (related_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA3E2E969B');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF8697D13');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA98771930');
        $this->addSql('DROP TABLE notification');
    }
}
