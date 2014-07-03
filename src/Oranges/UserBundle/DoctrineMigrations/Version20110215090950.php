<?php

namespace Oranges\UserBundle\DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

class Version20110215090950 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->_addSql("CREATE TABLE user_forgot_password_request (id INT AUTO_INCREMENT NOT NULL, request_id VARCHAR(33) NOT NULL, user_id INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB");
    }

    public function down(Schema $schema)
    {
        $this->_addSql("DROP TABLE user_forgot_password_request");
    }
}