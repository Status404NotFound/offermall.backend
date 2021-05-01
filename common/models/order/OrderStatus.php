<?php

namespace common\models\order;

use ReflectionClass;
use webmaster\models\statistics\Statistics;
use Yii;

class OrderStatus
{
    public const NOT_VALID = 0;
    public const NOT_VALID_CHECKED = 1;
    
    public const PENDING = 10;
    public const BACK_TO_PENDING = 11;
    
    public const CANCELED = 20;
    public const REJECTED = 21;
    public const NOT_PAID = 22;
    
    public const WAITING_DELIVERY = 40;
    public const DELIVERY_IN_PROGRESS = 50;
    
    public const SUCCESS_DELIVERY = 100;
    public const RETURNED = 101;

    /**
     * @inheritdoc
     */
    public static function attributeLabels($order_status = null)
    {
        $statuses = [
            self::PENDING => Yii::t('app', 'Pending'),
            self::BACK_TO_PENDING => Yii::t('app', 'Back To Pending'),
            self::WAITING_DELIVERY => Yii::t('app', 'Waiting Delivery'),
            self::DELIVERY_IN_PROGRESS => Yii::t('app', 'Delivery In Progress'),
            self::SUCCESS_DELIVERY => Yii::t('app', 'Success Delivery'),
            self::REJECTED => Yii::t('app', 'Rejected'),
            self::CANCELED => Yii::t('app', 'Canceled'),
            self::NOT_VALID => Yii::t('app', 'Not Valid'),
            self::NOT_VALID_CHECKED => Yii::t('app', 'Not Valid Checked'),
            self::NOT_PAID => Yii::t('app', 'Not Paid'),
            self::RETURNED => Yii::t('app', 'Returned'),
        ];

        return $order_status !== null ? $statuses[$order_status] : $statuses;
    }

    /**
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            ['status_id' => self::PENDING, 'status_name' => 'Pending'],
            ['status_id' => self::BACK_TO_PENDING, 'status_name' => 'Back To Pending'],
            ['status_id' => self::WAITING_DELIVERY, 'status_name' => 'Waiting Delivery'],
            ['status_id' => self::DELIVERY_IN_PROGRESS, 'status_name' => 'Delivery In Progress'],
            ['status_id' => self::SUCCESS_DELIVERY, 'status_name' => 'Success Delivery'],
            ['status_id' => self::REJECTED, 'status_name' => 'Rejected'],
            ['status_id' => self::CANCELED, 'status_name' => 'Canceled'],
            ['status_id' => self::NOT_VALID, 'status_name' => 'Not Valid'],
            ['status_id' => self::NOT_VALID_CHECKED, 'status_name' => 'Not Valid Checked'],
            ['status_id' => self::NOT_PAID, 'status_name' => 'Not Paid'],
            ['status_id' => self::RETURNED, 'status_name' => 'Returned'],
        ];
    }
    
    /**
     * @return array
     */
    public static function getWmFilterStatuses(): array
    {
        return [
            ['status_id' => Statistics::PENDING, 'status_name' => 'Pending'],
            ['status_id' => Statistics::APPROVED, 'status_name' => 'Approved'],
            ['status_id' => Statistics::SUCCESS_DELIVERY, 'status_name' => 'Success Delivery'],
            ['status_id' => Statistics::REJECTED, 'status_name' => 'Rejected'],
            ['status_id' => Statistics::NOT_VALID, 'status_name' => 'Not Valid'],
        ];
    }

    /**
     * @param $order_status
     * @return bool
     */
    public static function statusNeedReason($order_status)
    {
        $reason_statuses = StatusReason::getStatusesId();
        foreach ($reason_statuses as $status) {
            if ($order_status == $status) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param $order_status
     * @return bool
     */
    public function isValid($order_status)
    {
        $is_valid = false;
        foreach ($this->getValidStatuses() as $st_key => $st_name) {
            if ($order_status == $st_key) {
                $is_valid = true;
            }
        }

        return $is_valid;
    }
    
    /**
     * @param array $order_statuses
     *
     * @return string of statuses for select IN array like: (0, 2, 52, 34, 62)
     * @throws \ReflectionException
     */
    public static function sqlFormatFindByOrderStatuses(array $order_statuses): string
    {
        $reflectionClass = new ReflectionClass(__CLASS__);
        $statuses = "";
        
        foreach ($reflectionClass->getConstants() as $status) {
            if (\in_array($status, $order_statuses, true)) {
                $statuses .= $status . ", ";
            }
        }
        
        $in = "(" . $statuses . ")";
        $length = \strlen($in);
        
        return $length >= 5
            ? substr_replace($in, '', $length - 3, 2)
            : $in;
    }

    /**
     * @return array
     */
    public static function getValidStatuses()
    {
        return [
            self::PENDING => Yii::t('app', 'Pending'),
            self::BACK_TO_PENDING => Yii::t('app', 'Back To Pending'),
            self::WAITING_DELIVERY => Yii::t('app', 'Waiting Delivery'),
            self::DELIVERY_IN_PROGRESS => Yii::t('app', 'Delivery In Progress'),
            self::SUCCESS_DELIVERY => Yii::t('app', 'Success Delivery'),
            self::REJECTED => Yii::t('app', 'Rejected'),
            self::CANCELED => Yii::t('app', 'Canceled'),
            self::NOT_PAID => Yii::t('app', 'Not Paid'),
            self::RETURNED => Yii::t('app', 'Returned'),
        ];
    }

    /**
     * @return array
     */
    public static function getNotValidStatuses()
    {
        return [
            self::NOT_VALID => Yii::t('app', 'Not Valid'),
            self::NOT_VALID_CHECKED => Yii::t('app', 'Not Valid Checked'),
        ];
    }
}