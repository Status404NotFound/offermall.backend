<?php

namespace crm\models\finstrip;

use common\services\timezone\TimeZoneSrv;
use yii\base\Model;
use yii\db\ActiveQuery;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\order\OrderStatus;
use yii\helpers\ArrayHelper;

/**
 * Class Finstrip
 * @package crm\models\finstrip
 *
 * @property integer $views
 * @property integer $unique_views
 * @property integer $total_orders_amount
 * @property integer $sd_orders_amount
 * @property integer $dip_orders_amount
 * @property integer $nv_orders_amount
 * @property integer $achived_orders_amount
 * @property integer $total_sku_amount
 * @property integer $dip_sku_amount
 * @property integer $sd_sku_amount
 * @property integer $achived_sku_amount
 *
 * @property double $total_advert_commission
 * @property double $total_traffic_cost
 *
 * @property double $cr_t
 * @property double $cr_s
 * @property double $cpl
 * @property double $cpt
 * @property double $cp_cps
 * @property double $rate
 * @property double $not_valid_rate
 * @property double $profit
 * @property double $roi
 *
 */
class Finstrip extends Model
{
    public $views = 0;
    public $unique_views = 0;
    public $total_orders_amount = 0;
    public $sd_orders_amount = 0;
    public $dip_orders_amount = 0;
    public $nv_orders_amount = 0;
    public $achived_orders_amount = 0;
    public $total_sku_amount = 0;
    public $dip_sku_amount = 0;
    public $sd_sku_amount = 0;
    public $achived_sku_amount = 0;
    public $total_advert_commission = 0.0;
    public $total_traffic_cost = 0.0;
    public $cr_t = 0.0;
    public $cr_s = 0.0;
    public $cpl = 0.0;
    public $cpt = 0.0;
    public $cp_cps = 0.0;
    public $rate = 0.0;
    public $not_valid_rate = 0.0;
    public $profit = 0.0;
    public $roi = 0.0;

    protected $tz = null;

