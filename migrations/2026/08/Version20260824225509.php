<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260824225509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE listing_attribute_value ADD category_attribute_id INT NOT NULL');
        $this->addSql('ALTER TABLE listing_attribute_value ALTER value_text DROP NOT NULL');
        $this->addSql('ALTER TABLE listing_attribute_value ALTER value_number TYPE DOUBLE PRECISION');
        $this->addSql('ALTER TABLE listing_attribute_value ADD CONSTRAINT FK_3C5D899C6C310D68 FOREIGN KEY (category_attribute_id) REFERENCES category_attribute (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_3C5D899C6C310D68 ON listing_attribute_value (category_attribute_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_LISTING_ATTRIBUTE ON listing_attribute_value (listing_id, category_attribute_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE listing_attribute_value DROP CONSTRAINT FK_3C5D899C6C310D68');
        $this->addSql('DROP INDEX IDX_3C5D899C6C310D68');
        $this->addSql('DROP INDEX UNIQ_LISTING_ATTRIBUTE');
        $this->addSql('ALTER TABLE listing_attribute_value DROP category_attribute_id');
        $this->addSql('ALTER TABLE listing_attribute_value ALTER value_text SET NOT NULL');
        $this->addSql('ALTER TABLE listing_attribute_value ALTER value_number TYPE INT');
    }
}
