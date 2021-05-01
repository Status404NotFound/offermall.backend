<?php

namespace common\modules\user\models\search;

use common\modules\user\traits\AuthManagerTrait;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\rbac\Item;

/**
 * AuthItemSearch represents the model behind the search form about AuthItem.
 *
 * Dependencies:
 * @property-read \yii\rbac\ManagerInterface $authManager
 *
 * @author makandy <makandy42@gmail.com>
 */
class AuthItemSearch extends Model
{
    use AuthManagerTrait;

    const TYPE_ROUTE = 101;

    public $name;
    public $type;
    public $description;
    public $ruleName;
    public $data;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'ruleName', 'description'], 'safe'],
            [['type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('user', 'Name'),
            'item_name' => Yii::t('user', 'Name'),
            'type' => Yii::t('user', 'Type'),
            'description' => Yii::t('user', 'Description'),
            'ruleName' => Yii::t('user', 'Rule Name'),
            'data' => Yii::t('user', 'Data'),
        ];
    }

    /**
     * Search auth item
     * @param array $params
     * @return \yii\data\ActiveDataProvider|\yii\data\ArrayDataProvider
     */
    public function search($params) {
        $authManager = $this->authManager;
        if ($this->type == Item::TYPE_ROLE) {
            $items = $authManager->getRoles();
        } else {
            $items = array_filter($authManager->getPermissions(), function($item) {
                return $this->type == Item::TYPE_PERMISSION xor strncmp($item->name, '/', 1) === 0;
            });
        }
        $this->load($params);
        if ($this->validate()) {

            $search = mb_strtolower(trim($this->name));
            $desc = mb_strtolower(trim($this->description));
            $ruleName = $this->ruleName;
            foreach ($items as $name => $item) {
                $f = (empty($search) || mb_strpos(mb_strtolower($item->name), $search) !== false) &&
                    (empty($desc) || mb_strpos(mb_strtolower($item->description), $desc) !== false) &&
                    (empty($ruleName) || $item->ruleName == $ruleName);
                if (!$f) {
                    unset($items[$name]);
                }
            }
        }

        return new ArrayDataProvider([
            'allModels' => $items,
        ]);
    }
}
