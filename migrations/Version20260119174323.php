<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260119174323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE match_result (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, player1_score INT DEFAULT NULL, player2_score INT DEFAULT NULL, player1_result VARCHAR(255) DEFAULT NULL, player2_result VARCHAR(255) DEFAULT NULL, scheduled_date DATETIME DEFAULT NULL, played_date DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, moderation_notes LONGTEXT DEFAULT NULL, needs_moderation TINYINT NOT NULL, player1_id INT NOT NULL, player2_id INT NOT NULL, tournament_id INT NOT NULL, INDEX IDX_B2053812C0990423 (player1_id), INDEX IDX_B2053812D22CABCD (player2_id), INDEX IDX_B205381233D1A3E7 (tournament_id), UNIQUE INDEX unique_match_opponents (tournament_id, player1_id, player2_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, gamertag VARCHAR(50) NOT NULL, skill_level INT NOT NULL, main_character VARCHAR(50) NOT NULL, wins INT NOT NULL, losses INT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_98197A65A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, max_players INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, is_verified TINYINT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE match_result ADD CONSTRAINT FK_B2053812C0990423 FOREIGN KEY (player1_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_result ADD CONSTRAINT FK_B2053812D22CABCD FOREIGN KEY (player2_id) REFERENCES player (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE match_result ADD CONSTRAINT FK_B205381233D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE match_result DROP FOREIGN KEY FK_B2053812C0990423');
        $this->addSql('ALTER TABLE match_result DROP FOREIGN KEY FK_B2053812D22CABCD');
        $this->addSql('ALTER TABLE match_result DROP FOREIGN KEY FK_B205381233D1A3E7');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65A76ED395');
        $this->addSql('DROP TABLE match_result');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
