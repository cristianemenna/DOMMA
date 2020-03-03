<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200303171819 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE macro_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE macro (id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, code VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE macro_users (macro_id INT NOT NULL, users_id INT NOT NULL, PRIMARY KEY(macro_id, users_id))');
        $this->addSql('CREATE INDEX IDX_17FEFACBF43A187E ON macro_users (macro_id)');
        $this->addSql('CREATE INDEX IDX_17FEFACB67B3B43D ON macro_users (users_id)');
        $this->addSql('ALTER TABLE macro_users ADD CONSTRAINT FK_17FEFACBF43A187E FOREIGN KEY (macro_id) REFERENCES macro (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE macro_users ADD CONSTRAINT FK_17FEFACB67B3B43D FOREIGN KEY (users_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE macro_users DROP CONSTRAINT FK_17FEFACBF43A187E');
        $this->addSql('DROP SEQUENCE macro_id_seq CASCADE');
        $this->addSql('DROP TABLE macro');
        $this->addSql('DROP TABLE macro_users');
    }
}
