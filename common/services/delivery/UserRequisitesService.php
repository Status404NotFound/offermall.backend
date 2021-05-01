<?php

namespace common\services\delivery;

use Yii;
use common\models\delivery\UserRequisites;
use common\services\ServiceException;
use common\services\ValidateException;

/**
 * Class UserRequisitesService
 * @package common\services\delivery
 */
class UserRequisitesService
{

    private $owner_id;

    /**
     * UserRequisitesService constructor.
     */
    public function __construct()
    {
        $this->owner_id = Yii::$app->user->identity->getOwnerId();
    }

    /**
     * @param string|null $advert_id
     * @return array|UserRequisites[]|\yii\db\ActiveRecord[]
     */
    public function getList(string $advert_id = null)
    {
        $query = UserRequisites::find()
            ->select([
                'user_requisites.requisite_id',
                'user_requisites.user_id',
                'user_requisites.description',
                'user_requisites.created_at',
                'user_requisites.geo_id',
                'geo.geo_name',
                'geo.iso',
            ])
            ->leftJoin('user', 'user.id = user_requisites.user_id')
            ->leftJoin('geo', 'geo.geo_id = user_requisites.geo_id');

        if (!empty($advert_id)) {
            $query->where(['user_requisites.user_id' => $advert_id]);
        } else {
            if (!is_null($this->owner_id)) $query->where(['user_requisites.user_id' => $this->owner_id]);
        }

        $list = $query
            ->asArray()
            ->all();

        return $list;
    }

    /**
     * @param array $request
     * @return bool
     * @throws ValidateException
     */
    public function save(array $request)
    {
        $requisite = isset($request['requisite_id']) ?
            UserRequisites::findOne(['requisite_id' => $request['requisite_id']]) : new UserRequisites();

        $requisite->setAttributes([
            'user_id' => isset($request['user_id']) ? $request['user_id'] : $this->owner_id,
            'geo_id' => $request['geo_id'],
            'description' => $request['description']
        ]);

        if (!$requisite->save()) {
            throw new ValidateException($requisite->errors);
        }

        return true;
    }

    /**
     * @param array $request
     * @return bool
     * @throws ServiceException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(array $request)
    {
        $requisite = UserRequisites::findOne([
            'requisite_id' => $request['requisite_id'],
        ]);

        if (!$requisite->delete()) {
            throw new ServiceException('Error! Can not delete');
        }

        return true;
    }
}