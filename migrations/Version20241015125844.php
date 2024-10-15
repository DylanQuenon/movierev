<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015125844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CABA934BCD');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CABA934BCD FOREIGN KEY (quizz_id) REFERENCES quizz (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CABA934BCD');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CABA934BCD FOREIGN KEY (quizz_id) REFERENCES quizz (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
