<?php

namespace common\modules\user\models;

use common\modules\user\models\tables\User;
use yii\helpers\ArrayHelper;

/**
 * Class Permission
 * @package common\modules\user\models
 */
class Permission
{
    public $permissions;
    public $role_id;
    public $user_id;

    const viewDashboard = 1;

    const viewOrder = 2;
    const deleteOrder = 21;
    const editOrder = 22;
    const viewExtendedOrderLeadInfo = 23;
    const viewShortOrderLeadInfo = 28;
    const viewOrderSubIds = 24;
    const editOrderStatus = 25;
    const viewOrderCostRules = 26;
    const viewOrderOwner = 27;
    const changeOrderStatusOnNotValidChecked = 29;

    const viewOffer = 3;
    const createOffer = 31;
    const viewOfferGeo = 32;
    const viewOfferAdverts = 33;
    const viewOfferOrdersLog = 34;
    const doOfferOrder = 35;
    const editOffer = 36;
    const editOfferSettings = 37;
    const deleteOffer = 38;
    const viewOfferStatistics = 39;
    const viewOfferAllStatistics = 30;
    const viewOfferDailyStatistics = 311;
    const viewOfferTodayStatistics = 312;
    const viewOfferAdvertStatistics = 313;
    const viewOfferWmStatistics = 314;
    const useAdvertFilter = 315;
    const viewOfferOwner = 316;
    const viewHourlyStatistics = 317;
    const useTargetFilter = 318;
    const viewExtendedHourlyStatistics = 319;
    const viewZeroOffers = 321;
    const viewZeroGeo = 322;
    const viewOfferInfo = 323;
    const viewRejectOperatorsTodayStatistics = 325;
    const viewDeliverySkuStatistics = 327;
    const viewAdvertLive = 328;
    const viewSuperWMLive = 329;
    const viewOfferLive = 330;
    const viewGeoLive = 331;

    const viewApprovedRateColumn = 320;
    const viewStatisticUpSaleRateColumn = 324;
    const viewAverageBillCostColumn = 326;

    const viewDelivery = 4;
    const view1cSuccessDelivery = 40;
    const viewTaxInvoice = 400;
    const viewWFD = 41;
    const viewDIP = 42;
    const viewDeliveryGroupSearchOrders = 43;
    const viewEnableClosedPeriod = 404;
    const viewBlockedFinancialPeriod = 405;
    const doExport = 44;
    const printDeliveryDeclaration = 45;
    const deliverOrders = 46;
    const editWfdOrders = 47;
    const addDeliveryComment = 48;
    const changeDelivery = 49;
    const viewDeliveryStickers = 406;
    const viewDeliveryFilter = 407;

    public const fulfillmentUAE = 180;
    public const fulfillmentKSA = 181;
    public const fulfillmentBahrain = 182;
    public const fulfillmentEgypt = 183;
    public const fulfillmentJordan = 184;
    public const dropshipUAE = 185;
    public const maraExpress = 186;
    public const courierPlus = 187;
    public const shipaDelivery = 188;
    public const uginFetchrDropshipUAE = 189;

    const viewStocks = 5;
    const viewProducts = 51;
    const moveProductsToStock = 52;
    const addProductsToStock = 53;
    const deleteStock = 54;
    const createStock = 55;
    const viewStockFlow = 56;
    const createProduct = 57;
    const updateProduct = 58;

    const viewFinance = 6;
    const useFinanceAdvertFilter = 61;
    const viewFinanceChecks = 62;
    const viewFinanceFunds = 63;
    const viewFinanceTotalBalanceByMonth = 64;
    const viewCurrency = 65;
    const changeUserBalance = 66;
    const viewTurboSmsBalance = 67;

    const viewFinStripCalendar = 71;
    const viewFinStripOffer = 72;
    const viewFinStripSummary = 73;

    const viewWebmasterRequests = 8;
    const viewWebmasterCheckouts = 82;
    const viewFlowUserColumn = 83;
    const viewWebmasterDropdownList = 84;

