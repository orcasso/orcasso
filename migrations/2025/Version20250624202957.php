<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250624202957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Define member log table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE t_member_log (
            id INT AUTO_INCREMENT NOT NULL,
            member_id INT NOT NULL,
            action VARCHAR(8) NOT NULL,
            logged_at DATETIME NOT NULL,
            object_id VARCHAR(64) DEFAULT NULL,
            object_class VARCHAR(191) NOT NULL,
            version INT NOT NULL,
            data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\',
            username VARCHAR(191) DEFAULT NULL,
            INDEX IDX_EACE75EE7597D3FE (member_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE t_member_log
            ADD CONSTRAINT FK_EACE75EE7597D3FE FOREIGN KEY (member_id) REFERENCES t_member (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE t_member_log DROP FOREIGN KEY FK_EACE75EE7597D3FE');
        $this->addSql('DROP TABLE t_member_log');
    }
}
