<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181028183015 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_result (id INT AUTO_INCREMENT NOT NULL, game_id INT NOT NULL, strategy_id INT NOT NULL, result INT NOT NULL, INDEX IDX_6E5F6CDBE48FD905 (game_id), INDEX IDX_6E5F6CDBD5CAD932 (strategy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE individual_game_result (id INT AUTO_INCREMENT NOT NULL, game_result_id INT NOT NULL, partner_id INT NOT NULL, result INT NOT NULL, partner_result INT NOT NULL, INDEX IDX_E3137191D5017741 (game_result_id), INDEX IDX_E31371919393F8FE (partner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game_result ADD CONSTRAINT FK_6E5F6CDBE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE game_result ADD CONSTRAINT FK_6E5F6CDBD5CAD932 FOREIGN KEY (strategy_id) REFERENCES strategy (id)');
        $this->addSql('ALTER TABLE individual_game_result ADD CONSTRAINT FK_E3137191D5017741 FOREIGN KEY (game_result_id) REFERENCES game_result (id)');
        $this->addSql('ALTER TABLE individual_game_result ADD CONSTRAINT FK_E31371919393F8FE FOREIGN KEY (partner_id) REFERENCES strategy (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE game_result DROP FOREIGN KEY FK_6E5F6CDBE48FD905');
        $this->addSql('ALTER TABLE individual_game_result DROP FOREIGN KEY FK_E3137191D5017741');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_result');
        $this->addSql('DROP TABLE individual_game_result');
    }
}
