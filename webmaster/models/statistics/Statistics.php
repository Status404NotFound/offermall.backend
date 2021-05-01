<?php

namespace webmaster\models\statistics;

use common\models\order\OrderStatus;
use yii\base\Model;

/**
 * Class Statistics
 * @package webmaster\models\statistics
 *
 * @property integer $views
 * @property integer $uniques
 * @property integer $total
 * @property integer $pending
 * @property integer $success_delivery
 * @property integer $waiting_for_delivery
 * @property integer $back_to_pending
 * @property integer $rejected
 * @property integer $returned
 * @property integer $canceled
 * @property integer $delivery_in_progress
 * @property integer $not_paid
 * @property integer $not_valid
 * @property integer $not_valid_checked
 * @property integer $approved
 * @property integer $up_sale_rate
 *
 * @property double $cr
 * @property double $cs
 * @property double $up_sale_pcs
 * @property double $sr
 * @property double $pr
 * @property double $nr
 * @property double $ar
 *
 */
class Statistics extends Model
{
    public $views = 0;
    public $uniques = 0;
    public $total = 0;
    public $pending = 0;
    public $success_delivery = 0;
    public $back_to_pending = 0;
    public $canceled = 0;
    public $rejected = 0;
    public $returned = 0;
    public $waiting_for_delivery = 0;
    public $delivery_in_progress = 0;
    public $up_sale_rate = 0;
    public $not_valid = 0;
    public $not_valid_checked = 0;
    public $not_paid = 0;
    public $approved = 0;

    public $cr = 0;
    public $cs = 0;
    public $up_sale_pcs = 0;
    public $sr = 0;
    public $pr = 0;
    public $nr = 0;
    public $ar = 0;

    const pending = "(" . OrderStatus::PENDING . ", " . OrderStatus::BACK_TO_PENDING . " )";
    const approved = "(" . OrderStatus::WAITING_DELIVERY . ", " . OrderStatus::DELIVERY_IN_PROGRESS . ", " . OrderStatus::NOT_PAID . ", " . OrderStatus::CANCELED . ")";
    const rejected = "(" . OrderStatus::REJECTED . ")";
    const not_valid = "(" . OrderStatus::NOT_VALID . ", " . OrderStatus::NOT_VALID_CHECKED . " )";
    const approved_statuses = "(" . OrderStatus::WAITING_DELIVERY . ", " . OrderStatus::DELIVERY_IN_PROGRESS . ", " .
    OrderStatus::SUCCESS_DELIVERY . ", " . OrderStatus::CANCELED . ", " . OrderStatus::RETURNED . ", " . OrderStatus::NOT_PAID . ")";
    