    const viewCallCenterHistory = 91;
    const playCallCenterRecord = 92;
    const viewCallCenterStatistics = 93;
    const viewCallCenterSettings = 94;
    const viewCallCenterCallList = 95;
    const updateCallCenterOrderPriority = 96;
    const viewCallCenterFines = 97;
    const viewCallCenterPieces = 98;
    const updateCallCenterSetting = 99;
    const updateCallCenterFineStatus = 911;
    const viewCallCenterActivity = 912;
    const viewCallCenterCabinetOrderCardNotValidButton = 913;
    const viewCallCenterScript = 914;
    const viewCallCenterQueues = 915;

    const viewUser = 10;
    const createUser = 11;
    const editUser = 12;
    const editUserPermissions = 13;
    const editParent = 14;
    const deleteUser = 15;
    const blockUser = 16;

    //public const createAdmin = 159;
    public const createAdvertiser = 160;
    public const createAdvertiserManager = 161;
    public const createWebmaster = 162;
    public const createSuperWm = 163;
    public const createManager = 164;
    public const createFinManager = 165;
    public const createCallCenterMaster = 166;
    public const createCallCenterManager = 167;
    public const createOperator = 168;
    public const createStatist = 169;
    public const createVisor = 170;
    public const createSmm = 171;

    const viewRolesList = 17;
    const editRolesPermissions = 18;

    const viewExtendedStatistics = 111;
    const useWebmasterFilter = 112;
    const viewWebmasterCommissionColumn = 113;
    const viewWrongGeo = 114;
    const viewGeoStatistics = 115;
    const viewAdvertCommissionColumn = 116;
    const viewExtendedHistoryInfo = 117;
    const changeOrderStatusWithoutCheck = 118;
    const viewRejectStatistics = 119;

    const viewBlockDeliveryDates = 900;
    const viewBlockSku = 901;

    const viewJournalCrash = 991;
    const viewStartupLog = 992;
    const viewFormLog = 993;
    const viewNotificationButton = 994;
    const createUserCurrency = 995;
    const viewAddons = 996;

    const deliveryStatusButton = 997;

    const viewContactSearch = 998;
    const viewBlackList = 999;

    const view_1C_button = 888;

    const ORDERS_PAGE_EXPORT = 700;
    const FINSTRIP_EXPORT_BUTTON = 710;

    const IndiaBlueDart = 401;
    const DropshipAE = 402;
    const Fulfillment = 403;
    const viewSkuListAdvanced = 408;

    public static $create_users = [
        //self::createAdmin             => ['role_id' => User::ROLE_ADMIN, 'role_name' => 'Admin'],
        self::createAdvertiser        => ['role_id' => User::ROLE_ADVERTISER, 'role_name' => 'Advertiser'],
        self::createAdvertiserManager => ['role_id' => User::ROLE_ADVERTISER_MANAGER, 'role_name' => 'Advertiser Manager'],
        self::createWebmaster         => ['role_id' => User::ROLE_WEBMASTER, 'role_name' => 'Webmaster'],
        self::createSuperWm           => ['role_id' => User::ROLE_SUPER_WM, 'role_name' => 'Super Webmaster'],
        self::createManager           => ['role_id' => User::ROLE_MANAGER, 'role_name' => 'Manager'],
        self::createFinManager        => ['role_id' => User::ROLE_FIN_MANAGER, 'role_name' => 'Finance Manager'],
        self::createCallCenterMaster  => ['role_id' => User::ROLE_CALLCENTER_MASTER, 'role_name' => 'Call Center Master'],
        self::createCallCenterManager => ['role_id' => User::ROLE_CALLCENTER_MANAGER, 'role_name' => 'Call Center Manager'],
        self::createOperator          => ['role_id' => User::ROLE_OPERATOR, 'role_name' => 'Operator'],
        self::createStatist           => ['role_id' => User::ROLE_STATIST, 'role_name' => 'Statist'],
        self::createVisor             => ['role_id' => User::ROLE_VISOR, 'role_name' => 'Visor'],
        self::createSmm               => ['role_id' => User::ROLE_SMM, 'role_name' => 'Smm'],
    ];

