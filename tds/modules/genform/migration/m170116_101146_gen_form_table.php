<?php

use yii\db\Migration;
use yii\db\Schema;

class m170116_101146_gen_form_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%gen_form_table}}',[
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_CHAR . '(255) NOT NULL',
            'hash' => Schema::TYPE_CHAR . '(255) NOT NULL',
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'theme' => Schema::TYPE_CHAR . '(255) NOT NULL',
            'extensions' => Schema::TYPE_TEXT,
            'hidden_conf' => Schema::TYPE_TEXT,
            'pages_conf' => Schema::TYPE_TEXT,
        ]);

        $this->createIndex('genform_user_id', '{{%gen_form_table}}', 'user_id');
    }

    public function down()
    {
        $this->dropTable('{{%gen_form_table}}');
    }
}
