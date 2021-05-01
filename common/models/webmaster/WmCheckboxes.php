<?php

namespace common\models\webmaster;

use Yii;
use common\models\BaseModel;

/**
 * This is the model class for table "{{%wm_checkboxes}}".
 *
 * @property integer $id
 * @property integer $wm_offer_id
 * @property integer $websites
 * @property integer $doorway
 * @property integer $contextual_advertising
 * @property integer $for_the_brand
 * @property integer $teaser_advertising
 * @property integer $banner_advertising
 * @property integer $social_networks_targeting_ads
 * @property integer $games_applications
 * @property integer $email_marketing
 * @property integer $cash_back
 * @property integer $click_under
 * @property integer $motivated
 * @property integer $adult
 * @property integer $toolbar_traffic
 * @property integer $sms_sending
 * @property integer $spam
 * @property string $created_at
 *
 * @property WmOffer $wmOffer
 */
class WmCheckboxes extends BaseModel
{
    public $updated_at = false;
    public $created_by = false;
    public $updated_by = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wm_checkboxes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wm_offer_id'], 'required'],
            [['wm_offer_id', 'websites', 'doorway', 'contextual_advertising', 'for_the_brand', 'teaser_advertising', 'banner_advertising', 'social_networks_targeting_ads', 'games_applications', 'email_marketing', 'cash_back', 'click_under', 'motivated', 'adult', 'toolbar_traffic', 'sms_sending', 'spam'], 'integer'],
            [['created_at'], 'safe'],
            [['wm_offer_id'], 'exist', 'skipOnError' => true, 'targetClass' => WmOffer::className(), 'targetAttribute' => ['wm_offer_id' => 'wm_offer_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'wm_offer_id' => Yii::t('app', 'Webmaster Offer ID'),
            'websites' => Yii::t('app', 'Websites'),
            'doorway' => Yii::t('app', 'Doorway'),
            'contextual_advertising' => Yii::t('app', 'Contextual advertising'),
            'for_the_brand' => Yii::t('app', 'Contextual advertising for the brand'),
            'teaser_advertising' => Yii::t('app', 'Teaser advertising'),
            'banner_advertising' => Yii::t('app', 'Banner advertising'),
            'social_networks_targeting_ads' => Yii::t('app', 'Social networks: targeting advertising'),
            'games_applications' => Yii::t('app', 'Social networks: public relations, games, applications'),
            'email_marketing' => Yii::t('app', 'Email marketing'),
            'cash_back' => Yii::t('app', 'Cash back'),
            'click_under' => Yii::t('app', 'ClickUnder/PopUnder'),
            'motivated' => Yii::t('app', 'Motivated'),
            'adult' => Yii::t('app', 'Adult'),
            'toolbar_traffic' => Yii::t('app', 'Toolbar traffic'),
            'sms_sending' => Yii::t('app', 'SMS sending'),
            'spam' => Yii::t('app', 'Spam'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWmOffer()
    {
        return $this->hasOne(WmOffer::className(), ['wm_offer_id' => 'wm_offer_id']);
    }
}
