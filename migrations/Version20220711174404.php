<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220711174404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE donation (
                id INT AUTO_INCREMENT NOT NULL, 
                seed_batch_id INT NOT NULL, 
                beneficiary_id INT NOT NULL, 
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
                status VARCHAR(30) NOT NULL, 
                UNIQUE INDEX UNIQ_31E581A07A633C14 (seed_batch_id), 
                INDEX IDX_31E581A0ECCAAFA0 (beneficiary_id), 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE donation ADD CONSTRAINT FK_31E581A07A633C14
             FOREIGN KEY (seed_batch_id) REFERENCES seed_batch (id)'
        );
        $this->addSql(
            'ALTER TABLE donation ADD CONSTRAINT FK_31E581A0ECCAAFA0
             FOREIGN KEY (beneficiary_id) REFERENCES user (id)'
        );
        $this->addSql(
            'ALTER TABLE seed_batch ADD is_available TINYINT(1) NOT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE donation');
        $this->addSql('ALTER TABLE seed_batch DROP is_available');
    }
}
