<?php

namespace common\models\webmaster\parking;

/**
 * This is the ActiveQuery class for [[ParkingDomain]].
 *
 * @see ParkingDomain
 */
class ParkingDomainQuery extends \yii\db\ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        return $this->andWhere('[[parking_domain.is_deleted]]=0');
    }

    /**
     * @inheritdoc
     * @return ParkingDomain[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return ParkingDomain|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
