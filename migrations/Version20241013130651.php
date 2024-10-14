<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241013130651 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_score (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, quizz_id INT NOT NULL, score INT NOT NULL, INDEX IDX_D05BCC09A76ED395 (user_id), INDEX IDX_D05BCC09BA934BCD (quizz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_score ADD CONSTRAINT FK_D05BCC09A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_score ADD CONSTRAINT FK_D05BCC09BA934BCD FOREIGN KEY (quizz_id) REFERENCES quizz (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_score DROP FOREIGN KEY FK_D05BCC09A76ED395');
        $this->addSql('ALTER TABLE user_score DROP FOREIGN KEY FK_D05BCC09BA934BCD');
        $this->addSql('DROP TABLE user_score');
    }
}
