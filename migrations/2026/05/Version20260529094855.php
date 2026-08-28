<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260529094855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'relate AttributeOption to CategoryAttribute instead of Category';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_option DROP CONSTRAINT fk_78672eea12469de2');
        $this->addSql('DROP INDEX idx_78672eea12469de2');
        $this->addSql('ALTER TABLE attribute_option ADD category_attribute_id INT NOT NULL');
        $this->addSql('ALTER TABLE attribute_option DROP category_id');
        $this->addSql('ALTER TABLE attribute_option ADD CONSTRAINT FK_78672EEA6C310D68 FOREIGN KEY (category_attribute_id) REFERENCES category_attribute (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_78672EEA6C310D68 ON attribute_option (category_attribute_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attribute_option DROP CONSTRAINT FK_78672EEA6C310D68');
        $this->addSql('DROP INDEX IDX_78672EEA6C310D68');
        $this->addSql('ALTER TABLE attribute_option ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attribute_option DROP category_attribute_id');
        $this->addSql('ALTER TABLE attribute_option ADD CONSTRAINT fk_78672eea12469de2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_78672eea12469de2 ON attribute_option (category_id)');
    }
}
