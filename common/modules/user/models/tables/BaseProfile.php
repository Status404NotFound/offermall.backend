<?php


namespace common\modules\user\models\tables;

use common\modules\user\traits\ModuleTrait;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "base_profile".
 *
 * @property integer $user_id
 * @property string $name
 * @property integer $phone_number
 * @property string $location
 * @property string $avatar
 * @property string $timezone
 * @property string $notification_audio
 * @property string $notification_audio_name
 *
 * @property User $user
 *
 * @author makandy <makandy42@gmail.com>
 */
class BaseProfile extends ActiveRecord {
    use ModuleTrait;

    /**
     * Returns avatar url or null if avatar is not set.
     * @param  int $size
     * @return string|null
     */
    public function getAvatarUrl($size = 200) {
        if (empty($this->avatar_path)) {
            return null;
        }
        return '/avatar/' . $this->avatar_path . '?s=' . $size;
    }

    /**
     * @return \yii\db\ActiveQueryInterface
     */
    public function getUser() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            'nameLength'           => ['name', 'string', 'max' => 255],
            'locationLength'       => ['location', 'string', 'max' => 255],
            'timeZoneValidation'   => ['timezone', 'validateTimeZone'],
            'timeZoneLength'       => ['timezone', 'string', 'max' => 40],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'name'              => \Yii::t('user', 'Name'),
            'phone_number'              => \Yii::t('user', 'Phone Number'),
            'location'          => \Yii::t('user', 'Location'),
            'avatar'       => \Yii::t('user', 'Avatar'),
            'timezone'          => \Yii::t('user', 'Time zone'),
            'notification_audio'          => \Yii::t('user', 'Notification audio'),
        ];
    }

    /**
     * Validates the timezone attribute.
     * Adds an error when the specified time zone doesn't exist.
     * @param string $attribute the attribute being validated
     */
    public function validateTimeZone($attribute) {
        if (!in_array($this->$attribute, timezone_identifiers_list())) {
            $this->addError($attribute, \Yii::t('user', 'Time zone is not valid'));
        }
    }

    /**
     * Get the user's time zone.
     * Defaults to the application timezone if not specified by the user.
     * @return \DateTimeZone
     */
    public function getTimeZone() {
        try {
            return new \DateTimeZone($this->timezone);
        } catch (\Exception $e) {
            // Default to application time zone if the user hasn't set their time zone
            return new \DateTimeZone(\Yii::$app->timeZone);
        }
    }

    /**
     * Set the user's time zone.
     * @param \DateTimeZone $timeZone the timezone to save to the user's base profile
     */
    public function setTimeZone(\DateTimeZone $timeZone) {
        $this->setAttribute('timezone', $timeZone->getName());
    }

    /**
     * Converts DateTime to user's local time
     * @param \DateTime the datetime to convert
     * @return \DateTime
     */
    public function toLocalTime(\DateTime $dateTime = null) {
        if ($dateTime === null) {
            $dateTime = new \DateTime();
        }

        return $dateTime->setTimezone($this->getTimeZone());
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%base_profile}}';
    }
}
