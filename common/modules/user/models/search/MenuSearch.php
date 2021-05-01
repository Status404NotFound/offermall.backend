<?php

namespace common\modules\user\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\modules\user\models\tables\Menu as MenuModel;

/**
 * Menu represents the model behind the search form about [[\common\modules\user\models\Menu]].
 *
 * Dependencies:
 * @property-read \yii\rbac\ManagerInterface $authManager
 *
 * @author makandy <makandy42@gmail.com>
 * @since 1.0
 */
class MenuSearch extends MenuModel
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'parent', 'order'], 'integer'],
            [['name', 'route', 'parent_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Searching menu
     * @param  array $params
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params)
    {
        $query = MenuModel::find()
            ->from(MenuModel::tableName() . ' t')
            ->joinWith(['menuParent' => function ($q) {
            $q->from(MenuModel::tableName() . ' parent');
        }]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $sort = $dataProvider->getSort();
        $sort->attributes['menuParent.name'] = [
            'asc' => ['parent.name' => SORT_ASC],
            'desc' => ['parent.name' => SORT_DESC],
            'label' => 'parent',
        ];
        $sort->attributes['order'] = [
            'asc' => ['parent.order' => SORT_ASC, 't.order' => SORT_ASC],
            'desc' => ['parent.order' => SORT_DESC, 't.order' => SORT_DESC],
            'label' => 'order',
        ];
        $sort->defaultOrder = ['menuParent.name' => SORT_ASC];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            't.id' => $this->id,
            't.parent' => $this->parent,
        ]);

        $query->andFilterWhere(['like', 'lower(t.name)', strtolower($this->name)])
            ->andFilterWhere(['like', 't.route', $this->route])
            ->andFilterWhere(['like', 'lower(parent.name)', strtolower($this->parent_name)]);

        return $dataProvider;
    }
}
