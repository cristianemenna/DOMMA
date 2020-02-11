<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200211171010 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE context_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE context (id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE context_users (context_id INT NOT NULL, users_id INT NOT NULL, PRIMARY KEY(context_id, users_id))');
        $this->addSql('CREATE INDEX IDX_4594C9976B00C1CF ON context_users (context_id)');
        $this->addSql('CREATE INDEX IDX_4594C99767B3B43D ON context_users (users_id)');
        $this->addSql('ALTER TABLE context_users ADD CONSTRAINT FK_4594C9976B00C1CF FOREIGN KEY (context_id) REFERENCES context (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE context_users ADD CONSTRAINT FK_4594C99767B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE context_users DROP CONSTRAINT FK_4594C9976B00C1CF');
        $this->addSql('DROP SEQUENCE context_id_seq CASCADE');
        $this->addSql('DROP TABLE context');
        $this->addSql('DROP TABLE context_users');
    }
}
