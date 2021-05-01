<?php

class m000000_500000_known_sub_id_1 extends \yii\db\Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%known_sub_id_1}}', [
            'id' => $this->primaryKey(11),
            'name' => $this->string(255)->notNull(),
            'alias' => $this->string(255)->notNull(),
        ], $tableOptions);

        /** Insert data */
        Yii::$app->db->createCommand(file_get_contents(__DIR__ . '/sql/known_sub_id_1.sql'))->execute();
    }

    public function safeDown()
    {
        $this->dropTable('{{%known_sub_id_1}}');
    }
}
