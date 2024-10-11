<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011145810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA3E2E969B');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF8697D13');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA3E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA3E2E969B');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF8697D13');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA3E2E969B FOREIGN KEY (review_id) REFERENCES review (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
