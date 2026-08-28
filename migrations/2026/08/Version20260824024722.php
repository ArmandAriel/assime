<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260824024722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add Listing.owner (User) - required, so clear any ownerless test data first';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM listing');
        $this->addSql('ALTER TABLE listing ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D47E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_CB0048D47E3C61F9 ON listing (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE listing DROP CONSTRAINT FK_CB0048D47E3C61F9');
        $this->addSql('DROP INDEX IDX_CB0048D47E3C61F9');
        $this->addSql('ALTER TABLE listing DROP owner_id');
    }
}
