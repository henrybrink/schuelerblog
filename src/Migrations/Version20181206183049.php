<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181206183049 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attached_image ADD linked_page_id INT DEFAULT NULL, CHANGE linked_post_id linked_post_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE attached_image ADD CONSTRAINT FK_C86CE17F670E5B73 FOREIGN KEY (linked_page_id) REFERENCES page (id)');
        $this->addSql('CREATE INDEX IDX_C86CE17F670E5B73 ON attached_image (linked_page_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE attached_image DROP FOREIGN KEY FK_C86CE17F670E5B73');
        $this->addSql('DROP INDEX IDX_C86CE17F670E5B73 ON attached_image');
        $this->addSql('ALTER TABLE attached_image DROP linked_page_id, CHANGE linked_post_id linked_post_id INT NOT NULL');
    }
}
