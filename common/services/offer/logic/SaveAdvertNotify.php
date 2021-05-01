<?php

namespace common\services\offer\logic;

use Yii;
use common\models\offer\Offer;
use common\services\ValidateException;
use common\services\offer\OfferNotFoundException;
use common\models\offer\targets\advert\AdvertOfferTarget;
use common\models\offer\targets\advert\TargetAdvert;
use common\models\offer\targets\advert\TargetAdvertGroup;
use yii\base\InvalidParamException;
use yii\base\Exception;

class SaveAdvertNotify
{
    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var
     */
    public $offer_id;

    /**
     * @var mixed
     */
    public $data;

    /**
     * SaveAdvertNotify constructor.
     * @param $offer_id
     * @param array $data
     */
    public function __construct($offer_id, $data = [])
    {
        $this->offer_id = $offer_id;
        $this->data = $data['notify'];
    }

    /**
     * @return bool
     * @throws OfferNotFoundException
     * @throws \yii\db\Exception
     */
    public function execute()
    {
        if (!$this->isOfferExists()) {
            throw new OfferNotFoundException('Offer not found');
        }
        return $this->saveData();
    }

    /**
     * @return bool
     * @throws \yii\db\Exception
     */
    private function saveData()
    {
        $mainTx = Yii::$app->db->beginTransaction();
        try {
            $result = $this->saveAdvertNotify();
            $mainTx->commit();
        } catch (InvalidParamException $e) {
            $this->errors['InvalidParamException'] = $e;
            $mainTx->rollBack();
            $result = false;
        } catch (ValidateException $e) {
            $this->errors['Exception'] = $e->getMessages();
            $mainTx->rollBack();
            $result = false;
        } catch (Exception $e) {
            $this->errors['Exception'] = $e;
            $mainTx->rollBack();
            $result = false;
        }

        return $result;
    }

    /**
     * @return bool
     * @throws ValidateException
     */
    public function saveAdvertNotify()
    {
        foreach ($this->data as $data) {
            $targetAdvertGroup = $this->findTargetAdvertGroup($data['target_advert_group_id']);

            if (!$targetAdvertGroup)
                return $this->errors['saveNotify'] = 'Oops! Something went wrong :(';

            $targetAdvertGroup->updateAttributes([
                'send_sms_customer' => (int)$data['send_sms_customer'],
                'send_second_sms_customer' => (int)$data['send_second_sms_customer'],
                'send_sms_owner' => (int)$data['send_sms_owner'],
                'sms_text_customer' => $data['sms_text_customer'],
                'second_sms_text_customer' => $data['second_sms_text_customer'],
                'sms_text_owner' => $data['sms_text_owner']
            ]);

            if (!$targetAdvertGroup->save()) {
                throw new ValidateException($targetAdvertGroup->errors);
            }
        }

        return true;
    }

    public function findTargetAdvertGroup($target_advert_group_id)
    {
        return TargetAdvertGroup::findOne(['target_advert_group_id' => $target_advert_group_id]);

    }

    public function findTargetAdvert($target_advert_group_id, $advert_id)
    {
        return TargetAdvert::findOne([
            'target_advert_group_id' => $target_advert_group_id,
            'advert_id' => (integer)$advert_id,
        ]);
    }

    public function findAdvertOfferTarget($advert_offer_target_id, $advert_offer_target_status, $geo_id)
    {
        return AdvertOfferTarget::findOne([
            'offer_id' => $this->offer_id,
            'advert_offer_target_id' => $advert_offer_target_id,
            'advert_offer_target_status' => $advert_offer_target_status,
            'geo_id' => $geo_id,
        ]);
    }

    public function findAdvertOfferTargets()
    {
        return AdvertOfferTarget::find()
            ->where(['offer_id' => $this->offer_id])
            ->joinWith('targetAdvertGroups')
            ->indexBy('advert_offer_target_id')
            ->all();
    }

    private function isOfferExists()
    {
        return Offer::find()
            ->where(['offer_id' => $this->offer_id])
            ->count();
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}