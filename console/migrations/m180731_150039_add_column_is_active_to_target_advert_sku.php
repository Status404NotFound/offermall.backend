<?php

use yii\db\Migration;

/**
 * Class m180731_150039_add_column_is_active_to_target_advert_sku
 */
class m180731_150039_add_column_is_active_to_target_advert_sku extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('target_advert_sku', 'is_active', 'integer(4)');
    }

}
