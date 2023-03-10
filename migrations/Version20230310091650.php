<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230310091650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resume_user (resume_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_7355178DD262AF09 (resume_id), INDEX IDX_7355178DA76ED395 (user_id), PRIMARY KEY(resume_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resume_user ADD CONSTRAINT FK_7355178DD262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_user ADD CONSTRAINT FK_7355178DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resume_user DROP FOREIGN KEY FK_7355178DD262AF09');
        $this->addSql('ALTER TABLE resume_user DROP FOREIGN KEY FK_7355178DA76ED395');
        $this->addSql('DROP TABLE resume_user');
    }
}