    public const PENDING = [OrderStatus::PENDING, OrderStatus::BACK_TO_PENDING];
    public const APPROVED = [OrderStatus::SUCCESS_DELIVERY, OrderStatus::WAITING_DELIVERY, OrderStatus::DELIVERY_IN_PROGRESS, OrderStatus::NOT_PAID, OrderStatus::CANCELED];
    public const REJECTED = [OrderStatus::REJECTED];
    public const NOT_VALID = [OrderStatus::NOT_VALID, OrderStatus::NOT_VALID_CHECKED];
    public const SUCCESS_DELIVERY = [OrderStatus::SUCCESS_DELIVERY];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['views', 'uniques', 'total', 'pending', 'success_delivery', 'up_sale_rate', 'back_to_pending', 'canceled', 'rejected', 'returned', 'waiting_for_delivery', 'delivery_in_progress', 'not_valid', 'not_valid_checked', 'not_paid', 'approved'], 'integer'],
            [['cr', 'cs', 'up_sale_pcs', 'sr', 'pr', 'nr', 'ar'], 'number'],
        ];
    }

    /**
     * Calculating attributes
     */
    public function setCalculatedAttributes()
    {
        $this->setAttributes([
            'cr'          => ($this->views == 0)            ? 0 : round($this->total / $this->views * 100, 2),
            'cs'          => ($this->views == 0)            ? 0 : round($this->success_delivery / $this->views * 100, 2),
            'up_sale_pcs' => ($this->up_sale_rate == 0)     ? 0 : round($this->up_sale_rate / $this->approved * 100, 2),
            'sr'          => ($this->success_delivery == 0) ? 0 : round($this->success_delivery / $this->total * 100, 2),
            'nr'          => ($this->total == 0)            ? 0 : round(($this->not_valid + $this->not_valid_checked) / $this->total * 100, 2),
            'pr'          => ($this->total == 0)            ? 0 : round((1 - ($this->pending) / $this->total) * 100, 2),
            'ar'          => ($this->total == 0)            ? 0 : round($this->approved / $this->total * 100, 2),
        ]);
    }

    public static function pendingStatuses(): string {
        return OrderStatus::sqlFormatFindByOrderStatuses(self::PENDING);
    }
    
    public static function approvedStatuses(): string {
        return OrderStatus::sqlFormatFindByOrderStatuses(self::APPROVED);
    }
    
    public static function rejectedStatuses(): string {
        return OrderStatus::sqlFormatFindByOrderStatuses(self::REJECTED);
    }
    
    public static function notValidStatuses(): string {
        return OrderStatus::sqlFormatFindByOrderStatuses(self::NOT_VALID);
    }
    
    public static function successStatuses(): string {
        return OrderStatus::sqlFormatFindByOrderStatuses(self::SUCCESS_DELIVERY);
    }
    
    /**
     * @return int
     */
    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    /**
     * @return int
     */
    public function getUniques(): int
    {
        return $this->uniques;
    }

    /**
     * @param int $uniques
     */
    public function setUniques(int $uniques): void
    {
        $this->uniques = $uniques;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    /**
     * @return int
     */
    public function getPending(): int
    {
        return $this->pending;
    }

    /**
     * @param int $pending
     */
    public function setPending(int $pending): void
    {
        $this->pending = $pending;
    }

    /**
     * @return int
     */
    public function getSuccessDelivery(): int
    {
        return $this->success_delivery;
    }

    /**
     * @param int $success_delivery
     */
    public function setSuccessDelivery(int $success_delivery): void
    {
        $this->success_delivery = $success_delivery;
    }

    /**
     * @return int
     */
    public function getDeliveryInProgress(): int
    {
        return $this->delivery_in_progress;
    }

    /**
     * @param int $delivery_in_progress
     */
    public function setDeliveryInProgress(int $delivery_in_progress): void
    {
        $this->delivery_in_progress = $delivery_in_progress;
    }

    /**
     * @return int
     */
    public function getNotValid(): int
    {
        return $this->not_valid;
    }

    /**
     * @param int $not_valid
     */
    public function setNotValid(int $not_valid): void
    {
        $this->not_valid = $not_valid;
    }

    /**
     * @return int
     */
    public function getNotPaid(): int
    {
        return $this->not_paid;
    }

    /**
     * @param int $not_paid
     */
    public function setNotPaid(int $not_paid): void
    {
        $this->not_paid = $not_paid;
    }

    /**
     * @return float
     */
    public function getCr(): float
    {
        return $this->cr;
    }

    /**
     * @param float $cr
     */
    public function setCr(float $cr): void
    {
        $this->cr = $cr;
    }

    /**
     * @return float
     */
    public function getCs(): float
    {
        return $this->cs;
    }

    /**
     * @param float $cs
     */
    public function setCs(float $cs): void
    {
        $this->cs = $cs;
    }

    /**
     * @return float
     */
    public function getUpSalePcs(): float
    {
        return $this->up_sale_pcs;
    }

    /**
     * @param float $up_sale_pcs
     */
    public function setUpSalePcs(float $up_sale_pcs): void
    {
        $this->up_sale_pcs = $up_sale_pcs;
    }

    /**
     * @return float
     */
    public function getSr(): float
    {
        return $this->sr;
    }

    /**
     * @param float $sr
     */
    public function setSr(float $sr): void
    {
        $this->sr = $sr;
    }

    /**
     * @return float
     */
    public function getPr(): float
    {
        return $this->pr;
    }

    /**
     * @param float $pr
     */
    public function setPr(float $pr): void
    {
        $this->pr = $pr;
    }

    /**
     * @return float
     */
    public function getNr(): float
    {
        return $this->nr;
    }

    /**
     * @param float $nr
     */
    public function setNr(float $nr): void
    {
        $this->nr = $nr;
    }

    /**
     * @return float
     */
    public function getAr(): float
    {
        return $this->ar;
    }

    /**
     * @param float $ar
     */
    public function setAr(float $ar): void
    {
        $this->ar = $ar;
    }

    /**
     * @return int
     */
    public function getWaitingForDelivery(): int
    {
        return $this->waiting_for_delivery;
    }

    /**
     * @param int $waiting_for_delivery
     */
    public function setWaitingForDelivery(int $waiting_for_delivery): void
    {
        $this->waiting_for_delivery = $waiting_for_delivery;
    }

    /**
     * @return int
     */
    public function getApproved(): int
    {
        return $this->approved;
    }

    /**
     * @param int $approved
     */
    public function setApproved(int $approved): void
    {
        $this->approved = $approved;
    }

    /**
     * @return int
     */
    public function getNotValidChecked(): int
    {
        return $this->not_valid_checked;
    }

    /**
     * @param int $not_valid_checked
     */
    public function setNotValidChecked(int $not_valid_checked): void
    {
        $this->not_valid_checked = $not_valid_checked;
    }

    /**
     * @return int
     */
    public function getBackToPending(): int
    {
        return $this->back_to_pending;
    }

    /**
     * @param int $back_to_pending
     */
    public function setBackToPending(int $back_to_pending): void
    {
        $this->back_to_pending = $back_to_pending;
    }

    /**
     * @return int
     */
    public function getRejected(): int
    {
        return $this->rejected;
    }

    /**
     * @param int $rejected
     */
    public function setRejected(int $rejected): void
    {
        $this->rejected = $rejected;
    }

    /**
     * @return int
     */
    public function getReturned(): int
    {
        return $this->returned;
    }

    /**
     * @param int $returned
     */
    public function setReturned(int $returned): void
    {
        $this->returned = $returned;
    }

    /**
     * @return int
     */
    public function getCanceled(): int
    {
        return $this->canceled;
    }

    /**
     * @param int $canceled
     */
    public function setCanceled(int $canceled): void
    {
        $this->canceled = $canceled;
    }
}