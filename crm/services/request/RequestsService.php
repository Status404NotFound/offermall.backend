<?php
namespace crm\services\request;

use Yii;
use common\models\webmaster\WmOffer;
use common\services\ValidateException;
use common\services\order\logic\status\ChangeStatusException;
use yii\base\Exception;

class RequestsService
{
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param $wm_offer_id
     * @return array
     */
    public function getRequestView($wm_offer_id)
    {
        $query = WmOffer::find()
            ->select([
                'O.offer_name', 'WO.leads',
                'WC.websites', 'WC.doorway',
                'WC.contextual_advertising', 'WC.for_the_brand',
                'WC.teaser_advertising', 'WC.banner_advertising',
                'WC.social_networks_targeting_ads', 'WC.games_applications',
                'WC.email_marketing', 'WC.cash_back',
                'WC.click_under', 'WC.motivated',
                'WC.adult', 'WC.toolbar_traffic',
                'WC.sms_sending', 'WC.spam'
            ])
            ->from('wm_offer WO')
            ->innerJoin('offer O', 'WO.offer_id = O.offer_id')
            ->innerJoin('wm_checkboxes WC', 'WO.wm_offer_id = WC.wm_offer_id')
            ->where(['WO.wm_offer_id' => $wm_offer_id])
            ->asArray()
            ->one();

        return [
            'info' => $query,
            'offer_name' => $query['offer_name'],
            'leads' => $query['leads'],
        ];
    }

    /**
     * @param null $pagination
     * @param null $sort_order
     * @return array|\yii\db\ActiveRecord[]
     */
    public function requestList($pagination = null, $sort_order = null)
    {
        $query = WmOffer::find()
            ->select([
                'WO.wm_offer_id as request_id',
                'WO.offer_id',
                'O.offer_name',
                'U.username as wm_name',
                'WO.created_at',
            ])
            ->from('wm_offer WO')
            ->innerJoin('offer O','WO.offer_id = O.offer_id')
            ->innerJoin('user U','WO.wm_id = U.id')
            ->where(['WO.status' => WmOffer::STATUS_WAITING]);

        if (!isset($pagination)) {
            return $query->asArray()->all();
        }

        $count = clone $query;
        $total = $count->count();

        $sort_order = ($sort_order == -1) ? SORT_DESC : SORT_ASC;

        if (isset($sort_field)) $query->orderBy([$sort_field => $sort_order]);

        return [
            'result' => $query->offset($pagination['first_row'])
                ->limit($pagination['rows'])
                ->asArray()
                ->all(),
            'total' => $total
        ];
    }

    /**
     * @param $id
     * @param $status
     * @throws Exception
     * @throws ValidateException
     */
    public function changeStatus($id, $status)
    {
        $wm_offer = WmOffer::findOne(['wm_offer_id' => $id]);

        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!WmOffer::statuses($status))
                throw new ChangeStatusException('Offer Status exists');
                $wm_offer->setAttribute('status', $status);
            if ($wm_offer->save() !== true)
                throw new ChangeStatusException('Failed to set Offer #' . $wm_offer->wm_offer_id . ' status ' . WmOffer::statuses($status));
            $tx->commit();
        } catch (ValidateException $e) {
            $tx->rollBack();
            throw $e;
        } catch (Exception $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}