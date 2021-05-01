<?php

namespace common\modules\user\models\tables;

use Yii;
use yii\db\Query;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_child}}".
 *
 * @property integer $parent
 * @property integer $child
 *
 * @property User $child0
 * @property User $parent0
 *
 * @author makandy <makandy42@gmail.com>
 */
class UserChild extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_child}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent', 'child'], 'required'],
            [['parent', 'child'], 'integer'],
            [['child'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['child' => 'id']],
            [['parent'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['parent' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent' => Yii::t('app', 'Parent'),
            'child' => Yii::t('app', 'Child'),
        ];
    }

    /**
     * @param User|null $parent
     * @param User $child
     * @return bool
     */
    public function setRelation(User $parent = null, User $child)
    {
        try {
            $child->unlinkAll('parent', true);
            if ($parent !== null) {
                $child->link('parent', $parent);
            }
        } catch (\Exception $e) {
            echo 'Exception: ', $e->getMessage(), "\n";
            return false;
        }
        return true;
    }

    /**
     * @param $parents
     * @param User $child
     * @return bool
     * @throws \yii\db\Exception
     */
    public function setParent($parents, User $child)
    {
        if ($parents != null && is_array($parents)) {

            $resp = [];
            foreach ($parents as $parent) {
                $resp[] = [
                    'parent' => $parent,
                    'child' => $child->id
                ];
            }

            if (!empty($resp)) {
                Yii::$app->db->createCommand()
                    ->batchInsert(self::tableName(), ['parent', 'child'], $resp)
                    ->execute();
            }
        }

        return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChild()
    {
        return $this->hasOne(User::className(), ['id' => 'child']);
    }

    /**
     */
    public function getParent()
    {
        return $this->hasMany(User::className(), ['id' => 'parent']);
    }
}