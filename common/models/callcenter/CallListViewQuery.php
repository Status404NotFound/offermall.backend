<?php

namespace common\models\callcenter;

/**
 * This is the ActiveQuery class for [[CallListView]].
 *
 * @see CallListView
 */
class CallListViewQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return CallListView[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return CallListView|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
