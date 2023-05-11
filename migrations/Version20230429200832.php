<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230429200832 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE job (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, resume_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TINYTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, published_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', requirements LONGTEXT NOT NULL, category VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, salary DOUBLE PRECISION DEFAULT NULL, company VARCHAR(255) NOT NULL, INDEX IDX_FBD8E0F8F675F31B (author_id), INDEX IDX_FBD8E0F8D262AF09 (resume_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resume (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, phone VARCHAR(255) DEFAULT NULL, summary VARCHAR(255) NOT NULL, education LONGTEXT DEFAULT NULL, experience LONGTEXT DEFAULT NULL, skills LONGTEXT DEFAULT NULL, certifications LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_60C1D0A0F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id)');
        $this->addSql('ALTER TABLE resume ADD CONSTRAINT FK_60C1D0A0F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8F675F31B');
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8D262AF09');
        $this->addSql('ALTER TABLE resume DROP FOREIGN KEY FK_60C1D0A0F675F31B');
        $this->addSql('DROP TABLE job');
        $this->addSql('DROP TABLE resume');
        $this->addSql('DROP TABLE user');
    }
}
