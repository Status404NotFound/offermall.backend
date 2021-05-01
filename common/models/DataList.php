<?php /** @noinspection TypeUnsafeComparisonInspection */

namespace common\models;

use common\models\delivery\UserDeliveryApi;
use common\models\finance\Currency;
use common\models\flow\Flow;
use common\models\geo\Countries;
use common\models\geo\Geo;
use common\models\geo\GeoRegion;
use common\models\offer\targets\advert\TargetAdvert;
use common\modules\user\models\Permission;
use common\modules\user\models\tables\User;
use crm\models\delivery\DeliveryStickers;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class DataList
 * @package common\models
 */
class DataList extends Model
{

    public $owner;
    public $role;
    public $offers;

    /**
     * DataList constructor.
     */
    public function __construct()
    {
        $this->owner = Yii::$app->user->identity->getOwnerId();
        $this->role = Yii::$app->user->identity->role;
        $this->offers = $this->getOffers();
        parent::__construct();
    }

    /**
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getOffers()
    {
        $offers_query = TargetAdvert::find()
            ->select('offer.offer_id, offer.offer_name')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert.target_advert_group_id = target_advert_group.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = advert_offer_target.offer_id');

        if (!is_null($this->owner)) $offers_query->where(['target_advert.advert_id' => $this->owner]);

        $offers = $offers_query
            ->groupBy('offer.offer_id')
            ->asArray()
            ->all();

        return $offers;
    }

    /**
     * @param $geo_id
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getGeoOffers($geo_id)
    {
        $offers_query = TargetAdvert::find()
            ->select('offer.offer_id, offer.offer_name')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert.target_advert_group_id = target_advert_group.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'offer', 'offer.offer_id = advert_offer_target.offer_id');

        if (!is_null($this->owner)) $offers_query->where(['target_advert.advert_id' => $this->owner]);
        if (!empty($geo_id)) $offers_query->andWhere(['advert_offer_target.geo_id' => $geo_id]);

        $offers = $offers_query
            ->groupBy('offer.offer_id')
            ->asArray()
            ->all();

        return $offers;
    }

    /**
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getAllCountries()
    {
        $countries = Countries::find()->select('`id` as country_id, country_name')->asArray()->all();
        return $countries;
    }

    /**
     * @param $geo_id
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getCountryRegions($geo_id)
    {
        $regions = GeoRegion::find()
            ->where(['geo_id' => $geo_id])
            ->asArray()
            ->all();

        return $regions;
    }

    /**
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getAllGeo()
    {
        $geos = Geo::find()->select('geo_id, geo_name')->asArray()->all();

        return $geos;
    }

    /**
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getOffersGeo()
    {
        $query = TargetAdvert::find()
            ->select('advert_offer_target.geo_id as country_id, countries.country_name, countries.country_code as iso')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert.target_advert_group_id = target_advert_group.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'countries', 'advert_offer_target.geo_id = countries.id');

        if (!is_null($this->owner)) $query->where(['target_advert.advert_id' => $this->owner]);

        $countries = $query
            ->groupBy('advert_offer_target.geo_id')
            ->asArray()
            ->all();

        return $countries;
    }

    /**
     * @param $offer_id
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getOfferGeo($offer_id)
    {
        $query = TargetAdvert::find()
            ->select('advert_offer_target.geo_id as country_id, countries.country_name, countries.country_code as iso')
            ->join('LEFT JOIN', 'target_advert_group', 'target_advert.target_advert_group_id = target_advert_group.target_advert_group_id')
            ->join('LEFT JOIN', 'advert_offer_target', 'target_advert_group.advert_offer_target_id = advert_offer_target.advert_offer_target_id')
            ->join('LEFT JOIN', 'countries', 'advert_offer_target.geo_id = countries.id');

        if (!is_null($this->owner)) $query->where(['target_advert.advert_id' => $this->owner]);

        $query->andWhere(['advert_offer_target.offer_id' => $offer_id]);

        $countries = $query
            ->groupBy('advert_offer_target.geo_id')
            ->asArray()
            ->all();

        return $countries;
    }

    /**
     * @param null $role
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public function getUsers($role = null)
    {
        $users_query = User::find()
            ->select('`id` as user_id, username as user_name')
            ->join('LEFT JOIN', 'user_child', 'user_child.child = user.id');
    
        if ($role) {
            $users_query->where(['user.role' => $role]);
        }
        
        if ($this->owner) {
            $role == User::ROLE_ADVERTISER
                ? $users_query->andWhere(['user.id' => $this->owner])
                : $users_query->andWhere(['user_child.parent' => $this->owner]);
        }

        $users = $users_query
            ->groupBy('user.id')
            ->asArray()
            ->all();
    
        if ($this->role == User::ROLE_ADMIN) {
            $users[] = [
                'user_id' => 0,
                'user_name' => 'No Advert'
            ];
        }
        
        return $users;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        if ($this->owner == null) {
            return Yii::$app->user->identity->roles();
        }
        
        $permissions = (new Permission(
            Yii::$app->user->identity->role,
            Yii::$app->user->identity->id))
            ->permissions;
        
        if (empty($permissions)) return [];
        
        $create_staff_list = [];
        foreach ($permissions as $code => $role) {
            if (isset(Permission::$create_users[$code])) {
                $create_staff_list[] = Permission::$create_users[$code];
            }
        }
        
        return $create_staff_list;
    }

    /**
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public static function getCurrencyList()
    {
        return Currency::indexedList();
    }

    /**
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|\yii\db\ActiveRecord[]
     */
    public static function getCurrency()
    {
        return Currency::list();
    }

