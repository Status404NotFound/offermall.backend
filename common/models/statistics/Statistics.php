<?php

namespace common\models\statistics;

use common\modules\user\models\Permission;
use yii\base\Behavior;
use yii\base\Model;

/**
 * Class Statistics
 * @package common\models\statistics
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
 * @property integer $up_sale_pcs
 * @property integer $cross_sale_pcs
 * @property integer $up_sale_pcs_total
 * @property integer $up_sale_pcs_total_approved
 * @property integer $delivery_in_progress
 * @property integer $not_paid
 * @property integer $not_valid
 * @property integer $not_valid_checked
 * @property integer $total_wds
 * @property integer $sum_pcs_wds
 * @property integer $approved
 * @property double $total_cost
 *
 * @property double $cr
 * @property double $sr
 * @property double $pr
 * @property double $nr
 * @property double $ar
 * @property double $upsr
 * @property double $ups_pcsr
 * @property double $c_ups_pcsr
 * @property double $crosss_pcsr
 * @property double $avg_approved_pcs
 * @property double $c_sr
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
    public $not_valid = 0;
    public $not_valid_checked = 0;
    public $not_paid = 0;
    public $approved = 0;
    public $total_wds = 0;
    public $sum_pcs_wds = 0;
    public $up_sale_pcs = 0;
    public $cross_sale_pcs = 0;
    public $up_sale_pcs_total = 0;
    public $up_sale_pcs_total_approved = 0;
    public $cr = 0;
    public $sr = 0;
    public $pr = 0;
    public $nr = 0;
    public $ar = 0;
    public $c_ar = 0;
    public $upsr = 0;
    public $avg_bill = 0;
    public $total_cost = 0;
    public $ups_pcsr = 0;
    public $c_ups_pcsr = 0;
    public $crosss_pcsr = 0;
    public $avg_approved_pcs = 0;
    public $c_sr = 0;
    public $currency_code = '';

    const VALID_REJECT = "(1, 3, 5, 6, 7, 9, 11, 14, 15, 16, 17, 24, 25)";
    const NOT_VALID_REJECT = "(0, 2, 4, 8, 10, 12, 13, 18, 19, 20, 21, 22, 23, 26, 27)";

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['views', 'uniques', 'total', 'pending', 'success_delivery', 'back_to_pending', 'canceled', 'rejected', 'returned', 'waiting_for_delivery',
              'delivery_in_progress', 'not_valid', 'not_valid_checked', 'not_paid', 'approved', 'total_wds', 'sum_pcs_wds', 'up_sale_pcs', 'cross_sale_pcs',
              'up_sale_pcs_total', 'up_sale_pcs_total_approved'], 'integer'],
            [['cr', 'sr', 'pr', 'nr', 'ar', 'c_ar', 'upsr', 'ups_pcsr', 'crosss_pcsr', 'c_ups_pcsr' , 'c_sr', 'avg_bill', 'total_cost', 'avg_approved_pcs'], 'number'],
            [['currency_code'], 'string'],
        ];
    }

    /**
     * Calculating attributes
     */
    public function setCalculatedAttributes(): void
    {
        $this->setAttributes([
            'cr'               => ($this->views == 0) ? 0 : round($this->total / $this->views * 100, 2),
            'sr'               => ($this->success_delivery == 0) ? 0 : round($this->success_delivery / $this->total * 100, 2),
            'nr'               => ($this->total == 0) ? 0 : round(($this->not_valid + $this->not_valid_checked) / $this->total * 100, 2),
            'pr'               => ($this->total == 0) ? 0 : round((1 - ($this->pending) / $this->total) * 100, 2),
            'ar'               => ($this->approved == 0) ? 0 : round($this->approved / $this->total * 100, 2),
            'c_ar'             => ($this->approved == 0) ? 0 : round($this->approved / ($this->total - ($this->not_valid + $this->not_valid_checked)) * 100, 2),
            'ups_pcsr'         => ($this->up_sale_pcs_total == 0 || $this->total == 1) ? 0 : round(($this->up_sale_pcs_total / ($this->total - ($this->not_valid + $this->not_valid_checked))) * 100, 2),
            'c_ups_pcsr'       => ($this->approved == 0) ? 0 : round(($this->up_sale_pcs_total_approved / $this->approved) * 100, 2),
            'avg_approved_pcs' => ($this->sum_pcs_wds == 0) ? 0 : round($this->sum_pcs_wds / $this->total_wds, 3),
            'upsr'             => ($this->up_sale_pcs == 0 || $this->total_wds == 0) ? 0 : round($this->up_sale_pcs / $this->total_wds, 3),
            'crosss_pcsr'      => ($this->cross_sale_pcs == 0 || $this->total_wds == 0) ? 0 : round($this->cross_sale_pcs / $this->total_wds, 3),
            'c_sr'             => ($this->success_delivery == 0) ? 0 : round($this->success_delivery / ($this->total - $this->not_valid - $this->not_valid_checked) * 100, 2),
            'avg_bill'         => ($this->success_delivery == 0) ? 0 : round($this->total_cost / $this->success_delivery, 1),
        ]);
    }

    public function getAllStatisticsTotalRow($rows): array
    {
        if (empty($rows)) {
            
            return [];
        }
    
        $total = [];
        $attributes = ['offer_id', 'offer_name', 'currency_code', 'advert_id', 'advert_name', 'geo_id', 'geo_name', 'iso', 'wm_id', 'wm_name', 'date', 'index_date'];
        foreach ($rows as $offer) {
            
            foreach ($attributes as $attr) {
                if (isset($offer[$attr])) {
                    unset($offer[$attr]);
                }
            }

            foreach ($offer as $key => $row) {
                isset($total[$key])
                    ? $total[$key] += $row
                    : $total[$key] = $row;
            }
        }
        
        $calculating = new self();
        $calculating->setAttributes($total);
        $calculating->setCalculatedAttributes();

        return $calculating->getAttributes();
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
     * @return Behavior[]
     */
    public function getBehaviors(): array
    {
        return $this->behaviors;
    }

    /**
     * @param Behavior[] $behaviors
     */
    public function setBehaviors(array $behaviors): void
    {
        $this->behaviors = $behaviors;
    }

    /**
     * @return float
     */
    public function getUpsPcsr(): float
    {
        return $this->ups_pcsr;
    }

    /**
     * @param float $ups_pcsr
     */
    public function setUpsPcsr(float $ups_pcsr): void
    {
        $this->ups_pcsr = $ups_pcsr;
    }

    /**
     * @return float
     */
    public function getCrosssPcsr(): float
    {
        return $this->crosss_pcsr;
    }

    /**
     * @param float $crosss_pcsr
     */
    public function setCrosssPcsr(float $crosss_pcsr): void
    {
        $this->crosss_pcsr = $crosss_pcsr;
    }

    /**
     * @return float
     */
    public function getCSr(): float
    {
        return $this->c_sr;
    }

    /**
     * @param float $c_sr
     */
    public function setCSr(float $c_sr): void
    {
        $this->c_sr = $c_sr;
    }
    
    /**
     * @return float
     */
    public function getCUpsPcsr(): float
    {
        return $this->c_ups_pcsr;
    }
    
    /**
     * @param float $c_ups_pcsr
     */
    public function setCUpsPcsr(float $c_ups_pcsr): void
    {
        $this->c_ups_pcsr = $c_ups_pcsr;
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
     * @return float
     */
    public function getUpsr(): float
    {
        return $this->upsr;
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