<?php

namespace common\models\offer;

use common\models\geo\Countries;
use common\modules\user\models\tables\User;
use Yii;

/**
 * This is the model class for table "offer_note".
 *
 * @property integer $offer_note_id
 * @property integer $offer_id
 * @property integer $advert_id
 * @property integer $geo_id
 * @property string $note
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $advert
 * @property Countries $geo
 * @property Offer $offer
 */
class OfferNote extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'offer_note';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['offer_id', 'advert_id', 'geo_id'], 'required'],
            [['offer_id', 'advert_id', 'geo_id'], 'integer'],
            [['note'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['advert_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['advert_id' => 'id']],
            [['geo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Countries::className(), 'targetAttribute' => ['geo_id' => 'id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'offer_note_id' => Yii::t('app', 'Offer Note ID'),
            'offer_id' => Yii::t('app', 'Offer ID'),
            'advert_id' => Yii::t('app', 'Advert ID'),
            'geo_id' => Yii::t('app', 'Geo ID'),
            'note' => Yii::t('app', 'Note'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvert()
    {
        return $this->hasOne(User::className(), ['id' => 'advert_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGeo()
    {
        return $this->hasOne(Countries::className(), ['id' => 'geo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }
}
