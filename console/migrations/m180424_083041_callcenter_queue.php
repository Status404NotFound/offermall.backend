<?php

use yii\db\Migration;

/**
 * Class m180424_083041_callcenter_queue
 */
class m180424_083041_callcenter_queue extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('call_queue', [
            'queue_id' => $this->primaryKey(),
            'offer' => $this->text(),
            'geo' => $this->text(),
            'language' => $this->text(),
            'attempts' => $this->text(),
            'lead_status' => $this->text(),

            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('call_queue');
    }

}
