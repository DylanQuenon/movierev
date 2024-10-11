<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241011134350 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collections DROP FOREIGN KEY FK_D325D3EEA76ED395');
        $this->addSql('ALTER TABLE collections ADD CONSTRAINT FK_D325D3EEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72F983624B7');
        $this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FED442CF4');
        $this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72F983624B7 FOREIGN KEY (requested_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FED442CF4 FOREIGN KEY (requester_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE collections DROP FOREIGN KEY FK_D325D3EEA76ED395');
        $this->addSql('ALTER TABLE collections ADD CONSTRAINT FK_D325D3EEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72FED442CF4');
        $this->addSql('ALTER TABLE follow_request DROP FOREIGN KEY FK_6562D72F983624B7');
        $this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72FED442CF4 FOREIGN KEY (requester_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE follow_request ADD CONSTRAINT FK_6562D72F983624B7 FOREIGN KEY (requested_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
