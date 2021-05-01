<?php

namespace common\services\stock;

use common\modules\user\models\tables\User;
use Yii;
use common\models\stock\Stock;
use common\models\stock\StockSku;
use common\models\stock\StockTraffic;
use yii\base\Exception;


/**
 * Class StockService
 * @package crm\services\stock
 */
class StockService extends StockCommonService
{
    private $errors = [];

    public function getErrors()
    {
        return $this->errors;
    }

    public function findActive($pagination = null)
    {
        $stocks = Stock::find()
            ->select(['stock_id', 'stock_name'])
            ->active()
            ->withSku();

        if (Yii::$app->user->identity->role !== User::ROLE_ADMIN) {
            $stocks->andWhere(['owner_id' => Yii::$app->user->identity->getId()]);
        }

        if (!isset($pagination)) {
            return $stocks->all();
        }

        $count = clone $stocks;
        $count = $count->count();
        return [
            'stocks' => $stocks->offset($pagination['first_row'])
                ->limit($pagination['rows'])
                ->asArray()
                ->all(),
            'count' => $count
        ];
    }

    public function getStockById($stock_id, $stock_sku = false)
    {
        $stock = Stock::find()->where(['stock_id' => $stock_id]);
        if ($stock_sku == true) {
            $stock->withSku();
        }
        return $stock->asArray()->one();
    }

    public function saveStatus($stock_id, $status)
    {
        $stock = Stock::findOne(['stock_id' => $stock_id]);
        $stock->setAttribute('status', $status);
        return $stock->validate() ? $stock->save() : $stock->errors;
    }

    public function addSku($stock_id, $stock_id_from = null, $stock_sku = [])
    {
        // TODO: Add loging
        if (!$this->isStockExists($stock_id)) {
            $this->errors['stock'] = 'Stock not found';
            return false;
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            foreach ($stock_sku as $sku) {
                $sku_id = $sku['sku_id'];
                $amount = abs($sku['amount']);
                $is_new = !(isset($sku['is_new']) && $sku['is_new'] == 0);

                if (!$this->saveStockSku($stock_id, $sku_id, $amount)) {
                    throw new StockServiceException('Failed to add stock SKU');
                }
                if (!$this->saveStockTraffic($stock_id, $sku_id, $amount, $stock_id_from, $is_new)) {
                    throw new StockServiceException('Failed to add stock traffic');
                }
            }

            $tx->commit();
            return true;
        } catch (Exception $e) {
            $this->errors['exception'] = $e;
            $tx->rollBack();
        }
        return false;
    }

    public function moveSku($stock_id_from, $stock_id_to, $sku_id, $amount)
    {
        if (!$sku_from = $this->findStockSku($stock_id_from, $sku_id)) {
            $this->errors['sku_from'] = 'Stock SKU not found!';
            return false;
        }
        if ($sku_from->amount - $amount < 0) {
            $this->errors['sku_from'] = 'Sku amount in stock is too less!';
            return false;
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            $sku_from->amount -= $amount;
            $result = $sku_from->amount === 0
                ? $sku_from->delete()
                : $sku_from->save();

            if (!$result) {
                $this->errors['sku_from'] = $sku_from->errors;
                throw new StockServiceException('Failed to update stock SKU amount');
            }

            if ($stock_id_to !== null) {
                $result = $this->addSku($stock_id_to, $stock_id_from,
                    ['stock_sku' => ['sku_id' => $sku_id, 'amount' => $amount, 'is_new' => 0]]);
                if (!$result) {
                    throw new StockServiceException('Failed to add stock SKU');
                }
            }

            $tx->commit();
            return true;
        } catch (Exception $e) {
            $this->errors['exception'] = $e;
            $tx->rollBack();
        }
        return false;
    }


    private function saveStockSku($stock_id, $sku_id, $amount)
    {
        $sku = $this->findStockSku($stock_id, $sku_id) ?? new StockSku();
        if (!$sku) {
            $this->errors['stock_sku'] = 'Stock SKU not found';
            return false;
        }

        $sku->setAttributes([
            'stock_id' => $stock_id,
            'sku_id' => $sku_id,
            'amount' => $sku->amount + $amount,
        ]);
        if (!$sku->validate() || !$sku->save()) {
            $this->errors['stock_sku'] = $sku->errors;
            return false;
        }
        return true;
    }

    private function saveStockTraffic($stock_id, $sku_id, $amount, $stock_id_from = null, $is_new = true)
    {
        $traffic = new StockTraffic();
        $traffic->setAttributes([
            'stock_id_to' => $stock_id,
            'sku_id' => $sku_id,
            'amount' => $amount,
            'is_new' => $is_new ? 1 : 0,
            'stock_id_from' => $is_new ? null : $stock_id_from
        ]);
        if (!$traffic->validate() || !$traffic->save()) {
            $this->errors['stock_traffic'] = $traffic->errors;
            return false;
        }
        return true;
    }

    private function findStockSku($stock_id, $sku_id)
    {
        return StockSku::findOne([
            'sku_id' => $sku_id,
            'stock_id' => $stock_id
        ]);
    }

    private function isStockExists($stock_id)
    {
        return Stock::find()
            ->where(['stock_id' => $stock_id])
            ->count();
    }
}