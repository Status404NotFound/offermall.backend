<?php

namespace common\services\contact;

use Yii;

/**
 * Class ContactSearchService
 *
 * @package common\services\contact
 */
class ContactSearchService
{
    private $min_digits = 6;
    private $addSort;

    /**
     * @param array $filters
     * @param       $pagination
     * @param       $sort_field
     * @param       $sort_order
     *
     * @return array
     * @throws ContactSearchNotFoundException
     * @throws \yii\db\Exception
     */
    public function searchContact($filters = [], $pagination, $sort_field, $sort_order)
    {
        if (empty($filters['phone']['value'])) {
            throw new ContactSearchNotFoundException('Phone number must be set!');
        }

        if ($this->checkPhoneNumberCount($filters['phone']['value'])) {
            $chars = preg_split('//', $filters['phone']['value'], - 1, PREG_SPLIT_NO_EMPTY);
            $percent_separated = implode("%", $chars);

            $owner_id = \Yii::$app->user->identity->getOwnerId();

            if ( !is_null($owner_id) && is_array($owner_id)) {
                $owners = implode(', ', $owner_id);

                $search_by_new = "AND order_view.owner_id in($owners)";
                $search_by_old = "AND old_history.advert_id in($owners)";
            } else {
                $search_by_new = "AND order_view.owner_id IS NOT NULL";
                $search_by_old = "AND old_history.advert_id IS NOT NULL";
            }

            $sort_order = ($sort_order == - 1) ? "DESC" : "ASC";

            if (isset($sort_field)) {
                $this->addSort = "ORDER BY $sort_field $sort_order";
            } else {
                $this->addSort = "ORDER BY created_at DESC";
            }

            //$result = Yii::$app->db->createCommand("SELECT
            //IF(`customer_view`.`name` is null or `customer_view`.`name` = '', 'Not set', `customer_view`.`name`) as name,
            //`customer_view`.`phone`,
            //IF(`customer_view`.`address` is null or `customer_view`.`address` = '', 'Not set', `customer_view`.`address`) as address,
            //IF(`customer_view`.`email` is null or `customer_view`.`email` = '', 'Not set', `customer_view`.`email`) as email,
            //IF(`customer_view`.`ip` is null or `customer_view`.`ip` = '', 'Not set', `customer_view`.`ip`) as ip,
            //`order_view`.`order_hash`,
            //DATE_FORMAT(`order_view`.created_at, '%d.%m.%Y %H:%i:%s') AS date,
            //`order_view`.created_at
            //FROM `customer_view`
            //JOIN `order_view` ON order_view.customer_id = customer_view.customer_id
            //WHERE customer_view.phone LIKE '%$percent_separated%' $this->addSort LIMIT " . $pagination['rows'] . " OFFSET " . $pagination['first_row'] . "")->queryAll();

            $sql = "
            SELECT *
            FROM (
               (SELECT
                  IF(`customer_view`.`name` is null or `customer_view`.`name` = '', 'Not set', `customer_view`.`name`)    as name,
                  `customer_view`.`phone`,
                  `order_view`.`offer_name`,
                  `order_status`.`status_name`,
                  IF(`customer_view`.`address` is null or `customer_view`.`address` = '', 'Not set',
                     `customer_view`.`address`)                                                                           as address,
                  IF(`customer_view`.`email` is null or `customer_view`.`email` = '', 'Not set',
                     `customer_view`.`email`)                                                                             as email,
                  IF(`customer_view`.`ip` is null or `customer_view`.`ip` = '', 'Not set', `customer_view`.`ip`)          as ip,
                  IF(`customer_view`.`country_name` is null or `customer_view`.`country_name` = '', 'Not set', `customer_view`.`country_name`)          as geo,
                  `order_view`.`order_hash`,
                  DATE_FORMAT(`order_view`.created_at,
                              '%d.%m.%Y %H:%i:%s')                                                                        AS date,
                  `order_view`.created_at
                FROM `customer_view`
                  STRAIGHT_JOIN `order_view` ON order_view.customer_id = customer_view.customer_id
                  JOIN `order_status` ON order_view.order_status = order_status.status_id
                WHERE
                  customer_view.phone LIKE '%$percent_separated%' $search_by_new)
               UNION ALL
               (SELECT
                  IF(`name` is null or `name` = '', 'Not set', `name`)    as `name`,
                  phone,
                  offer_name,
                  order_statuses_old_crm.status_name,
                  IF(address is null or address = '', 'Not set', address) as address,
                  IF(email is null or email = '', 'Not set', email)       as email,
                  IF(ip is null or ip = '', 'Not set', INET_NTOA(ip))     as ip,
                  IF(country_name is null or country_name = '', 'Not set', country_name)     as geo,
                  (SELECT '-----' as temp)                                as order_hash,
                  DATE_FORMAT(created_at, '%d.%m.%Y %H:%i:%s')            AS date,
                  created_at
                FROM `old_history`
                JOIN `order_statuses_old_crm` ON old_history.status = order_statuses_old_crm.status_id
                WHERE
                  phone LIKE '%$percent_separated%' $search_by_old)
             ) customer_view $this->addSort LIMIT " . $pagination['rows'] . " OFFSET " . $pagination['first_row'] . "";

            $result = Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($result as $value){

            }

            //$count_all = Yii::$app->db->createCommand("SELECT COUNT(*)
            //FROM `customer_view`
            //JOIN `order_view` ON order_view.customer_id = customer_view.customer_id
            //WHERE customer_view.phone LIKE '%$percent_separated%'")->queryScalar();

            $count_query = "
            SELECT COUNT(*)
            FROM (
               (SELECT phone
                FROM `customer_view`
                  STRAIGHT_JOIN `order_view` ON order_view.customer_id = customer_view.customer_id
                WHERE
                  customer_view.phone LIKE '%$percent_separated%' $search_by_new)
               UNION ALL
               (SELECT phone
                FROM `old_history`
                WHERE
                  phone LIKE '%$percent_separated%' $search_by_old)
             ) `customer_view`
            WHERE `phone` IS NOT NULL";

            $count_all = Yii::$app->db->createCommand($count_query)->queryScalar();
        }

        return [
            'count'  => [
                'count_all' => $count_all
            ],
            'result' => $result
        ];
    }

    /**
     * @param $number
     *
     * @return bool
     * @throws ContactSearchNotFoundException
     */
    private function checkPhoneNumberCount($number)
    {
        $count = strlen($number);
        if ($this->min_digits > $count) {
            throw new ContactSearchNotFoundException('Minimum ' . $this->min_digits . ' digits!');
        } else {
            return true;
        }
    }
}