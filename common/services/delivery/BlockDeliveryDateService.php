<?php

namespace common\services\delivery;

use common\modules\user\models\tables\User;
use Yii;
use common\models\delivery\DeliveryDate;
use common\models\delivery\DeliveryDateOffers;
use common\services\ServiceException;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Class BlockDeliveryDateService
 * @package common\services\delivery
 */
class BlockDeliveryDateService
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function viewDeliveryDate($filters = [], $pagination = null, $sort_order = null, $sort_field = null): array
    {
        $query = DeliveryDate::find()
            ->select([
                'delivery_date.delivery_date_id',
                'delivery_date.delivery_dates',
            ])
            ->joinWith(['deliveryDateOffers' => function (ActiveQuery $query) use ($filters) {
                $query->select([
                    'delivery_date_id',
                    'offer.offer_id',
                    'offer.offer_name',
                    'geo.geo_id',
                    'geo.geo_name',
                    'geo.iso'
                ])
                    ->leftJoin('offer', 'offer.offer_id = delivery_date_offers.offer_id')
                    ->leftJoin('geo', 'geo.geo_id = delivery_date_offers.geo_id');

                if (isset($filters['offer_id'])) $query->andWhere(['delivery_date_offers.offer_id' => $filters['offer_id']]);
                if (isset($filters['geo_id'])) $query->andWhere(['delivery_date_offers.geo_id' => $filters['geo_id']]);
            }]);

        if (!is_null(Yii::$app->user->identity->getOwnerId())) $query->andWhere(['delivery_date.advert_id' => Yii::$app->user->identity->getOwnerId()]);

        if (isset($filters['delivery_dates'])) {
            $start = new \DateTime($filters['delivery_dates']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['delivery_dates']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'delivery_date.delivery_dates', $start_date]);
            $query->andWhere(['<', 'delivery_date.delivery_dates', $end_date]);
        }

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy([
                'delivery_date.delivery_dates' => SORT_DESC
            ]);
        }

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $count = clone $query;
        $count_all = $count->count();

        $delivery_dates = $query
            ->groupBy('delivery_date_id')
            ->asArray()
            ->all();

        return [
            'result' => $delivery_dates,
            'count' => [
                'count_all' => $count_all
            ],
        ];
    }

    /**
     * @param string $delivery_date_id
     * @return array|DeliveryDate[]|DeliveryDateOffers[]|\yii\db\ActiveRecord[]
     */
    public function view(string $delivery_date_id)
    {
        $query = DeliveryDateOffers::find()
            ->select([
                'offer.offer_id',
                'offer.offer_name'
            ])
            ->leftJoin('offer', 'offer.offer_id = delivery_date_offers.offer_id')
            ->leftJoin('geo', 'geo.geo_id = delivery_date_offers.geo_id')
            ->where(['delivery_date_id' => $delivery_date_id])
            ->asArray()
            ->all();

        return $query;
    }

    /**
     * @param array $data
     * @return bool
     * @throws ServiceException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function saveDeliveryDate(array $data)
    {
//        $date_formatted = $this->validateDeliveryDates($data['delivery_dates']);

        $owner_id = Yii::$app->user->identity->getOwnerId();

        $user = null;
        switch ($owner_id) {
            case !is_null($owner_id) && is_array($owner_id):
                $user = $owner_id[0];
                break;
            case !is_null($owner_id):
                $user = $owner_id;
                break;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (isset($data['offer_id']) && is_array($data['offer_id'])) {
                $this->checkIfDeliveryDateOfferExist($data);

                $delivery_dates = new DeliveryDate();

                $delivery_dates->setAttributes([
                    'advert_id' => $user,
                    'delivery_dates' => $data['delivery_dates']
                ]);
                $delivery_dates->save();
                foreach ($data['offer_id'] as $offer_id) {
                    $delivery_date_offers = $this->saveDeliveryDateOffers($delivery_dates, $offer_id, $data);
                    if ($delivery_date_offers != true || is_array($delivery_date_offers)) {
                        $this->errors['delivery_date_offers'] = $delivery_date_offers;
                    }
                }
            }
            $transaction->commit();
        } catch (InvalidParamException $e) {
            $this->errors['InvalidParamException'] = $e;
            $transaction->rollBack();
        } catch (Exception $e) {
            $this->errors['Exception'] = $e;
            $transaction->rollBack();
        }

        if (!empty($this->errors)) {
            throw new ServiceException('Error!');
        } else {
            return true;
        }
    }

    /**
     * @param DeliveryDate $delivery_date
     * @param string $offer_id
     * @param array $data
     * @return array|bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveDeliveryDateOffers(DeliveryDate $delivery_date, string $offer_id, array $data)
    {
        $delivery_dates_offer = new DeliveryDateOffers();

        $delivery_dates_offer->setAttributes([
            'delivery_date_id' => $delivery_date->delivery_date_id,
            'offer_id' => $offer_id,
            'geo_id' => $data['geo_id'],
        ]);

        return $delivery_dates_offer->validate() ? $delivery_dates_offer->save() : $delivery_dates_offer->errors;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getDeliveryDates(array $data)
    {
        $delivery_dates = DeliveryDate::find()
            ->select([
                'delivery_dates'
            ])
            ->join('LEFT JOIN', 'delivery_date_offers', 'delivery_date_offers.delivery_date_id = delivery_date.delivery_date_id')
            ->where(['offer_id' => $data['offer_id']])
            ->andWhere(['geo_id' => $data['geo_id']])
            ->asArray()
            ->all();

        $result = [];
        if (!empty($delivery_dates)) {
            foreach ($delivery_dates as $key => $value) {
                $result[] = $value['delivery_dates'];
//                $json_data = json_decode($value->delivery_dates, true);
//                $result = explode(', ', $value['delivery_dates']);
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @return false|int
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function delete(array $data)
    {
        $delivery_dates = DeliveryDate::findOne([
            'delivery_date_id' => $data['delivery_date_id'],
        ]);

        return $delivery_dates->delete();
    }

    /**
     * @return bool
     * @throws DeliveryException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deletePastDays()
    {
        $past_days = DeliveryDate::find()->where(['<', 'delivery_dates', new Expression('CURDATE()')])->all();

        if (!$past_days)
            throw new DeliveryException('No more records.');

        foreach ($past_days as $past_day) {
            $past_day->delete();
        }

        return true;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    private function checkIfDeliveryDateOfferExist(array $data)
    {
        $delivery_data = DeliveryDate::find()
            ->joinWith('deliveryDateOffers')
            ->where(['delivery_dates' => $data['delivery_dates']])
            ->andWhere(['offer_id' => $data['offer_id']])
            ->andWhere(['geo_id' => $data['geo_id']])
            ->all();

        foreach ($delivery_data as $delivery){
            $delivery->delete();
        }

        return true;
    }

    /**
     * @param string $dates
     * @return string
     */
    private function validateDeliveryDates(string $dates): string
    {
        $dates_array = explode(', ', $dates);
        $formatted = '';
        foreach ($dates_array as $date) {
            $date_format = new \DateTime($date);
            $formatted .= $date_format->format('Y-m-d') . ', ';
        }

        return rtrim($formatted, ', ');
    }
}