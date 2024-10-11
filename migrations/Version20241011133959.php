<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011133959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3AC24F853');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3D956F010');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3D956F010 FOREIGN KEY (followed_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3AC24F853');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3D956F010');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3D956F010 FOREIGN KEY (followed_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
