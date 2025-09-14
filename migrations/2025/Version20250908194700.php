<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250908194700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store HelloAsso checkout identifier and status in payment entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE t_payment ADD checkout_id VARCHAR(250) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_37043C6E146D8724 ON t_payment (checkout_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE t_payment ADD status VARCHAR(10) DEFAULT 'pending' NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE t_payment DROP checkout_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE t_payment DROP status
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_37043C6E146D8724 ON t_payment
        SQL);
    }
}
