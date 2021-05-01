<?php

use yii\db\Migration;

class m000000_400000_language extends Migration
{
    public function up()
    {
        $this->createTable('language', [
            'language_id' => $this->primaryKey(),
            'name' => $this->string(50),
            'code' => $this->string(10),
        ]);
        Yii::$app->db->createCommand('INSERT INTO language (name, code)
VALUES ("English", "EN")')->execute();
        Yii::$app->db->createCommand('INSERT INTO language (name, code)
VALUES ("Arabic", "AR")')->execute();
    }

    public function safeDown()
    {
        $this->dropTable('language');
    }
}