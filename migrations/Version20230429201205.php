<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230429201205 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resume_job (resume_id INT NOT NULL, job_id INT NOT NULL, INDEX IDX_E9025260D262AF09 (resume_id), INDEX IDX_E9025260BE04EA9 (job_id), PRIMARY KEY(resume_id, job_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resume_job ADD CONSTRAINT FK_E9025260D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resume_job ADD CONSTRAINT FK_E9025260BE04EA9 FOREIGN KEY (job_id) REFERENCES job (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job DROP FOREIGN KEY FK_FBD8E0F8D262AF09');
        $this->addSql('DROP INDEX IDX_FBD8E0F8D262AF09 ON job');
        $this->addSql('ALTER TABLE job DROP resume_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resume_job DROP FOREIGN KEY FK_E9025260D262AF09');
        $this->addSql('ALTER TABLE resume_job DROP FOREIGN KEY FK_E9025260BE04EA9');
        $this->addSql('DROP TABLE resume_job');
        $this->addSql('ALTER TABLE job ADD resume_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE job ADD CONSTRAINT FK_FBD8E0F8D262AF09 FOREIGN KEY (resume_id) REFERENCES resume (id)');
        $this->addSql('CREATE INDEX IDX_FBD8E0F8D262AF09 ON job (resume_id)');
    }
}