    /**
     * @param null $wm_id
     * @return array|Flow[]
     */
    public function getFlows($wm_id = null)
    {
        $flows = Flow::find()
            ->select(['flow_id', 'flow_name'])
            ->where(['active' => Flow::STATUS_ACTIVE]);

        if (!empty($wm_id)) $flows->andWhere(['wm_id' => $wm_id]);

        $query = $flows
            ->asArray()
            ->all();

        return $query;
    }

    /**
     * @return array
     */
    public function roles()
    {
        $roles_list = $this->getRoles();

        $roles = [];
        foreach ($roles_list as $role) {
            $roles[] = [
                'role_id' => (string)$role['role_id'],
                'role_name' => $role['role_name'],
            ];
        }

        return $roles;
    }

    /**
     * @return array
     */
    public function getUserList()
    {
        $query = User::find()
            ->select([
                "id as parent_id",
                "CONCAT(id,  ' - ', username) as parent",
                "role",
            ]);

        if ($this->owner) $query->where(['id' => $this->owner]);
        
        $data = $query
            ->andWhere(['blocked_at' => null])
            ->andWhere(['role' => [
                User::ROLE_ADMIN,
                User::ROLE_ADVERTISER,
                User::ROLE_SUPER_WM,
                User::ROLE_ADVERTISER_MANAGER,
                User::ROLE_CALLCENTER_MASTER,
                User::ROLE_CALLCENTER_MANAGER]])
            ->asArray()
            ->all();

        $result = [];
        foreach ($data as $key => $value) {
            $result[$key]['parent_id'] = $value['parent_id'];
            $result[$key]['parent_name'] = $value['parent'] . ' ( ' . User::rolesIndexed()[$value['role']] . ' ) ';
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getOrderStatusReasons()
    {
        $statuses = [
            ['reason_id' => 0, 'reason_name' => 'Rejected by Admin'],
            ['reason_id' => 1, 'reason_name' => 'Customer is out of country'],
            ['reason_id' => 2, 'reason_name' => 'Error in the phone number'],
            ['reason_id' => 3, 'reason_name' => 'I did not order'],
            ['reason_id' => 4, 'reason_name' => 'Duplicate order'],
            ['reason_id' => 5, 'reason_name' => 'Too expensive'],
            ['reason_id' => 6, 'reason_name' => 'Ordered elsewhere'],
            ['reason_id' => 7, 'reason_name' => 'Order to mistress but wife took the phone'],
            ['reason_id' => 8, 'reason_name' => 'A subscriber can not receive the call at the moment'],
            ['reason_id' => 9, 'reason_name' => 'Undefined language'],
            ['reason_id' => 10, 'reason_name' => 'Product is out of stock'],
            ['reason_id' => 11, 'reason_name' => 'Customer will order late'],
            ['reason_id' => 12, 'reason_name' => 'Not Supported Region'],
            ['reason_id' => 13, 'reason_name' => 'Test Conversion or Offer'],
            ['reason_id' => 14, 'reason_name' => 'Unhappy with delivery charge'],
            ['reason_id' => 15, 'reason_name' => 'Children\'s joke'],
            ['reason_id' => 16, 'reason_name' => 'Consultation'],
            ['reason_id' => 17, 'reason_name' => 'Customer is not interested anymore'],
            ['reason_id' => 18, 'reason_name' => 'Weird details'],
            ['reason_id' => 19, 'reason_name' => 'The subscriber is out of network coverage'],
            ['reason_id' => 20, 'reason_name' => 'Wrong Geo'],
            ['reason_id' => 21, 'reason_name' => 'No WM'],
            ['reason_id' => 22, 'reason_name' => 'Not correct details'],
            ['reason_id' => 23, 'reason_name' => 'NA for 4 days'],
            ['reason_id' => 24, 'reason_name' => 'Low quality'],
            ['reason_id' => 25, 'reason_name' => 'Out of money'],
            ['reason_id' => 26, 'reason_name' => 'Black list'],
        ];

        return $statuses;
    }

    /**
     * @return array
     */
    public function timeZone()
    {
        $timeZones = [];
        $timeZoneIdentifiers = \DateTimeZone::listIdentifiers();

        foreach ($timeZoneIdentifiers as $timeZone) {
            $date = new \DateTime('now', new \DateTimeZone($timeZone));
            $offset = $date->getOffset() / 60 / 60;
            $timeZones[] = [
                'timezone' => $timeZone,
                'name' => "{$timeZone} (UTC " . ($offset > 0 ? '+' : '') . "{$offset})",
            ];
        }

        ArrayHelper::multisort($timeZones, 'offset', SORT_DESC, SORT_NUMERIC);

        return $timeZones;
    }

    /**
     * @return array|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|DeliveryStickers[]|\yii\db\ActiveRecord[]
     */
    public function getUserOrderStickers()
    {
        return DeliveryStickers::find()
            ->select([
                'delivery_stickers.sticker_id',
                'delivery_stickers.sticker_name',
                'delivery_stickers.sticker_color'
            ])
            ->where(['is_active' => DeliveryStickers::IS_ACTIVE])
            ->andWhere(['delivery_stickers.owner_id' => Yii::$app->user->identity->getId()])
            ->asArray()
            ->all();
    }

    /**
     * @return array|UserDeliveryApi[]|Currency[]|Countries[]|Geo[]|GeoRegion[]|TargetAdvert[]|User[]|DeliveryStickers[]|\crm\models\delivery\OrderStickers[]|\yii\db\ActiveRecord[]
     */
    public function getDeliveryApi()
    {
        $api_list = DeliveryStickers::find()
            ->select([
                'sticker_id as api_id',
                'sticker_name as api_name',
            ])
            ->where(['is_active' => DeliveryStickers::IS_ACTIVE])
            ->andWhere([
                'owner_id' => Yii::$app->user->identity->getOwnerId(),
                'is_service' => 1
            ])
            ->orWhere([
                'owner_id' => Yii::$app->user->identity->getId(),
                'is_service' => 1
            ])
            ->asArray()
            ->all();

        return $api_list;
    }
}
