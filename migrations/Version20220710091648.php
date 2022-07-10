<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220710091648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE plant (
                id INT AUTO_INCREMENT NOT NULL, 
                purpose_id INT NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                INDEX IDX_AB030D727FC21131 (purpose_id), 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE purpose (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(30) NOT NULL, 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE quality (
                id INT AUTO_INCREMENT NOT NULL, 
                name VARCHAR(30) NOT NULL, 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE seed_batch (
                id INT AUTO_INCREMENT NOT NULL, 
                owner_id INT NOT NULL, 
                plant_id INT NOT NULL, 
                quality_id INT NOT NULL, 
                seed_quantity INT NOT NULL, 
                INDEX IDX_CC034BC47E3C61F9 (owner_id), 
                INDEX IDX_CC034BC41D935652 (plant_id), 
                INDEX IDX_CC034BC4BCFC6D57 (quality_id), 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE plant ADD CONSTRAINT FK_AB030D727FC21131 FOREIGN KEY (purpose_id) REFERENCES purpose (id)'
        );
        $this->addSql(
            'ALTER TABLE seed_batch ADD CONSTRAINT FK_CC034BC47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)'
        );
        $this->addSql(
            'ALTER TABLE seed_batch ADD CONSTRAINT FK_CC034BC41D935652 FOREIGN KEY (plant_id) REFERENCES plant (id)'
        );
        $this->addSql(
            'ALTER TABLE seed_batch ADD CONSTRAINT FK_CC034BC4BCFC6D57 FOREIGN KEY (quality_id) REFERENCES quality (id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seed_batch DROP FOREIGN KEY FK_CC034BC41D935652');
        $this->addSql('ALTER TABLE plant DROP FOREIGN KEY FK_AB030D727FC21131');
        $this->addSql('ALTER TABLE seed_batch DROP FOREIGN KEY FK_CC034BC4BCFC6D57');
        $this->addSql('DROP TABLE plant');
        $this->addSql('DROP TABLE purpose');
        $this->addSql('DROP TABLE quality');
        $this->addSql('DROP TABLE seed_batch');
    }
}
