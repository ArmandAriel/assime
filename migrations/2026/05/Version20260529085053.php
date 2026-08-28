<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260529085053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add new columns to listing_attribute_value table for different attribute types and a foreign key to listing table';
    }

    public function up(Schema $schema): void
    {
        
        $this->addSql('ALTER TABLE listing_attribute_value ADD value_text VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE listing_attribute_value ADD value_number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_attribute_value ADD value_boolean BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_attribute_value ADD value_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE listing_attribute_value ADD listing_id INT NOT NULL');
        $this->addSql('ALTER TABLE listing_attribute_value ADD CONSTRAINT FK_3C5D899CD4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_3C5D899CD4619D1A ON listing_attribute_value (listing_id)');
    }

    public function down(Schema $schema): void
    {
        
        $this->addSql('ALTER TABLE listing_attribute_value DROP CONSTRAINT FK_3C5D899CD4619D1A');
        $this->addSql('DROP INDEX IDX_3C5D899CD4619D1A');
        $this->addSql('ALTER TABLE listing_attribute_value DROP value_text');
        $this->addSql('ALTER TABLE listing_attribute_value DROP value_number');
        $this->addSql('ALTER TABLE listing_attribute_value DROP value_boolean');
        $this->addSql('ALTER TABLE listing_attribute_value DROP value_date');
        $this->addSql('ALTER TABLE listing_attribute_value DROP listing_id');
    }
}
