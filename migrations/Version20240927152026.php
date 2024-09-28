<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240927152026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, comment_id INT DEFAULT NULL, review_id INT DEFAULT NULL, INDEX IDX_49CA4E7DF675F31B (author_id), INDEX IDX_49CA4E7DF8697D13 (comment_id), INDEX IDX_49CA4E7D3E2E969B (review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7D3E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DF675F31B');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DF8697D13');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7D3E2E969B');
        $this->addSql('DROP TABLE likes');
    }
}
