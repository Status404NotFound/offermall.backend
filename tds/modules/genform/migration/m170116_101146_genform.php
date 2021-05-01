<?php

use yii\db\Migration;
use yii\db\Schema;

class m170116_101146_genform extends Migration
{
    public function up()
    {
        $this->createTable('genform',[
            'id' => Schema::TYPE_PK,
            'id_user' => Schema::TYPE_INTEGER . ' NOT NULL',
            'id_site' => Schema::TYPE_INTEGER . ' NOT NULL',
            'title' => Schema::TYPE_STRING,
            'fields' => Schema::TYPE_TEXT,
            'design' => Schema::TYPE_TEXT,
            'modules' => Schema::TYPE_TEXT,
        ]);

        $this->createIndex('genform_id', 'genform', 'id');
    }

    public function down()
    {
        $this->dropTable('genform');
    }
}
