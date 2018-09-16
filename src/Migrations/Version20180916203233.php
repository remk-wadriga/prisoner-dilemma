<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180916203233 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE decision (id INT AUTO_INCREMENT NOT NULL, strategy_id INT NOT NULL, parent_id INT DEFAULT NULL, step SMALLINT NOT NULL, return_step SMALLINT DEFAULT NULL, type VARCHAR(6) NOT NULL, INDEX IDX_84ACBE48D5CAD932 (strategy_id), INDEX IDX_84ACBE48727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48D5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48727ACA70 FOREIGN KEY (parent_id) REFERENCES decision (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE decision DROP FOREIGN KEY FK_84ACBE48727ACA70');
        $this->addSql('DROP TABLE decision');
    }
}
