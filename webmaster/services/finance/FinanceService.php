<?php

namespace webmaster\services\finance;

use common\modules\user\models\tables\User;
use Yii;
use common\models\webmaster\WmProfile;
use common\models\webmaster\WmCheckout;
use webmaster\traits\TotalTrait;
use common\services\ValidateException;
use yii\helpers\ArrayHelper;

class FinanceService
{
    use TotalTrait;

    /**
     * @return array|WmCheckout[]|WmProfile[]|\yii\db\ActiveRecord[]
     */
    public function getFinanceData()
    {
        $model = WmProfile::find()
            ->select([
                'wm_profile.card',
                'wm_checkout.amount',
                'wm_checkout.status',
                'wm_checkout.comment',
                "DATE_FORMAT(`wm_checkout`.created_at, '%d.%m.%Y %H:%i') AS date",
            ])
            ->where(['wm_checkout.wm_id' => Yii::$app->user->identity->getId()])
            ->innerJoin('wm_checkout', 'wm_checkout.wm_id = wm_profile.wm_id')
            ->orderBy(['date' => SORT_DESC])
            ->asArray()
            ->all();

        foreach ($model as $key => $value) {
            $model[$key]['status'] = WmCheckout::statusLabels($value['status']);
        }

        return $model;
    }

    /**
     * @param $request
     * @throws ValidateException
     */
    public function requestPay($request)
    {
        $wm_id = Yii::$app->user->identity->getId();
        $checkout = new WmCheckout();

        if(isset($request['comment'])){
            $checkout->setAttributes([
                'wm_id' => $wm_id,
                'wm_username' => User::findOne($wm_id)->username,
                'amount' => $request['amount'],
                'comment' => $request['comment'],
                'status' => WmCheckout::IN_PROCESSING
            ]);
        } else {
            $checkout->setAttributes([
                'wm_id' => $wm_id,
                'wm_username' => User::findOne($wm_id)->username,
                'amount' => $request['amount'],
                'status' => WmCheckout::IN_PROCESSING
            ]);
        }

        if (!$checkout->save())
            throw new ValidateException($checkout->errors);
    }

    /**
     * @param $models
     * @param $value
     * @return int|mixed
     */
    public function totalCnt($models, $value)
    {
        $total = 0;

        foreach ($models as $model) {
            $total += ArrayHelper::getValue($model, $value);
        }
        return $total;
    }

    public function getAllProcessingOrders()
    {
        return WmCheckout::find()->where(['status' => 0])->all();
    }

    public function getAllCompletedOrders()
    {
        return WmCheckout::find()->where(['!=', 'status', 0])->all();
    }
}