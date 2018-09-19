<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180919160329 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE strategy ADD decisions_json JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE decision DROP FOREIGN KEY FK_84ACBE48727ACA70');
        $this->addSql('ALTER TABLE decision DROP FOREIGN KEY FK_84ACBE48D5CAD932');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48727ACA70 FOREIGN KEY (parent_id) REFERENCES decision (id)');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48D5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE decision DROP FOREIGN KEY FK_84ACBE48D5CAD932');
        $this->addSql('ALTER TABLE decision DROP FOREIGN KEY FK_84ACBE48727ACA70');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48D5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48727ACA70 FOREIGN KEY (parent_id) REFERENCES decision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE strategy DROP decisions_json');
    }
}
