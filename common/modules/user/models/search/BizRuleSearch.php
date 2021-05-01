<?php

namespace common\modules\user\models\search;

use common\modules\user\traits\AuthManagerTrait;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use common\modules\user\models\BizRule;
use common\modules\user\rule\RouteRule;

/**
 * Description of BizRule
 *
 * Dependencies:
 * @property-read \yii\rbac\ManagerInterface $authManager
 *
 * @author makandy <makandy42@gmail.com>
 */
class BizRuleSearch extends Model {
    use AuthManagerTrait;
    /**
     * @var string name of the rule
     */
    public $name;

    public function rules()
    {
        return [
            [['name'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('user', 'Name'),
        ];
    }

    /**
     * Search BizRule
     * @param array $params
     * @return \yii\data\ActiveDataProvider|\yii\data\ArrayDataProvider
     */
    public function search($params)
    {
        $authManager = $this->authManager;
        $models = [];
        $included = !($this->load($params) && $this->validate() && trim($this->name) !== '');
        foreach ($authManager->getRules() as $name => $item) {
            if ($name != RouteRule::RULE_NAME && ($included || stripos($item->name, $this->name) !== false)) {
                $models[$name] = new BizRule($item);
            }
        }

        return new ArrayDataProvider([
            'allModels' => $models,
        ]);
    }
}
