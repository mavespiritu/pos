<?php

use yii\db\Schema;
use yii\db\Migration;

class m171127_032449_create_user_info extends Migration
{
    public function up()
    {
       $tables = Yii::$app->db->schema->getTableNames();
        $dbType = $this->db->driverName;
        $tableOptions_mysql = "CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB";
        $tableOptions_mssql = "";
        $tableOptions_pgsql = "";
        $tableOptions_sqlite = "";
        /* MYSQL */
        if (!in_array('user_info', $tables))  { 
        if ($dbType == "mysql") {
            $this->createTable('{{%user_info}}', [
                'user_id' => 'INT(255) NOT NULL AUTO_INCREMENT',
                0 => 'PRIMARY KEY (`user_id`)',
                'LAST_M' => 'VARCHAR(255) NULL',
                'FIRST_M' => 'VARCHAR(255) NULL',
                'MIDDLE_M' => 'VARCHAR(255) NULL',
                'SUFFIX' => 'VARCHAR(255) NULL',
                'BRANCH_C' => 'INT(2) NULL',
                'MOBILEPHONE' => 'VARCHAR(20) NULL',
            ], $tableOptions_mysql);
        }
        }
         
         
        $this->createIndex('idx_BRANCH_C_211_00','user_info','BRANCH_C',0);
         
        $this->execute('SET foreign_key_checks = 0');
        $this->addForeignKey('fk_BRANCH_C_211_00','{{%branch}}', 'id', '{{%user_info}}', 'BRANCH_C', 'CASCADE', 'CASCADE' );
        $this->addForeignKey('fk_user_2104_03','{{%user_info}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE' );
        $this->execute('SET foreign_key_checks = 1;');
    }

    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->execute('DROP TABLE IF EXISTS `user_info`');
        $this->execute('SET foreign_key_checks = 1;');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
