<?php
/**
 * Created by PhpStorm.
 * User: ihor-fish
 * Date: 12.10.17
 * Time: 13:09
 */

namespace callcenter\services\operator_activity;


use common\models\callcenter\OperatorPcs;

class SkuCallcenterService
{
//    public $operator_id;
//    public $order_id;
//    public $old_pcs;
//    public $new_pcs;
//    public $upsale;

    public function operatorUpsalesSave($order_id, $old_pcs, $new_pcs)
    {
        $operatorPcs = OperatorPcs::find()->where(['order_id' => $order_id])->one();
        if ($operatorPcs)
        {
            if ($operatorPcs->pcs_new != $new_pcs)
            {
                $operatorPcs->pcs_new = $new_pcs;
                $operatorPcs->up_sale = $new_pcs - 1;
                $operatorPcs->operator_id = \Yii::$app->user->id;

                if ($operatorPcs->update()) return true;
                else $operatorPcs->errors;
            }

            return true;

        }else{
            $operatorPcs = new OperatorPcs();
            $operatorPcs->pcs_old = 1;
            $operatorPcs->pcs_new = $new_pcs;
            $operatorPcs->operator_id = \Yii::$app->user->id;
            $operatorPcs->order_id = $order_id;
            $operatorPcs->up_sale = $new_pcs - 1;

            if ($operatorPcs->save()) return true;
            else $operatorPcs->errors;
        }
    }

}