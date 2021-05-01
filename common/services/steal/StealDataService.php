<?php

namespace common\services\steal;

use Yii;
use common\models\steal\StealDataSent;
use common\models\landing\Landing;
use common\models\webmaster\parking\ParkingDomain;
use common\services\whois\WhoisService;
use common\services\ValidateException;
use common\services\order\logic\status\ChangeStatusException;
use common\services\ServiceException;
use linslin\yii2\curl\Curl;
use yii\helpers\ArrayHelper;
use yii\base\Exception;

/**
 * Class StealDataService
 * @package common\services\steal
 */
class StealDataService
{
    /**
     * Name of file
     * @var string
     */
    private $filename = 'steal_form_log.txt';

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function getStealLog($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $query = StealDataSent::find();

        if (isset($filters['site'])) $query->andWhere(['like', 'site', $filters['site']['value']]);
        if (isset($filters['status'])) $query->andWhere(['status' => $filters['status']['value']]);

        if (isset($filters['date_sent'])) {
            $start = new \DateTime($filters['date_sent']['start']);
            $start->setTime(0, 0, 0);
            $start_date = $start->format('Y-m-d H:i:s');

            $end = new \DateTime($filters['date_sent']['end']);
            $end->setTime(23, 59, 59);
            $end_date = $end->format('Y-m-d H:i:s');

            $query->andWhere(['>', '`steal_data_sent`.date_sent', $start_date]);
            $query->andWhere(['<', '`steal_data_sent`.date_sent', $end_date]);
        }

        if (isset($sort_field)) {
            $query->orderBy([$sort_field => $sort_order]);
        } else {
            $query->orderBy(['status' => StealDataSent::STATUS_NOT_VIEW]);
        }

        $count = clone $query;
        $count_all = $count->count();

        if (isset($pagination)) $query->offset($pagination['first_row'])->limit($pagination['rows']);

        return [
            'result' => $query
                ->asArray()
                ->all(),
            'count' => [
                'count_all' => $count_all,
            ],
        ];
    }

    /**
     * @param string $domain
     * @return mixed|string
     */
    public function checkSite(string $domain)
    {
        $result = '';

        $landing = Landing::find()->select('url')->where(['url' => $domain])->asArray()->one();
        $parking_domain = ParkingDomain::find()->select('domain_name')->where(['domain_name' => $domain])
            ->andWhere(['is_deleted' => 0])->asArray()->one();

        if (!empty($landing)) {
            $result = $landing['url'];
        }

        if (!empty($parking_domain)) {
            $result = $parking_domain['domain_name'];
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function readLogFile()
    {
        $curl = new Curl();
        $url = 'http://monstertds.com/' . $this->filename;

        try {
            $data = $curl->get($url);
            $curl->head($url);
            if ($curl->getInfo()['http_code'] == 404) {
                $result['data'] = 'No file on: ' . $url . ' Or no domain exists.';
                $result['log'] = $url;
                $result['error'] = true;
            } else {
                $result['data'] = $data;
                $result['landing'] = $url;
            }
        } catch (\Exception $e) {
            if ($e) {
                $result['data'] = 'No file on: ' . $url . ' Or no domain exists.';
                $result['log'] = $url;
                $result['error'] = true;
            }
        }

        return $result;
    }

    /**
     * @param $data
     * @return bool|int
     */
    public function writeToLogFile($data)
    {
        $filename = 'steal_form_log.txt';
        $file = fopen($filename, 'a');
        $message = date('d.m.Y H:i:s', time()).'---'.$data.'---'.PHP_EOL;
        $result = fwrite($file, $message);
        fclose($file);

        return $result;
    }

    /**
     * @param array $filters
     * @param null $pagination
     * @param null $sort_order
     * @param null $sort_field
     * @return array
     */
    public function readFormLog($filters = [], $pagination = null, $sort_order = null, $sort_field = null)
    {
        $file = $this->readLogFile();

        if (empty($file['data'])) return [];

        $content = explode('}---', $file['data']);

        foreach ($content as $row) {
            $data = explode('---{', $row);
            $index = trim($data[0]);
            if ($index === '') continue;
            $order_data = json_decode('{' . $data[1] . '}', true);

            if (isset($order_data['fields'])) {
                $order = $order_data;
                $order['created_at'] = $index;
            } else {
                $order['fields'] = $order_data;
                $order['created_at'] = $index;
                $order['site'] = $order_data['site'];
            }

            $order['created_at'] = $index ?? 0;
            $result[] = $order;
        }

        if (isset($sort_field)) {
            ArrayHelper::multisort($result, $sort_field, $sort_order);
        } else {
            ArrayHelper::multisort($result, 'created_at', SORT_DESC);
        }

        if (isset($filters['site'])) {
            $site = strtolower(trim($filters['site']['value']));
            $result = array_filter($result, function ($rule) use ($site) {
                return (empty($site) || strpos((strtolower(is_object($rule) ? $rule->site : $rule['site'])), $site) !== false);
            });
        }

        if (isset($filters['created_at'])) {
            $from_date = strtotime($filters['created_at']['start']);
            $to_date = strtotime($filters['created_at']['end']);

            $date = array_filter($result, function ($array) use ($from_date, $to_date) {
                $epoch = strtotime($array['created_at']);
                return $epoch >= $from_date && $epoch <= $to_date;
            });

            $result = array_values($date);
        }

        $slice = isset($pagination) ? array_slice($result, $pagination['first_row'], $pagination['rows']) : $result;
        $total_rows = !empty($filters) ? count($slice) : count($result);

        return [
            'result' => $slice,
            'count' => [
                'count_all' => $total_rows,
            ],
        ];
    }

    /**
     * @param $landing
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function sendMail($landing)
    {
        $who_is = (new WhoisService())->whoisInfo($landing);

        return Yii::$app->mailer->compose('landing',
            ['site' => $landing, 'who_is' => $who_is, 'time' => Yii::$app->formatter->asDate(time(), 'php:d.m.Y H:i')])
            ->setFrom(['lead@crmka.net' => 'Crmka'])
            ->setTo(['admin@crmka.net'])
            ->setSubject('Был произведен запуск лендинга на ' . $landing)
            ->send();
    }

    /**
     * @param string $site
     * @return bool
     * @throws ServiceException
     * @throws ValidateException
     * @throws \yii\base\InvalidConfigException
     */
    public function save(string $site)
    {
        if (empty($site))
            throw new ServiceException('Site not set.');

        $steal = new StealDataSent();

        $steal->setAttributes([
            'site' => $site,
            'status' => StealDataSent::STATUS_NOT_VIEW
        ]);

        if (!$steal->save())
            throw new ValidateException($steal->errors);

        return $this->sendMail($steal->site);
    }

    /**
     * @param $id
     * @param $status
     * @throws Exception
     * @throws ValidateException
     * @throws \yii\db\Exception
     */
    public function changeStatus($id, $status)
    {
        $model = StealDataSent::findOne(['site_id' => $id]);

        $tx = Yii::$app->db->beginTransaction();
        try {
            if (!StealDataSent::statuses($status))
                throw new ChangeStatusException('Status not exists');
            $model->setAttribute('status', $status);
            if ($model->save() !== true)
                throw new ChangeStatusException('Failed to set Site :' . $model->site . ' status ' . StealDataSent::statuses($status));
            $tx->commit();
        } catch (ValidateException $e) {
            $tx->rollBack();
            throw $e;
        } catch (Exception $e) {
            $tx->rollBack();
            throw $e;
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
