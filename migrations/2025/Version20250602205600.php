<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250602205600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'First version of database schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE t_activity (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_789515005E237E06 (name),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_configuration (
            id INT AUTO_INCREMENT NOT NULL,
            item VARCHAR(255) NOT NULL,
            value LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_80FA20161F1B251E (item),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_legal_representative (
            id INT AUTO_INCREMENT NOT NULL,
            member_id INT NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone_number VARCHAR(35) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_7E2B4B67597D3FE (member_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_member (
            id INT AUTO_INCREMENT NOT NULL,
            gender VARCHAR(10) NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            birth_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\',
            email VARCHAR(255) NOT NULL,
            phone_number VARCHAR(35) NOT NULL,
            street1 VARCHAR(255) NOT NULL,
            street2 VARCHAR(255) NOT NULL,
            street3 VARCHAR(255) NOT NULL,
            postal_code VARCHAR(10) NOT NULL,
            city VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_member_document (
            id INT AUTO_INCREMENT NOT NULL,
            member_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size INT NOT NULL,
            mime_type VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_2FB4299C7597D3FE (member_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_order (
            id INT AUTO_INCREMENT NOT NULL,
            member_id INT NOT NULL,
            identifier VARCHAR(20) NOT NULL,
            notes LONGTEXT DEFAULT \'\' NOT NULL,
            total_amount NUMERIC(10, 2) NOT NULL,
            paid_amount NUMERIC(10, 2) NOT NULL,
            status VARCHAR(10) DEFAULT \'pending\' NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_4B98F5E1772E836A (identifier),
            INDEX IDX_4B98F5E17597D3FE (member_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_order_line (
            id INT AUTO_INCREMENT NOT NULL,
            order_id INT NOT NULL,
            subscribed_activity_id INT DEFAULT NULL,
            position SMALLINT NOT NULL,
            type VARCHAR(255) NOT NULL,
            label LONGTEXT NOT NULL,
            allowance_percentage NUMERIC(5, 2) DEFAULT NULL,
            allowance_base_amount NUMERIC(10, 2) DEFAULT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_C8B0B3918D9F6D38 (order_id),
            INDEX IDX_C8B0B3913010C22D (subscribed_activity_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_order_form (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT NOT NULL,
            order_main_line_label VARCHAR(255) NOT NULL,
            order_main_line_amount NUMERIC(10, 2) NOT NULL,
            enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_1F79C7582B36786B (title),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_order_form_field (
            id INT AUTO_INCREMENT NOT NULL,
            form_id INT NOT NULL,
            position SMALLINT NOT NULL,
            question VARCHAR(255) NOT NULL,
            type VARCHAR(20) NOT NULL,
            required TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_6B681CCE5FF69B7D (form_id),
            UNIQUE INDEX UNIQ_6B681CCE5FF69B7DB6F7494E (form_id, question),
            UNIQUE INDEX UNIQ_6B681CCE5FF69B7D462CE4F5 (form_id, position),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_order_form_field_choice (
            id INT AUTO_INCREMENT NOT NULL,
            field_id INT NOT NULL,
            activity_id INT DEFAULT NULL,
            activity_amount NUMERIC(10, 2) NOT NULL,
            allowance_label VARCHAR(255) NOT NULL,
            allowance_percentage NUMERIC(5, 2) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_95CBD7A9443707B0 (field_id),
            INDEX IDX_95CBD7A981C06096 (activity_id),
            UNIQUE INDEX UNIQ_95CBD7A9443707B081C06096 (field_id, activity_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_order_form_reply (
            id INT AUTO_INCREMENT NOT NULL,
            form_id INT NOT NULL,
            order_id INT DEFAULT NULL,
            member_data JSON NOT NULL COMMENT \'(DC2Type:json)\',
            notes LONGTEXT DEFAULT \'\' NOT NULL,
            field_values JSON NOT NULL COMMENT \'(DC2Type:json)\',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_CD359F765FF69B7D (form_id),
            UNIQUE INDEX UNIQ_CD359F768D9F6D38 (order_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_payment (
            id INT AUTO_INCREMENT NOT NULL,
            member_id INT NOT NULL,
            issued_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            received_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            amount NUMERIC(10, 2) NOT NULL,
            identifier VARCHAR(255) NOT NULL,
            notes LONGTEXT NOT NULL,
            method VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_6D28840D7597D3FE (member_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_payment_order (
            id INT AUTO_INCREMENT NOT NULL,
            payment_id INT NOT NULL,
            order_id INT NOT NULL,
            amount NUMERIC(10, 2) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            INDEX IDX_A260A52A4C3A3BB (payment_id),
            INDEX IDX_A260A52A8D9F6D38 (order_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE t_user (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
            password VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_37E5BF3BE7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE t_member_document ADD CONSTRAINT FK_2FB4299C7597D3FE FOREIGN KEY (member_id) REFERENCES t_member (id)');
        $this->addSql('ALTER TABLE t_order_form_field ADD CONSTRAINT FK_6B681CCE5FF69B7D FOREIGN KEY (form_id) REFERENCES t_order_form (id)');
        $this->addSql('ALTER TABLE t_order_form_field_choice ADD CONSTRAINT FK_95CBD7A9443707B0 FOREIGN KEY (field_id) REFERENCES t_order_form_field (id)');
        $this->addSql('ALTER TABLE t_order_form_field_choice ADD CONSTRAINT FK_95CBD7A981C06096 FOREIGN KEY (activity_id) REFERENCES t_activity (id)');
        $this->addSql('ALTER TABLE t_order_form_reply ADD CONSTRAINT FK_CD359F765FF69B7D FOREIGN KEY (form_id) REFERENCES t_order_form (id)');
        $this->addSql('ALTER TABLE t_order_form_reply ADD CONSTRAINT FK_CD359F768D9F6D38 FOREIGN KEY (order_id) REFERENCES t_order (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE t_payment ADD CONSTRAINT FK_6D28840D7597D3FE FOREIGN KEY (member_id) REFERENCES t_member (id)');
        $this->addSql('ALTER TABLE t_payment_order ADD CONSTRAINT FK_A260A52A4C3A3BB FOREIGN KEY (payment_id) REFERENCES t_payment (id)');
        $this->addSql('ALTER TABLE t_payment_order ADD CONSTRAINT FK_A260A52A8D9F6D38 FOREIGN KEY (order_id) REFERENCES t_order (id)');
        $this->addSql('ALTER TABLE t_legal_representative ADD CONSTRAINT FK_7E2B4B67597D3FE FOREIGN KEY (member_id) REFERENCES t_member (id)');
        $this->addSql('ALTER TABLE t_order ADD CONSTRAINT FK_4B98F5E17597D3FE FOREIGN KEY (member_id) REFERENCES t_member (id)');
        $this->addSql('ALTER TABLE t_order_line ADD CONSTRAINT FK_C8B0B3918D9F6D38 FOREIGN KEY (order_id) REFERENCES t_order (id)');
        $this->addSql('ALTER TABLE t_order_line ADD CONSTRAINT FK_C8B0B3913010C22D FOREIGN KEY (subscribed_activity_id) REFERENCES t_activity (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE t_member_document DROP FOREIGN KEY FK_2FB4299C7597D3FE');
        $this->addSql('ALTER TABLE t_order_form_field DROP FOREIGN KEY FK_6B681CCE5FF69B7D');
        $this->addSql('ALTER TABLE t_order_form_field_choice DROP FOREIGN KEY FK_95CBD7A9443707B0');
        $this->addSql('ALTER TABLE t_order_form_field_choice DROP FOREIGN KEY FK_95CBD7A981C06096');
        $this->addSql('ALTER TABLE t_order_form_reply DROP FOREIGN KEY FK_CD359F765FF69B7D');
        $this->addSql('ALTER TABLE t_order_form_reply DROP FOREIGN KEY FK_CD359F768D9F6D38');
        $this->addSql('ALTER TABLE t_payment DROP FOREIGN KEY FK_6D28840D7597D3FE');
        $this->addSql('ALTER TABLE t_payment_order DROP FOREIGN KEY FK_A260A52A4C3A3BB');
        $this->addSql('ALTER TABLE t_payment_order DROP FOREIGN KEY FK_A260A52A8D9F6D38');
        $this->addSql('ALTER TABLE t_legal_representative DROP FOREIGN KEY FK_7E2B4B67597D3FE');
        $this->addSql('ALTER TABLE t_order DROP FOREIGN KEY FK_4B98F5E17597D3FE');
        $this->addSql('ALTER TABLE t_order_line DROP FOREIGN KEY FK_C8B0B3918D9F6D38');
        $this->addSql('ALTER TABLE t_order_line DROP FOREIGN KEY FK_C8B0B3913010C22D');
        $this->addSql('DROP TABLE t_member_document');
        $this->addSql('DROP TABLE t_order_form');
        $this->addSql('DROP TABLE t_order_form_field');
        $this->addSql('DROP TABLE t_order_form_field_choice');
        $this->addSql('DROP TABLE t_order_form_reply');
        $this->addSql('DROP TABLE t_payment');
        $this->addSql('DROP TABLE t_payment_order');
        $this->addSql('DROP TABLE t_activity');
        $this->addSql('DROP TABLE t_configuration');
        $this->addSql('DROP TABLE t_legal_representative');
        $this->addSql('DROP TABLE t_member');
        $this->addSql('DROP TABLE t_order');
        $this->addSql('DROP TABLE t_order_line');
        $this->addSql('DROP TABLE t_user');
    }
}
