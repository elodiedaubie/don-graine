<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220712121046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE donation DROP INDEX UNIQ_31E581A07A633C14, 
            ADD INDEX IDX_31E581A07A633C14 (seed_batch_id)'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE donation DROP INDEX IDX_31E581A07A633C14, 
            ADD UNIQUE INDEX UNIQ_31E581A07A633C14 (seed_batch_id)'
        );
    }
}
