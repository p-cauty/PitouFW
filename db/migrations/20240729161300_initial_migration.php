<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialMigration extends AbstractMigration
{
    public function up(): void
    {
        $initial_sql_schema = file_get_contents(__DIR__ . '/../sql/pitoufw.sql');
        $this->execute($initial_sql_schema);
    }

    public function down(): void
    {
        $this->execute("
            SET FOREIGN_KEY_CHECKS = 0;
            DROP TABLE IF EXISTS `email_queue`;
            DROP TABLE IF EXISTS `email_update`;
            DROP TABLE IF EXISTS `newsletter_email`;
            DROP TABLE IF EXISTS `passwd_reset`;
            DROP TABLE IF EXISTS `user`;
            SET FOREIGN_KEY_CHECKS = 1;
        ");
    }
}