<?php

namespace common\services\customer;

use common\helpers\FishHelper;
use common\models\customer\Customer;
use common\services\ServiceException;
use common\services\ValidateException;

class CustomerCommonService
{
    public function saveCustomer(Customer $customer, $attributes)
    {
        if (isset($attributes['name'])) {
            $customer->name = $attributes['name'];
        }
        if (isset($attributes['phone'])) {
            $customer->phone = Customer::getValidPhoneNumber((string)$attributes['phone']);
        }
        if (isset($attributes['phone_string'])) {
            $customer->phone_string = $customer->phone_string . ', ' . $customer->phone;
        }
        if (isset($attributes['country_id'])) {
            $customer->country_id = (int)$attributes['country_id'];
        }
        if (isset($attributes['city_id']) && !empty($attributes['city_id'])) {
            $customer->city_id = ($attributes['city_id'] == 8) ? 2 : (int)$attributes['city_id'];
            $customer->region_id = ($attributes['city_id'] == 8) ? 2 : (int)$attributes['city_id'];
//            $customer->city_id = (int)$attributes['city_id'];
//            $customer->region_id = (int)$attributes['city_id'];
        }
        if (isset($attributes['region_id']) && !empty($attributes['region_id'])) {
            $customer->city_id = ($attributes['region_id'] == 8) ? 2 : (int)$attributes['region_id'];
            $customer->region_id = ($attributes['region_id'] == 8) ? 2 : (int)$attributes['region_id'];
//            $customer->city_id = (int)$attributes['region_id'];
//            $customer->region_id = (int)$attributes['region_id'];
        }
        if (isset($attributes['address']) && !empty($attributes['address'])) {
            $customer->address = $attributes['address'];
        }
        if (isset($attributes['email']) && !empty($attributes['email'])) {
            if ( !filter_var($attributes['email'], FILTER_VALIDATE_EMAIL)) {
                throw new ServiceException('Invalid email address');
            }
            $customer->email = $attributes['email'];
        }
        if (isset($attributes['pin']) && !empty($attributes['pin'])) {
            $customer->pin = $attributes['pin'];
        }

        if ( !$customer->save()) {
            throw new ServiceException('Something went wrong when saving customer model');
        }
        
        return true;
    }
}