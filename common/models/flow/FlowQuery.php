<?php

namespace common\models\flow;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Flow]].
 *
 * @see Flow
 */
class FlowQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere('[[active]]=1');
    }

    /**
     * @inheritdoc
     * @return Flow[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Flow|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
