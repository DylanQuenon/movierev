<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241015154005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77843E2E969B');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77843E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77843E2E969B');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77843E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
