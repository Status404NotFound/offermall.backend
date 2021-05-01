<?php

use yii\db\Migration;

/**
 * Class m180730_142421_user_delivery_api_add_column
 */
class m180730_142421_user_delivery_api_add_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('user_delivery_api_fk_user_id', 'user_delivery_api');
        //$this->dropIndex('user_delivery_api_fk_user_id', 'user_delivery_api');

        $this->dropColumn('user_delivery_api', 'advert_id');
        $this->dropColumn('user_delivery_api', 'advert_name');
        $this->addColumn('user_delivery_api', 'permission_api_id', $this->integer(11)->notNull());

        $this->update('user_delivery_api', ['permission_api_id' => 180], 'api_id = ' . 1);
        $this->update('user_delivery_api', ['permission_api_id' => 182], 'api_id = ' . 2);
        $this->update('user_delivery_api', ['permission_api_id' => 181], 'api_id = ' . 3);
        $this->update('user_delivery_api', ['permission_api_id' => 185], 'api_id = ' . 4);
        $this->update('user_delivery_api', ['permission_api_id' => 184], 'api_id = ' . 5);
        $this->update('user_delivery_api', ['permission_api_id' => 186], 'api_id = ' . 6);
        $this->update('user_delivery_api', ['permission_api_id' => 187], 'api_id = ' . 7);
        $this->update('user_delivery_api', ['permission_api_id' => 183], 'api_id = ' . 8);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180730_142421_user_delivery_api_add_column cannot be reverted.\n";

        return false;
    }
}
