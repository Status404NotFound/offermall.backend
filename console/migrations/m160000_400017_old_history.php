<?php

use yii\db\Migration;

/**
 * Class m600000_400017_old_history
 */
class m160000_400017_old_history extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $this->createTable('old_history', [
            'customer_id' => $this->primaryKey(11),
            'name' => $this->string(255),
            'phone' => $this->bigInteger(20),
            'address' => $this->string(255),
            'email' => $this->string(255),
            'ip' => $this->bigInteger(20),
            'advert_name' => $this->string(255),
            'country_name' => $this->string(255),
            'iso' => $this->string(255),
            'offer_name' => $this->string(255),
            'advert_id' => $this->integer(11),
            'status' => $this->integer(11),
            'created_at' => 'timestamp NOT NULL',
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('old_history');
    }
}
