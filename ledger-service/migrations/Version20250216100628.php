<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216100628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ledger (ledger_id VARCHAR(36) NOT NULL, currency VARCHAR(3) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(ledger_id))');
        $this->addSql('COMMENT ON COLUMN ledger.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE transaction (transaction_id VARCHAR(36) NOT NULL, ledger_id VARCHAR(36) NOT NULL, transaction_type VARCHAR(6) NOT NULL, amount NUMERIC(10, 2) NOT NULL, currency VARCHAR(3) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(transaction_id))');
        $this->addSql('CREATE INDEX IDX_723705D1A7B913DD ON transaction (ledger_id)');
        $this->addSql('COMMENT ON COLUMN transaction.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A7B913DD FOREIGN KEY (ledger_id) REFERENCES ledger (ledger_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1A7B913DD');
        $this->addSql('DROP TABLE ledger');
        $this->addSql('DROP TABLE transaction');
    }
}
