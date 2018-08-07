<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180803110304 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE webobject CHANGE subdomain subdomain VARCHAR(255) DEFAULT NULL, CHANGE suffix suffix VARCHAR(255) DEFAULT NULL, CHANGE registrabledomain registrabledomain VARCHAR(255) DEFAULT NULL, CHANGE ip ip VARCHAR(255) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE webobject CHANGE url url VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE subdomain subdomain VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE suffix suffix VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE registrabledomain registrabledomain VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE ip ip VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
