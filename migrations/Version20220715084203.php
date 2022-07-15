<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220715084203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE seed_batch_user (
                seed_batch_id INT NOT NULL, 
                user_id INT NOT NULL, 
                INDEX IDX_E53F116E7A633C14 (seed_batch_id), 
                INDEX IDX_E53F116EA76ED395 (user_id), 
                PRIMARY KEY(seed_batch_id, user_id)) DEFAULT CHARACTER SET utf8mb4 
                COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE seed_batch_user ADD CONSTRAINT FK_E53F116E7A633C14 
            FOREIGN KEY (seed_batch_id) REFERENCES seed_batch (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE seed_batch_user ADD CONSTRAINT FK_E53F116EA76ED395 
            FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE seed_batch_user');
    }
}
