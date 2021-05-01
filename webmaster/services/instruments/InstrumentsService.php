<?php

namespace webmaster\services\instruments;

use Yii;
use common\models\webmaster\parking\ParkingDomain;
use common\models\webmaster\postback\PostbackGlobal;
use common\models\webmaster\postback\PostbackIndividual;
use common\models\landing\Landing;
use common\services\ValidateException;

/**
 * Class InstrumentsService
 * @package webmaster\services\instruments
 */
class InstrumentsService
{
    /**
     * @return array|ParkingDomain[]
     */
    public function getParkedDomains()
    {
        $result = ParkingDomain::find()
            ->select([
                'parking_domain.domain_id',
                'parking_domain.wm_id',
                'parking_domain.domain_name as parking_domain',
                'parking_domain.flow_id',
                'parking_domain.geo_id',
                'geo.geo_id',
                'IF(geo.geo_name is null or geo.geo_name = \'\', \'Not set\', geo.geo_name) as geo_name',
                'geo.iso',
                'flow.flow_name',
                'flow.updated_at',
                'user.username',
                'offer.offer_name'
            ])
            ->join('LEFT JOIN', 'flow', 'flow.flow_id = parking_domain.flow_id')
            ->join('LEFT JOIN', 'user', 'user.id = parking_domain.wm_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = flow.offer_id')
            ->join('LEFT JOIN', 'geo', 'geo.geo_id = parking_domain.geo_id')
            ->where(['parking_domain.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->andWhere(['parking_domain.is_deleted' => 0])
            ->active()
            ->orderBy(['parking_domain.created_at' => SORT_DESC])
            ->asArray()
            ->all();

        return $result;
    }

    /**
     * @param string $domain_id
     * @return array|ParkingDomain|null
     */
    public function getParkedDomainById(string $domain_id)
    {
        $result = ParkingDomain::find()
            ->select([
                'parking_domain.domain_id',
                'parking_domain.wm_id',
                'parking_domain.flow_id',
                'parking_domain.domain_name as parking_domain',
                'parking_domain.geo_id',
                'geo.geo_id',
                'geo.geo_name',
                'geo.iso',
                'flow.flow_name',
                'user.username'
            ])
            ->join('LEFT JOIN', 'flow', 'flow.flow_id = parking_domain.flow_id')
            ->join('LEFT JOIN', 'user', 'user.id = parking_domain.wm_id')
            ->join('LEFT JOIN', 'geo', 'geo.geo_id = parking_domain.geo_id')
            ->where(['parking_domain.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->andWhere(['parking_domain.domain_id' => $domain_id])
            ->active()
            ->asArray()
            ->one();

        return $result;
    }

    /**
     * @param string $domain_id
     * @return array|ParkingDomain|null
     */
    public function getLink(string $domain_id)
    {
        $result = ParkingDomain::find()
            ->select([
                'flow.flow_key',
                'flow.flow_name',
                'parking_domain.domain_name',
            ])
            ->join('LEFT JOIN', 'flow', 'flow.flow_id = parking_domain.flow_id')
            ->where(['parking_domain.wm_id' => Yii::$app->user->identity->getWmChild()])
            ->andWhere(['parking_domain.domain_id' => $domain_id])
            ->active()
            ->asArray()
            ->one();

        return $result;
    }

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function getIndividualPostbackList($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $query = PostbackIndividual::find()
            ->select([
                'postback_individual.postback_individual_id',
                'postback_individual.url',
                'postback_individual.url_approved',
                'postback_individual.url_cancelled',
                'postback_individual.url_notValid',
                'postback_individual.created_at',
                'flow.flow_name',
            ])
            ->leftJoin('flow', 'flow.flow_id = postback_individual.flow_id')
            ->where(['postback_individual.wm_id' => Yii::$app->user->identity->getId()]);

        if (isset($filters['flow_id'])) $query->andWhere(['postback_individual.flow_id' => $filters['flow_id']]);
        if (isset($filters['url']['value'])) $query->andWhere(['LIKE', 'postback_individual.url', $filters['url']['value']]);
        if (isset($filters['url_approved']['value'])) $query->andWhere(['LIKE', 'postback_individual.url_approved', $filters['url_approved']['value']]);
        if (isset($filters['url_cancelled']['value'])) $query->andWhere(['LIKE', 'postback_individual.url_cancelled', $filters['url_cancelled']['value']]);
        if (isset($filters['url_notValid']['value'])) $query->andWhere(['LIKE', 'postback_individual.url_notValid', $filters['url_notValid']['value']]);

        if (isset($filters['time'])) {
            $start = new \DateTime($filters['time']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['time']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', 'postback_individual.created_at', $start_date]);
            $query->andWhere(['<', 'postback_individual.created_at', $end_date]);
        }

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy([
                'postback_individual.created_at' => SORT_DESC
            ]);
        }

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        $count = clone $query;
        $count_all = $count->count();

        $individual_list = $query
            ->asArray()
            ->all();

        return [
            'list' => $individual_list,
            'count' => [
                'count_all' => $count_all
            ],
        ];
    }

    /**
     * @param array $data
     * @throws ValidateException
     */
    public function saveIndividualPostback(array $data)
    {
        $postback_individual = PostbackIndividual::findOne(['flow_id' => $data['flow']]) ?? new PostbackIndividual();

        $postback_individual->setAttributes([
            'wm_id' => Yii::$app->user->identity->getId(),
            'flow_id' => trim($data['flow']),
            'url' => !empty($data['url']) ? trim($data['url']) : null,
            'url_approved' => !empty($data['url_approved']) ? trim($data['url_approved']) : null,
            'url_cancelled' => !empty($data['url_cancelled']) ? trim($data['url_cancelled']) : null,
            'url_notValid' => !empty($data['url_notValid']) ? trim($data['url_notValid']) : null,
        ]);

        if (!$postback_individual->save())
            throw new ValidateException($postback_individual->errors);
    }

    /**
     * @param array $data
     * @throws ValidateException
     */
    public function saveGlobalPostback(array $data)
    {
        $postback_global = PostbackGlobal::findOne(['wm_id' => Yii::$app->user->identity->getId()]) ?? new PostbackGlobal();

        $postback_global->setAttributes([
            'wm_id' => Yii::$app->user->identity->getId(),
            'url' => !empty($data['url']) ? trim($data['url']) : null,
            'url_approved' => !empty($data['url_approved']) ? trim($data['url_approved']) : null,
            'url_cancelled' => !empty($data['url_cancelled']) ? trim($data['url_cancelled']) : null,
            'url_notValid' => !empty($data['url_notValid']) ? trim($data['url_notValid']) : null,
        ]);

        if (!$postback_global->save())
            throw new ValidateException($postback_global->errors);
    }

    /**
     * @param string $postback_individual_id
     * @return bool
     * @throws InstrumentsNotFoundException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deletePostback(string $postback_individual_id)
    {
        $postback_individual = PostbackIndividual::findOne(['postback_individual_id' => $postback_individual_id]);

        if (!isset($postback_individual)) {
            throw new InstrumentsNotFoundException('Oops, something went wrong.');
        }

        if ($postback_individual->delete()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $data
     * @throws ValidateException
     */
    public function save(array $data)
    {
//        if ($this->checkDomain($request['domain_url']))
//            throw new InstrumentsNotFoundException('This domain not found.');

        $domain = new ParkingDomain();

        $domain->setAttributes([
            'flow_id' => $data['flow_id'],
            'wm_id' => isset($data['wm_id']) ? $data['wm_id'] : Yii::$app->user->identity->getId(),
            'domain_name' => trim($data['domain_url']),
            'geo_id' => isset($data['geo_id']) ? $data['geo_id'] : null,
        ]);

        if (!$domain->save())
            throw new ValidateException($domain->errors);
    }

    /**
     * @param array $data
     * @param string $domain_id
     * @throws InstrumentsNotFoundException
     * @throws ValidateException
     */
    public function update(array $data, string $domain_id)
    {
        $domain = ParkingDomain::findOne(['domain_id' => $domain_id]);

        if (!isset($domain)) {
            throw new InstrumentsNotFoundException('Oops, something went wrong.');
        }

        $domain->setAttributes([
            'flow_id' => $data['flow_id'],
            'wm_id' => isset($data['wm_id']) ? $data['wm_id'] : Yii::$app->user->identity->getId(),
            'geo_id' => isset($data['geo_id']) ? $data['geo_id'] : null,
        ]);

        if (!$domain->save())
            throw new ValidateException($domain->errors);
    }

    /**
     * @param string $domain_id
     * @throws InstrumentsNotFoundException
     * @throws ValidateException
     */
    public function delete(string $domain_id)
    {
        $domain = ParkingDomain::findOne(['domain_id' => $domain_id]);

        if (!isset($domain)) {
            throw new InstrumentsNotFoundException('Oops, something went wrong.');
        }

        $domain->setAttribute('is_deleted', 1);

        if (!$domain->save())
            throw new ValidateException($domain->errors);
    }

    /**
     * @param string $domain
     * @return bool
     */
    private function checkDomain(string $domain)
    {
        return Landing::find()->where(['LIKE', 'url', $domain])->exists();
    }
}