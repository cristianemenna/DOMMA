<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200222145634 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE macros_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE macros (id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, code VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE macros_users (macros_id INT NOT NULL, users_id INT NOT NULL, PRIMARY KEY(macros_id, users_id))');
        $this->addSql('CREATE INDEX IDX_43C526265038DB06 ON macros_users (macros_id)');
        $this->addSql('CREATE INDEX IDX_43C5262667B3B43D ON macros_users (users_id)');
        $this->addSql('ALTER TABLE macros_users ADD CONSTRAINT FK_43C526265038DB06 FOREIGN KEY (macros_id) REFERENCES macros (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE macros_users ADD CONSTRAINT FK_43C5262667B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SCHEMA context_1');
        $this->addSql('CREATE SCHEMA context_1478');
        $this->addSql('ALTER TABLE macros_users DROP CONSTRAINT FK_43C526265038DB06');
        $this->addSql('DROP SEQUENCE macros_id_seq CASCADE');
        $this->addSql('DROP TABLE macros');
        $this->addSql('DROP TABLE macros_users');
    }
}