    public static $available_apis = [
        self::fulfillmentUAE,
        self::fulfillmentKSA,
        self::fulfillmentBahrain,
        self::fulfillmentEgypt,
        self::fulfillmentJordan,
        self::dropshipUAE,
        self::maraExpress,
        self::courierPlus,
        self::shipaDelivery,
        self::uginFetchrDropshipUAE,
    ];

    /**
     * Permission constructor.
     * @param $role_id
     * @param null $user_id
     */
    public function __construct($role_id, $user_id = null)
    {
        $this->role_id = $role_id;
        $this->user_id = $user_id;


        if ($this->user_id != null) {
            $role_permissions = ArrayHelper::map(RolePermission::find()->where(['role_id' => $this->role_id])->asArray()->all(), 'permission_id', 'role_id');
            $user_permissions = ArrayHelper::map(UserPermission::find()->where(['user_id' => $this->user_id])->asArray()->all(), 'permission_id', 'user_id') ?? [];
            $this->permissions = $role_permissions + $user_permissions;
        } else $this->permissions = ArrayHelper::map(RolePermission::find()->where(['role_id' => $this->role_id])->asArray()->all(), 'permission_id', 'role_id');

    }

    /**
     * @return array
     */
    public function getPermissionsSeparateByEntityList()
    {
        return [
            'Dashboard' => [
                self::viewDashboard => 'viewDashboard'
            ],

            'Order' => [
                self::viewOrder => 'viewOrder',
                self::deleteOrder => 'deleteOrder',
                self::editOrder => 'editOrder',
                self::viewExtendedOrderLeadInfo => 'viewExtendedOrderLeadInfo',
                self::viewShortOrderLeadInfo => 'viewShortOrderLeadInfo',
                self::viewOrderSubIds => 'viewOrderSubIds',
                self::editOrderStatus => 'editOrderStatus',
                self::viewOrderCostRules => 'viewOrderCostRules',
                self::viewOrderOwner => 'viewOrderOwner',
                self::ORDERS_PAGE_EXPORT => 'ORDERS_PAGE_EXPORT',
                self::viewWrongGeo => 'viewWrongGeo',
                self::viewWebmasterCommissionColumn => 'viewWebmasterCommissionColumn',
                self::viewAdvertCommissionColumn => 'viewAdvertCommissionColumn',
                self::viewExtendedHistoryInfo => 'viewExtendedHistoryInfo',
                self::changeOrderStatusWithoutCheck => 'changeOrderStatusWithoutCheck',
                self::changeOrderStatusOnNotValidChecked => 'changeOrderStatusOnNotValidChecked',
            ],

            'Offer' => [
                self::viewOffer => 'viewOffer',
                self::createOffer => 'createOffer',
                self::viewOfferGeo => 'viewOfferGeo',
                self::viewOfferAdverts => 'viewOfferAdverts',
                self::viewOfferOrdersLog => 'viewOfferOrdersLog',
                self::doOfferOrder => 'doOfferOrder',
                self::editOffer => 'editOffer',
                self::editOfferSettings => 'editOfferSettings',
                self::deleteOffer => 'deleteOffer',
                self::useWebmasterFilter => 'useWebmasterFilter',
                self::useAdvertFilter => 'useAdvertFilter',
                self::useTargetFilter => 'useTargetFilter',
                self::viewOfferOwner => 'viewOfferOwner',
                self::viewOfferInfo => 'viewOfferInfo',
            ],

            'Statistics' => [
                self::viewOfferStatistics => 'viewOfferStatistics',
                self::viewOfferAllStatistics => 'viewOfferAllStatistics',
                self::viewOfferDailyStatistics => 'viewOfferDailyStatistics',
                self::viewOfferTodayStatistics => 'viewOfferTodayStatistics',
                self::viewOfferAdvertStatistics => 'viewOfferAdvertStatistics',
                self::viewGeoStatistics => 'viewGeoStatistics',
                self::viewOfferWmStatistics => 'viewOfferWmStatistics',
                self::viewHourlyStatistics => 'viewHourlyStatistics',
                self::viewExtendedHourlyStatistics => 'viewExtendedHourlyStatistics',
                self::viewCallCenterStatistics => 'viewCallCenterStatistics',
                self::viewRejectStatistics => 'viewRejectStatistics',
                self::viewExtendedStatistics => 'viewExtendedStatistics',
                self::viewZeroOffers => 'viewZeroOffers',
                self::viewZeroGeo => 'viewZeroGeo',
                self::viewRejectOperatorsTodayStatistics => 'viewRejectOperatorsTodayStatistics',
                self::viewDeliverySkuStatistics => 'viewDeliverySkuStatistics',
                self::viewGeoLive => 'viewGeoLive',
                self::viewAdvertLive => 'viewAdvertLive',
                self::viewOfferLive => 'viewOfferLive',
                self::viewSuperWMLive => 'viewSuperWMLive',



                // TODO DELETE THIS 3 const

                self::viewApprovedRateColumn => 'viewApprovedRateColumn',
                self::viewStatisticUpSaleRateColumn => 'viewStatisticUpSaleRateColumn',
                self::viewAverageBillCostColumn => 'viewAverageBillCostColumn',
            ],

            'Delivery' => [
                self::viewDelivery => 'viewDelivery',
                self::viewWFD => 'viewWFD',
                self::viewDIP => 'viewDIP',
                self::viewDeliveryGroupSearchOrders => 'viewDeliveryGroupSearchOrders',
                self::viewEnableClosedPeriod => 'viewEnableClosedPeriod',
                self::doExport => 'doExport',
                self::printDeliveryDeclaration => 'printDeliveryDeclaration',
                self::viewTaxInvoice => 'viewTaxInvoice',
                self::deliverOrders => 'deliverOrders',
                self::viewBlockDeliveryDates => 'viewBlockDeliveryDates',
                self::viewBlockSku => 'viewBlockSku',
                self::editWfdOrders => 'editWfdOrders',
                self::viewDeliveryStickers => 'viewDeliveryStickers',
                self::view_1C_button => 'View_1C_button',
                self::deliveryStatusButton => 'deliveryStatusButton',
                self::viewDeliveryFilter => 'viewDeliveryFilter',
                self::view1cSuccessDelivery => 'view1cSuccessDelivery',
            ],

            'Delivery API' => [
                self::fulfillmentUAE => 'fulfillmentUAE',
                self::fulfillmentKSA => 'fulfillmentKSA',
                self::fulfillmentBahrain => 'fulfillmentBahrain',
                self::fulfillmentEgypt => 'fulfillmentEgypt',
                self::fulfillmentJordan => 'fulfillmentJordan',
                self::dropshipUAE => 'dropshipUAE',
                self::maraExpress => 'maraExpress',
                self::courierPlus => 'courierPlus',
                self::shipaDelivery => 'shipaDelivery',
                self::uginFetchrDropshipUAE => 'uginFetchrDropshipUAE',
            ],

            'Warehouse' => [
                self::viewStocks => 'viewStocks',
                self::viewProducts => 'viewProducts',
                self::moveProductsToStock => 'moveProductsToStock',
                self::addProductsToStock => 'addProductsToStock',
                self::deleteStock => 'deleteStock',
                self::createStock => 'createStock',
                self::viewStockFlow => 'viewStockFlow',
                self::createProduct => 'createProduct',
                self::updateProduct => 'updateProduct',
            ],

            'CallCenter' => [
                self::viewCallCenterHistory => 'viewCallCenterHistory',
                self::playCallCenterRecord => 'playCallCenterRecord',
                self::viewCallCenterSettings => 'viewCallCenterSettings',
                self::viewCallCenterCallList => 'viewCallCenterCallList',
                self::updateCallCenterOrderPriority => 'updateCallCenterOrderPriority',
                self::viewCallCenterFines => 'viewCallCenterFines',
                self::viewCallCenterPieces => 'viewCallCenterPieces',
                self::updateCallCenterSetting => 'updateCallCenterSetting',
                self::updateCallCenterFineStatus => 'updateCallCenterFineStatus',
                self::viewCallCenterActivity => 'viewCallCenterActivity',
                self::viewCallCenterScript => 'viewCallCenterScript',
                self::viewCallCenterQueues => 'viewCallCenterQueues',
            ],

            'CallCenterCabinet' => [
                self::viewCallCenterCabinetOrderCardNotValidButton => 'viewCallCenterCabinetOrderCardNotValidButton',
            ],

            'Finance' => [
                self::viewFinance => 'viewFinance',
                self::useFinanceAdvertFilter => 'useFinanceAdvertFilter',
                self::viewFinanceChecks => 'viewFinanceChecks',
                self::viewFinanceFunds => 'viewFinanceFunds',
                self::viewFinanceTotalBalanceByMonth => 'viewFinanceTotalBalanceByMonth',
                self::viewCurrency => 'viewCurrency',
                self::changeUserBalance => 'changeUserBalance',
                self::viewTurboSmsBalance => 'viewTurboSmsBalance',
            ],

            'Finstrip' => [
                self::viewFinStripCalendar => 'viewFinStripCalendar',
                self::viewFinStripOffer => 'viewFinStripOffer',
                self::viewFinStripSummary => 'viewFinStripSummary',
                self::viewBlockedFinancialPeriod => 'viewBlockedFinancialPeriod',
                self::FINSTRIP_EXPORT_BUTTON => 'FINSTRIP_EXPORT_BUTTON',
            ],

            'Webmaster' => [
                self::viewWebmasterRequests => 'viewWebmasterRequests',
                self::viewWebmasterCheckouts => 'viewWebmasterCheckouts',
            ],

            'WebmasterCabinet' => [
                self::viewFlowUserColumn => 'viewFlowUserColumn',
                self::viewWebmasterDropdownList => 'viewWebmasterDropdownList',
            ],

            'User' => [
                self::viewUser => 'viewUser',
                self::createUser => 'createUser',
                self::editUser => 'editUser',
                self::editUserPermissions => 'editUserPermission',
                self::editParent => 'editParent',
                self::deleteUser => 'deleteUser',
                self::blockUser => 'blockUser',
            ],

            'CreateUser' => [
                self::createAdvertiser => 'createAdvertiser',
                self::createAdvertiserManager => 'createAdvertiserManager',
                self::createWebmaster => 'createWebmaster',
                self::createSuperWm => 'createSuperWM',
                self::createManager => 'createManager',
                self::createFinManager => 'createFinManager',
                self::createCallCenterMaster => 'createCallCenterMaster',
                self::createCallCenterManager => 'createCallCenterManager',
                self::createOperator => 'createOperator',
                self::createStatist => 'createStatist',
                self::createVisor => 'createVisor',
                self::createSmm => 'createSMM',
            ],

            'Roles' => [
                self::viewRolesList => 'viewRolesList',
                self::editRolesPermissions => 'editRolesPermissions',
            ],

            'Export' => [
                self::IndiaBlueDart => 'IndiaBlueDart',
                self::DropshipAE => 'DropshipAE',
                self::Fulfillment => 'Fulfillment',
                self::viewSkuListAdvanced => 'viewSkuListAdvanced',
            ],

            'Other' => [
                self::viewBlackList => 'viewBlackList',
                self::viewJournalCrash => 'viewJournalCrash',
                self::viewStartupLog => 'viewStartupLog',
                self::viewFormLog => 'viewFormLog',
                self::viewContactSearch => 'viewContactSearch',
                self::viewNotificationButton => 'viewNotificationButton',
                self::createUserCurrency => 'createUserCurrency',
                self::viewAddons => 'viewAddons',
            ]
        ];
    }

