<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526202648 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fiscal period';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE t_fiscal_period (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                is_current TINYINT(1) DEFAULT 0 NOT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                UNIQUE INDEX UNIQ_12A1DF85E237E06 (name),
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO t_fiscal_period (name, is_current, created_at, updated_at)
            VALUES ('2025/2026', 0, NOW(), NOW())
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE t_order ADD fiscal_period_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE t_order SET fiscal_period_id = (SELECT id FROM t_fiscal_period WHERE name = '2025/2026')
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE t_order MODIFY fiscal_period_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE t_order ADD CONSTRAINT FK_4B98F5E13CB44A99 FOREIGN KEY (fiscal_period_id) REFERENCES t_fiscal_period (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4B98F5E13CB44A99 ON t_order (fiscal_period_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE t_order DROP FOREIGN KEY FK_4B98F5E13CB44A99
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4B98F5E13CB44A99 ON t_order
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE t_fiscal_period
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE t_order DROP fiscal_period_id
        SQL);
    }
}
