<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181130151833 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE attached_image (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, linked_post_id INT NOT NULL, path LONGTEXT NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_C86CE17F7E3C61F9 (owner_id), INDEX IDX_C86CE17F20C13BBB (linked_post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE attached_image ADD CONSTRAINT FK_C86CE17F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE attached_image ADD CONSTRAINT FK_C86CE17F20C13BBB FOREIGN KEY (linked_post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post CHANGE image image LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE attached_image');
        $this->addSql('ALTER TABLE post CHANGE image image LONGBLOB DEFAULT NULL');
    }
}