    public $approved_statuses = "(" . OrderStatus::CANCELED . ",
        " . OrderStatus::NOT_PAID . ", 
        " . OrderStatus::WAITING_DELIVERY . ", 
        " . OrderStatus::DELIVERY_IN_PROGRESS . ", 
        " . OrderStatus::SUCCESS_DELIVERY . ", 
        " . OrderStatus::RETURNED . ")";

    /**
     * Finstrip constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->tz = new TimeZoneSrv();
        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['views', 'unique_views', 'total_orders_amount', 'sd_orders_amount', 'dip_orders_amount', 'nv_orders_amount', 'achived_orders_amount',
                'total_sku_amount', 'dip_sku_amount', 'sd_sku_amount', 'achived_sku_amount',
                'offer_id'], 'integer'],
            [['total_advert_commission', 'total_traffic_cost', 'cr_t', 'cr_s', 'cpl', 'cpt', 'cp_cps', 'rate', 'not_valid_rate', 'profit', 'roi'], 'number'],
            [['day_month', 'offer_name'], 'string'],
        ];
    }

    /**
     * Calculating data
     */
    public function setCalculatedAttributes()
    {
        $this->setAttributes([
            'cr_t' => ($this->total_orders_amount == 0) ? 0
                : round($this->views / $this->total_orders_amount, 2),
            'cr_s' => ($this->sd_orders_amount == 0) ? 0
                : round($this->views / $this->sd_orders_amount, 2),
            'total_advert_commission' => round($this->total_advert_commission, 2),
            'total_traffic_cost' => isset($this->total_traffic_cost)
                ? round($this->total_traffic_cost, 2) : 0,
            'cpl' => ($this->total_orders_amount == 0) ? 0
                : round($this->total_traffic_cost / $this->total_orders_amount, 2),
            'cpt' => ($this->achived_orders_amount == 0) ? 0
                : round($this->total_traffic_cost / $this->achived_orders_amount, 2),
            'cp_cps' => ($this->sd_sku_amount == 0) ? 0
                : round($this->total_traffic_cost / $this->sd_sku_amount, 2),
            'rate' => ($this->sd_orders_amount == 0) ? 0
                : round($this->total_orders_amount / $this->sd_orders_amount, 2),
            'not_valid_rate' => ($this->total_orders_amount == 0) ? 0
                : round($this->nv_orders_amount / $this->total_orders_amount * 100, 2),
            'profit' => round($this->total_advert_commission - $this->total_traffic_cost, 2),
            'roi' => ($this->total_traffic_cost == 0) ? 0
                : round(100 * ($this->total_advert_commission - $this->total_traffic_cost) / $this->total_traffic_cost, 2)
//                : round($this->profit / $this->total_traffic_cost * 100, 2)
        ]);
//        return $this->attributes;
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
    public function getUniqueViews(): int
    {
        return $this->unique_views;
    }

    /**
     * @param int $unique_views
     */
    public function setUniqueViews(int $unique_views): void
    {
        $this->unique_views = $unique_views;
    }

    /**
     * @return int
     */
    public function getTotalOrdersAmount(): int
    {
        return $this->total_orders_amount;
    }

    /**
     * @param int $total_orders_amount
     */
    public function setTotalOrdersAmount(int $total_orders_amount): void
    {
        $this->total_orders_amount = $total_orders_amount;
    }

    /**
     * @return int
     */
    public function getSdOrdersAmount(): int
    {
        return $this->sd_orders_amount;
    }

    /**
     * @param int $sd_orders_amount
     */
    public function setSdOrdersAmount(int $sd_orders_amount): void
    {
        $this->sd_orders_amount = $sd_orders_amount;
    }

    /**
     * @return int
     */
    public function getDipOrdersAmount(): int
    {
        return $this->dip_orders_amount;
    }

    /**
     * @param int $dip_orders_amount
     */
    public function setDipOrdersAmount(int $dip_orders_amount): void
    {
        $this->dip_orders_amount = $dip_orders_amount;
    }

    /**
     * @return int
     */
    public function getNvOrdersAmount(): int
    {
        return $this->nv_orders_amount;
    }

    /**
     * @param int $nv_orders_amount
     */
    public function setNvOrdersAmount(int $nv_orders_amount): void
    {
        $this->nv_orders_amount = $nv_orders_amount;
    }

    /**
     * @return int
     */
    public function getAchivedOrdersAmount(): int
    {
        return $this->achived_orders_amount;
    }

    /**
     * @param int $achived_orders_amount
     */
    public function setAchivedOrdersAmount(int $achived_orders_amount): void
    {
        $this->achived_orders_amount = $achived_orders_amount;
    }

    /**
     * @return int
     */
    public function getTotalSkuAmount(): int
    {
        return $this->total_sku_amount;
    }

    /**
     * @param int $total_sku_amount
     */
    public function setTotalSkuAmount(int $total_sku_amount): void
    {
        $this->total_sku_amount = $total_sku_amount;
    }

    /**
     * @return int
     */
    public function getDipSkuAmount(): int
    {
        return $this->dip_sku_amount;
    }

    /**
     * @param int $dip_sku_amount
     */
    public function setDipSkuAmount(int $dip_sku_amount): void
    {
        $this->dip_sku_amount = $dip_sku_amount;
    }

    /**
     * @return int
     */
    public function getSdSkuAmount(): int
    {
        return $this->sd_sku_amount;
    }

    /**
     * @param int $sd_sku_amount
     */
    public function setSdSkuAmount(int $sd_sku_amount): void
    {
        $this->sd_sku_amount = $sd_sku_amount;
    }

    /**
     * @return int
     */
    public function getAchivedSkuAmount(): int
    {
        return $this->achived_sku_amount;
    }

    /**
     * @param int $achived_sku_amount
     */
    public function setAchivedSkuAmount(int $achived_sku_amount): void
    {
        $this->achived_sku_amount = $achived_sku_amount;
    }

    /**
     * @return float
     */
    public function getTotalAdvertCommission(): float
    {
        return $this->total_advert_commission;
    }

    /**
     * @param float $total_advert_commission
     */
    public function setTotalAdvertCommission(float $total_advert_commission): void
    {
        $this->total_advert_commission = $total_advert_commission;
    }

    /**
     * @return float
     */
    public function getTotalTrafficCost(): float
    {
        return $this->total_traffic_cost;
    }

    /**
     * @param float $total_traffic_cost
     */
    public function setTotalTrafficCost(float $total_traffic_cost): void
    {
        $this->total_traffic_cost = $total_traffic_cost;
    }

    /**
     * @return float
     */
    public function getCrT(): float
    {
        return $this->cr_t;
    }

    /**
     * @param float $cr_t
     */
    public function setCrT(float $cr_t): void
    {
        $this->cr_t = $cr_t;
    }

    /**
     * @return float
     */
    public function getCrS(): float
    {
        return $this->cr_s;
    }

    /**
     * @param float $cr_s
     */
    public function setCrS(float $cr_s): void
    {
        $this->cr_s = $cr_s;
    }

    /**
     * @return float
     */
    public function getCpl(): float
    {
        return $this->cpl;
    }

    /**
     * @param float $cpl
     */
    public function setCpl(float $cpl): void
    {
        $this->cpl = $cpl;
    }

    /**
     * @return float
     */
    public function getCpt(): float
    {
        return $this->cpt;
    }

    /**
     * @param float $cpt
     */
    public function setCpt(float $cpt): void
    {
        $this->cpt = $cpt;
    }

    /**
     * @return float
     */
    public function getCpCps(): float
    {
        return $this->cp_cps;
    }

    /**
     * @param float $cp_cps
     */
    public function setCpCps(float $cp_cps): void
    {
        $this->cp_cps = $cp_cps;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * @param float $rate
     */
    public function setRate(float $rate): void
    {
        $this->rate = $rate;
    }

    /**
     * @return float
     */
    public function getNotValidRate(): float
    {
        return $this->not_valid_rate;
    }

    /**
     * @param float $not_valid_rate
     */
    public function setNotValidRate(float $not_valid_rate): void
    {
        $this->not_valid_rate = $not_valid_rate;
    }

    /**
     * @return float
     */
    public function getProfit(): float
    {
        return $this->profit;
    }

    /**
     * @param float $profit
     */
    public function setProfit(float $profit): void
    {
        $this->profit = $profit;
    }

    /**
     * @return float
     */
    public function getRoi(): float
    {
        return $this->roi;
    }

    /**
     * @param float $roi
     */
    public function setRoi(float $roi): void
    {
        $this->roi = $roi;
    }

    /**
     * @param ActiveQuery $query
     * @param $filters
     */
    protected function filterViews(ActiveQuery $query, $filters)
    {
        if (isset($filters['advert_id'])) {
            $query->join('LEFT JOIN', '`advert_offer_target`', 'advert_offer_target.offer_id = landing_views.offer_id');
            $query->join('LEFT JOIN', '`target_advert_group`', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id');
            $query->join('LEFT JOIN', '`target_advert`', 'target_advert.target_advert_id = target_advert_group.target_advert_group_id');
            $offers = $this->getAdvertOffers($filters['advert_id']['value']);
            $geo = $this->getAdvertGeo($filters['advert_id']['value']);
            $query->andWhere(['landing_views.offer_id' => $offers]);
            $query->andWhere(['landing_views.geo_id' => $geo]);
            $query->andWhere(['target_advert.advert_id' => $filters['advert_id']['value']]);
        }
        if (isset($filters['advert_offer_target_status'])) {
            $query->join('LEFT JOIN', 'advert_offer_target', ' advert_offer_target.offer_id = landing_views.offer_id');
            $query->andWhere(['advert_offer_target.advert_offer_target_status' => $filters['advert_offer_target_status']['value']]);
        }
        if (isset($filters['wm_id'])) {
            $query->join('LEFT JOIN', 'flow', ' flow.flow_id = landing_views.flow_id');
            $query->andWhere(['flow.wm_id' => $filters['wm_id']['value']]);
        }
    }

    /**
     * @param $advert_id
     * @return array
     */
    public function getAdvertOffers($advert_id)
    {
        $offers = TargetAdvert::find()
            ->select('advert_offer_target.offer_id')
            ->join('LEFT JOIN', 'target_advert_group', ' target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->where(['target_advert.advert_id' => $advert_id])
            ->groupBy('advert_offer_target.offer_id')
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($offers, 'offer_id');
    }

    /**
     * @param $advert_id
     * @return array
     */
    protected function getAdvertGeo($advert_id)
    {
        $query = TargetAdvert::find()
            ->select('advert_offer_target.geo_id')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert_group.target_advert_group_id = target_advert.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->where(['target_advert.advert_id' => $advert_id])
            ->asArray()
            ->all();

        return ArrayHelper::getColumn($query, 'geo_id');
    }
}