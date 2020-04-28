<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200424175354 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE import DROP CONSTRAINT FK_9D4ECE1D6B00C1CF');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1D6B00C1CF FOREIGN KEY (context_id) REFERENCES context (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('
                            CREATE OR REPLACE FUNCTION remove_expired_contexts() RETURNS text AS $$
                            DECLARE query RECORD;
                            DECLARE iterator INTEGER := 0;
                            BEGIN
                                FOR query IN SELECT id, lower(title) as title
                                FROM context
                                WHERE created_at + make_interval(days => duration) <= CURRENT_TIMESTAMP
                              LOOP
                                EXECUTE format(\'DROP SCHEMA %I CASCADE\', query.title);
                                DELETE FROM context WHERE id = query.id;
                                    iterator := iterator + 1;
                               END LOOP;
                            RETURN iterator || \' contextes supprimés\';
                            END;
                            $$ LANGUAGE plpgsql;
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE import DROP CONSTRAINT fk_9d4ece1d6b00c1cf');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT fk_9d4ece1d6b00c1cf FOREIGN KEY (context_id) REFERENCES context (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP FUNCTION remove_expired_contexts()');
    }
}
