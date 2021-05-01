<?php

namespace common\models\landing;

use common\models\flow\FlowLanding;
use common\models\offer\Offer;
use tds\modules\genform\tables\GenFormTable;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "landing".
 *
 * @property integer $landing_id
 * @property string $name
 * @property string $url
 * @property integer $offer_id
 * @property integer $form_id
 *
 * @property FlowLanding[] $flowLandings
 * @property GenFormTable $form
 * @property Offer $offer
 * @property LandingGeo[] $landingGeos
 */
class Landing extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'landing';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'url', 'offer_id', 'form_id'], 'required'],
            [['landing_id', 'offer_id', 'form_id', 'wm_id'], 'integer'],
            [['name', 'url'], 'string', 'max' => 255],
//            [['form_id'], 'exist', 'skipOnError' => true, 'targetClass' => GenFormTable::className(), 'targetAttribute' => ['form_id' => 'id']],
            [['offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Offer::className(), 'targetAttribute' => ['offer_id' => 'offer_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'landing_id' => 'Landing ID',
            'name' => 'Name',
            'url' => 'Url',
            'offer_id' => 'Offer ID',
            'form_id' => 'Form ID',
            'wm_id' => 'Wm ID'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFlowLandings()
    {
        return $this->hasMany(FlowLanding::className(), ['landing_id' => 'landing_id']);
    }

//    /**
//     * @return \yii\db\ActiveQuery
//     */
//    public function getForm()
//    {
//        return $this->hasOne(GenFormTable::className(), ['id' => 'form_id']);
//    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(Offer::className(), ['offer_id' => 'offer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLandingGeos()
    {
        return $this->hasMany(LandingGeo::className(), ['landing_id' => 'landing_id']);
    }

    /**
     * @return array|null
     */
    public static function getAllOfferLandings()
    {
        $query = self::find()
            ->select(['url'])
            ->asArray()
            ->all();

        if (!empty($query)){
            return ArrayHelper::getColumn($query, 'url');
        } else {
            return null;
        }
    }

    /**
     * @param $offer_id
     * @return array|FlowLanding[]|\common\models\flow\FlowTransit[]|Landing[]|\common\models\LandingViews[]|\common\models\webmaster\WmCheckout[]|\common\models\webmaster\WmProfile[]|\yii\db\ActiveRecord[]
     */
    public static function getOfferLandings($offer_id)
    {
        return self::find()
            ->where(['offer_id' => $offer_id])
            ->asArray()
            ->all();
    }

    public static function getLandingByUrl($url)
    {
        return self::find()
            ->where(['url' => $url])
            ->one();
    }

    public static function getUrlLandingById($id)
    {
        return self::findOne($id)->url;
    }

    public static function checkExistByUrl($url) :bool
    {
        if(isset(self::find()->where(['url' => $url])->one()->landing_id)){
            return true;
        }
        return false;
    }

    public static function generateLandingName() :string
    {
        $lastInsertID = Yii::$app->db->getLastInsertID();
        return 'land-'.$lastInsertID;
    }
}