    /**
     * @return array
     */
    public function generatePermissionsList()
    {
        $list = $this->getPermissionsSeparateByEntityList();

        $result = [];
        foreach ($list as $key => $val) {
            foreach ($val as $k => $item) {
                $result[$k] = $item;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getPermissionStringList()
    {
        $permissions = [];
        foreach ($this->permissions as $permission_id => $string) {
            $permissions[] = $this->generatePermissionsList()[$permission_id];
        }


        return $permissions;
    }

    /**
     * @return array
     */
    public function generatePermissionsListIndexed()
    {
        $list = $this->getPermissionsSeparateByEntityList();

        $result = [];
        foreach ($list as $key => $value) {
            foreach ($value as $val => $item) {
                $result[$key][] = [
                    'permission_id' => $val,
                    'permission_name' => $item,
                ];
            }
        }

        return $result;
    }

    /**
     * @param null $role_id
     * @return array
     */
    public function getListOfPermissions($role_id = null)
    {
        $result = [];
        $list = $this->generatePermissionsListIndexed();
        foreach ($list as $entity_name => $entities) {
            foreach ($entities as $entity) {
                if (!isset($this->permissions[$entity['permission_id']])) {
                    $result[$entity_name][] = [
                        'permission_id' => $entity['permission_id'],
                        'permission_name' => $entity['permission_name'],
                        'permission_allowed' => false,
                    ];
                } else {
                    $result[$entity_name][] = [
                        'permission_id' => $entity['permission_id'],
                        'permission_name' => $entity['permission_name'],
                        'permission_allowed' => true,
                    ];
                }
            }
        }

        $result['username'] = ArrayHelper::getValue(User::find()->select('username')->where(['id' => $this->user_id])->asArray()->one(), 'username');

        if (!empty($role_id)) $result['role_name'] = User::rolesIndexed()[$role_id];

        return $result;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['role_id', 'user_id', 'permission'], 'integer'],
        ];
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveRolePermissions()
    {
        $old_permissions = RolePermission::find()->where(['role_id' => $this->role_id])->asArray()->all();
        $old_permissions_indexed = ArrayHelper::map($old_permissions, 'permission_id', 'role_id');

        foreach ($this->permissions as $permission) {
            if (isset($old_permissions_indexed[$permission])) unset($old_permissions_indexed[$permission]);
            else {
                $role_permission = new RolePermission();
                $role_permission->role_id = $this->role_id;
                $role_permission->permission_id = $permission;
                $role_permission->is_active = true;
                if ($role_permission->save()) continue;
                else var_dump($role_permission->errors);
                exit;
            }
        }

        foreach ($old_permissions_indexed as $permission_id => $role_id) {
            $role_del = RolePermission::find()->where(['permission_id' => $permission_id, 'role_id' => $role_id])->one();
            $role_del->delete();
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveUserPermissions()
    {
        $old_permissions = UserPermission::find()->where(['user_id' => $this->user_id])->asArray()->all();
        $old_permissions_indexed = ArrayHelper::map($old_permissions, 'permission_id', 'user_id');
        $userPermissions = $this->excludeRolePermissions();
        foreach ($userPermissions as $permission) {
            if (isset($old_permissions_indexed[$permission])) unset($old_permissions_indexed[$permission]);
            else {
                $role_permission = new UserPermission();
                $role_permission->user_id = $this->user_id;
                $role_permission->permission_id = $permission;
                $role_permission->is_active = true;
                if ($role_permission->save()) continue;
                else var_dump($role_permission->errors);
                exit;
            }
        }

        foreach ($old_permissions_indexed as $permission_id => $user_id) {
            $role_del = UserPermission::find()->where(['permission_id' => $permission_id, 'user_id' => $user_id])->one();
            $role_del->delete();
        }
    }

    /**
     * @return array
     */
    private function excludeRolePermissions()
    {
        $role_permissions = ArrayHelper::map(RolePermission::find()->where(['role_id' => $this->role_id])->asArray()->all(), 'permission_id', 'role_id');
        return array_diff($this->permissions, array_keys($role_permissions));
    }

    /**
     * @param $entity
     * @return array
     */
    public function getSelectedPermissionsByEntity($entity)
    {
        $all_permissions_by_entity = $this->getPermissionsSeparateByEntityList()[$entity];

        foreach ($all_permissions_by_entity as $permission_id => $permission_name) {
            if (!isset($this->permissions[$permission_id])) unset($all_permissions_by_entity[$permission_id]);
        }

        return array_keys($all_permissions_by_entity);
    }
}
