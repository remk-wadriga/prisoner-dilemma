<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180920065130 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE strategy (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, decisions_data JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, status VARCHAR(8) NOT NULL, INDEX IDX_144645EDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE decision (id INT AUTO_INCREMENT NOT NULL, strategy_id INT NOT NULL, parent_id INT DEFAULT NULL, step SMALLINT NOT NULL, return_step SMALLINT DEFAULT NULL, type VARCHAR(6) NOT NULL, INDEX IDX_84ACBE48D5CAD932 (strategy_id), INDEX IDX_84ACBE48727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', access_token VARCHAR(64) NOT NULL, renew_token VARCHAR(64) NOT NULL, password VARCHAR(64) NOT NULL, salt VARCHAR(32) NOT NULL, access_token_expired_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE strategy ADD CONSTRAINT FK_144645EDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48D5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id)');
        $this->addSql('ALTER TABLE decision ADD CONSTRAINT FK_84ACBE48727ACA70 FOREIGN KEY (parent_id) REFERENCES decision (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE decision DROP FOREIGN KEY FK_84ACBE48D5CAD932');
        $this->addSql('ALTER TABLE decision DROP FOREIGN KEY FK_84ACBE48727ACA70');
        $this->addSql('ALTER TABLE strategy DROP FOREIGN KEY FK_144645EDA76ED395');
        $this->addSql('DROP TABLE strategy');
        $this->addSql('DROP TABLE decision');
        $this->addSql('DROP TABLE user');
    }
}
