<?php
/**
 * Created by PhpStorm.
 * User: bogdan-fish
 * Date: 13.03.2017
 * Time: 16:30
 */

namespace crm\forms;

use Yii;
use \yii\base\Model;

/**
 * OfferForm model
 */
class StockForm extends Model
{
    public $stock_id;
    public $owner_id;
    public $stock_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stock_id', 'owner_id', 'stock_name'], 'required'],
            [['stock_id', 'owner_id'], 'integer'],
            [['stock_name'], 'string', 'max' => 255],
            ['stock_id', 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'stock_id' => Yii::t('app', 'Stock ID'),
            'owner_id' => Yii::t('app', 'Owner ID'),
            'stock_name' => Yii::t('app', 'Stock Name'),
        ];
    }
}