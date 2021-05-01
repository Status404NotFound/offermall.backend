<?php

namespace webmaster\services\offer;

use Yii;
use common\models\offer\Offer;
use common\services\offer\OfferNotFoundException;
use common\models\webmaster\WmCheckboxes;
use common\models\webmaster\WmOffer;
use yii\base\InvalidParamException;
use yii\db\Exception;

/**
 * Class OfferDataSave
 * @package webmaster\services\offer
 */
class OfferDataSave
{
    public $offer_id;
    public $data;
    public $errors = [];

    /**
     * OfferDataSave constructor.
     * @param $offer_id
     * @param array $data
     */
    public function __construct($offer_id, $data = [])
    {
        $this->offer_id = $offer_id;
        $this->data = $data;
    }

    /**
     * @return array|bool
     * @throws Exception
     * @throws OfferNotFoundException
     */
    public function execute()
    {
        if (!$this->isOfferExists()) {
            throw new OfferNotFoundException('Offer not found');
        }
        return $this->takeOffer();
    }

    /**
     * @return array|bool
     * @throws Exception
     */
    public function takeOffer()
    {
        $wm_offer = new WmOffer();

        $wm_offer->setAttributes([
            'wm_id' => Yii::$app->user->identity->getId(),
            'offer_id' => (int)$this->offer_id,
            'leads' => $this->data['leads'],
            'status' => WmOffer::STATUS_WAITING,
        ]);

        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $wm_offer->validate() ? $wm_offer->save() : $this->errors['take_offer'] = $wm_offer->errors;
            if (isset($this->offer_id)) {
                $data = $this->saveCheckboxes($wm_offer->wm_offer_id);
                if ($data != true || is_array($data)) {
                    $this->errors['take_offer'] = $data;
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

        return !empty($this->errors) ? $this->errors : true;
    }

    /**
     * @param $wm_offer_id
     * @return bool
     */
    public function saveCheckboxes($wm_offer_id)
    {
        $wm_offer = new WmCheckboxes();

        $wm_offer->setAttributes([
            'wm_offer_id' => $wm_offer_id,
            'websites' => (int)$this->data['websites'],
            'doorway' => (int)$this->data['doorway'],
            'contextual_advertising' => (int)$this->data['contextual_advertising'],
            'for_the_brand' => (int)$this->data['for_the_brand'],
            'teaser_advertising' => (int)$this->data['teaser_advertising'],
            'banner_advertising' => (int)$this->data['banner_advertising'],
            'social_networks_targeting_ads' => (int)$this->data['social_networks_targeting_ads'],
            'games_applications' => (int)$this->data['games_applications'],
            'email_marketing' => (int)$this->data['email_marketing'],
            'cash_back' => (int)$this->data['cash_back'],
            'click_under' => (int)$this->data['click_under'],
            'motivated' => (int)$this->data['motivated'],
            'adult' => (int)$this->data['adult'],
            'toolbar_traffic' => (int)$this->data['toolbar_traffic'],
            'sms_sending' => (int)$this->data['sms_sending'],
            'spam' => (int)$this->data['spam'],
        ]);

        if (!$wm_offer->validate() || !$wm_offer->save()) {
            $this->errors['take_offer'] = $wm_offer->errors;
            return false;
        }
        return true;
    }

    private function isOfferExists()
    {
        return Offer::find()->where(['offer_id' => $this->offer_id])->exists();
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}