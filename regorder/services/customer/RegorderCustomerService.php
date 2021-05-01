<?php
/**
 * Created by PhpStorm.
 * User: ihor-fish
 * Date: 13.09.17
 * Time: 12:24
 */
namespace regorder\services\customer;

use Yii;
use common\models\customer\Customer;
use common\models\customer\CustomerSystem;

class RegorderCustomerService
{
    public function saveEmail($email, $sid)
    {
        $customerSystem = CustomerSystem::find()->where(['sid' => $sid])->one();
        $customer = Customer::findOne($customerSystem->customer_id);
        $customer->email = $email;
        if($customer->update(['email'])) return true;
        else return $customer->errors;

    }
}