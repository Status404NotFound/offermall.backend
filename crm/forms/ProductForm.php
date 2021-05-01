<?php

namespace crm\forms;

use common\models\User;
use Yii;
use \yii\base\Model;

/**
 * ProductForm model
 */
class ProductForm extends Model
{
    /**
     * @var
     */
    public $product_name;
    /**
     * @var
     */
    public $owner_id;
    /**
     * @var
     */
    public $category;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['owner_id', 'product_name', 'created_at', 'created_by', 'updated_by'], 'required'],
            [['owner_id', 'created_by', 'updated_by', 'category'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_name'], 'string', 'max' => 255],
            [['owner_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['owner_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => Yii::t('app', 'Product ID'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'product_name' => Yii::t('app', 'Product Name'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'category' => Yii::t('app', 'Category'),
        ];
    }
}