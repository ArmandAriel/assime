<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260528225733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la relation entre CategoryAttribute et Category';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_attribute ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE category_attribute ADD CONSTRAINT FK_3D1A3DCB12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_3D1A3DCB12469DE2 ON category_attribute (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category_attribute DROP CONSTRAINT FK_3D1A3DCB12469DE2');
        $this->addSql('DROP INDEX IDX_3D1A3DCB12469DE2');
        $this->addSql('ALTER TABLE category_attribute DROP category_id');
    }
}
