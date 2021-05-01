<?php

use yii\db\Migration;
use yii\db\Schema;

class m126000_000000_gen_form_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%gen_form_table}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_CHAR . '(255) NOT NULL',
            'hash' => Schema::TYPE_CHAR . '(255) NOT NULL',
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'theme' => Schema::TYPE_CHAR . '(255) NOT NULL',
            'extensions' => Schema::TYPE_TEXT,
            'hidden_conf' => Schema::TYPE_TEXT,
            'pages_conf' => Schema::TYPE_TEXT,
            'offer_id' => $this->integer(11)->null(),
        ]);

        $this->addForeignKey('gen_form_table_fk_offer_id', 'gen_form_table', 'offer_id', 'offer', 'offer_id', 'NO ACTION', 'NO ACTION');
        $this->createIndex('genform_user_id', '{{%gen_form_table}}', 'user_id');
    }

    public function down()
    {
        $this->dropTable('{{%gen_form_table}}');
    }
}
