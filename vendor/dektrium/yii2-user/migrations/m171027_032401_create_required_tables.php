<?php

use yii\db\Schema;
use yii\db\Migration;

class m171027_032401_create_required_tables extends Migration
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
        if (!in_array('branch', $tables))  { 
        if ($dbType == "mysql") {
            $this->createTable('{{%branch}}', [
                'id' => 'INT(255) NOT NULL AUTO_INCREMENT',
                0 => 'PRIMARY KEY (`id`)',
                'code' => 'VARCHAR(10) NOT NULL',
                'name' => 'VARCHAR(200) NOT NULL'
            ], $tableOptions_mysql);
        }
        }
         
        $this->execute('SET foreign_key_checks = 0');
        $this->insert('{{%branch}}',['code'=>'0','name'=>'MANILA']);
        $this->insert('{{%branch}}',['code'=>'1','name'=>'MARIKINA']);
        $this->insert('{{%branch}}',['code'=>'2','name'=>'QC']);
        $this->insert('{{%branch}}',['code'=>'3','name'=>'ZAMBOANGA']);
        $this->insert('{{%branch}}',['code'=>'4','name'=>'BAGUIO']);
        $this->insert('{{%branch}}',['code'=>'5','name'=>'BATAAN']);
        $this->insert('{{%branch}}',['code'=>'6','name'=>'DAGUPAN']);
        $this->insert('{{%branch}}',['code'=>'7','name'=>'KORONADAL']);
        $this->insert('{{%branch}}',['code'=>'8','name'=>'ILEARN']);
        $this->insert('{{%branch}}',['code'=>'9','name'=>'MIDWIFERY']);
        $this->execute('SET foreign_key_checks = 1;');
    }

    public function down()
    {
        $this->execute('SET foreign_key_checks = 0');
        $this->execute('DROP TABLE IF EXISTS `branch`');
        $this->execute('SET foreign_key_checks = 1;');
    }
}
