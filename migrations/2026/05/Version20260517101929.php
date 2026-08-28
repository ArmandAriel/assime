<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260517101929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD status VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE post ADD city_id INT NOT NULL');
        $this->addSql('ALTER TABLE post RENAME COLUMN localisation TO localisation_details');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D8BAC62AF ON post (city_id)');
    }

    public function down(Schema $schema): void
    {        
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8D8BAC62AF');
        $this->addSql('DROP INDEX IDX_5A8A6C8D8BAC62AF');
        $this->addSql('ALTER TABLE post ADD localisation VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE post DROP localisation_details');
        $this->addSql('ALTER TABLE post DROP status');
        $this->addSql('ALTER TABLE post DROP city_id');
    }
}
