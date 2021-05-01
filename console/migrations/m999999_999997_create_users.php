<?php

use yii\db\Migration;
use common\modules\user\models\tables\User;
use common\models\finance\advert\AdvertMoney;

class m999999_999997_create_users extends Migration
{
    public function up()
    {
        /** CREATE admin User  **/
        shell_exec('php yii user/create admin@gmail.com admin 123123 ' . User::ROLE_ADMIN);
        shell_exec('php yii user/role/create Admin');
        shell_exec('php yii user/route');
        shell_exec('php yii user/role/assign Admin /user/*');
        shell_exec('php yii user/assignment/assign admin Admin');
        /** CREATE vetaska User  **/
        shell_exec('php yii user/create vetaska@gmail.com vetaska 123123 ' . User::ROLE_ADMIN);
        shell_exec('php yii user/route');
        shell_exec('php yii user/assignment/assign vetaska Admin');
        /** CREATE zulua User  **/
        shell_exec('php yii user/create zulua@gmail.com zulua 123123 ' . User::ROLE_ADMIN);
        shell_exec('php yii user/route');
        shell_exec('php yii user/assignment/assign zulua Admin');
        /** CREATE laring User  **/
        shell_exec('php yii user/create laring@gmail.com laring 123123 ' . User::ROLE_ADMIN);
        shell_exec('php yii user/route');
        shell_exec('php yii user/assignment/assign laring Admin');
        /** CREATE i_admin User  **/
        shell_exec('php yii user/create i_admin@gmail.com i_admin 123123 ' . User::ROLE_ADMIN);
        shell_exec('php yii user/route');
        shell_exec('php yii user/assignment/assign i_admin Admin');
        /** CREATE Somebody User  **/
        shell_exec('php yii user/create somebody@gmail.com Somebody 123123 ' . User::ROLE_ADVERTISER);
        shell_exec('php yii user/role/create Advert');
        shell_exec('php yii user/route');
        shell_exec('php yii user/role/assign advert /user/*');
        shell_exec('php yii user/assignment/assign Somebody Advert');
        /** CREATE wm Users  **/
        shell_exec('php yii user/create chewm@gmail.com ChePollyNo_wm 123123 ' . User::ROLE_WEBMASTER);
        shell_exec('php yii user/role/create Wm');
        shell_exec('php yii user/route');
        shell_exec('php yii user/role/assign Wm /user/*');
        shell_exec('php yii user/assignment/assign ChePollyNo_wm Wm');
        /** CREATE YA-WM User  **/
        shell_exec('php yii user/create yawm@gmail.com ya-wm 123123 ' . User::ROLE_WEBMASTER);
        shell_exec('php yii user/route');
        shell_exec('php yii user/assignment/assign ya-wm Wm');
        /** CREATE YA-WM User  **/
        shell_exec('php yii user/create luba@gmail.com luba-wm 123123 ' . User::ROLE_WEBMASTER);
        shell_exec('php yii user/route');
        shell_exec('php yii user/assignment/assign luba-wm Wm');

        $this->insertPermissions();
    }

    public function insertPermissions()
    {
        $query = file_get_contents(__DIR__ . '/sql/role_permissions.sql');
        Yii::$app->db->createCommand($query)->execute();
    }

    public function safeDown()
    {
        parent::safeDown();
    }

    private function oldUsersCreation()
    {
        //        for ($i = 0; $i < 5; $i++) {
//            shell_exec('php yii user/create advert' . $i . '@gmail.com advert' . $i . ' 123123 ' . User::ROLE_ADVERTISER);
//            shell_exec('php yii user/route');
//            shell_exec('php yii user/assignment/assign advert' . $i . ' Advert');
//        }
        //        /** CREATE advert User  **/
//        shell_exec('php yii user/create advert@gmail.com advert 123123 ' . User::ROLE_ADVERTISER);
//        shell_exec('php yii user/role/create Advert');
//        shell_exec('php yii user/route');
//        shell_exec('php yii user/role/assign advert /user/*');
//        shell_exec('php yii user/assignment/assign advert Advert');
        //        for ($i = 0; $i < 5; $i++) {
//            shell_exec('php yii user/create wm' . $i . '@gmail.com wm' . $i . ' 123123 ' . User::ROLE_ADVERTISER);
//            shell_exec('php yii user/route');
//            shell_exec('php yii user/assignment/assign wm' . $i . ' Wm');
//        }
//        /** CREATE operator1 User  **/
//        shell_exec('php yii user/create operator1@gmail.com operator1 123123 ' . User::ROLE_OPERATOR);
//        shell_exec('php yii user/role/create Operator');
//        shell_exec('php yii user/route');
//        shell_exec('php yii user/role/assign Operator /user/*');
//        shell_exec('php yii user/assignment/assign operator1 Operator');
//        /** CREATE operator2 User  **/
//        for ($i = 0; $i < 3; $i++) {
//            shell_exec('php yii user/create operator' . $i . '@gmail.com operator' . $i . ' 123123 ' . User::ROLE_OPERATOR);
//            shell_exec('php yii user/route');
//            shell_exec('php yii user/assignment/assign operator' . $i . ' Operator');
//        }
//        /** INSERT USER-DATA (role identificator) **/
//        Yii::$app->db->createCommand('UPDATE auth_item SET data = ' . '"s:10:\"role_admin\";"' . '
//WHERE NAME = "Admin"')->execute();
//        Yii::$app->db->createCommand('UPDATE auth_item SET data = ' . '"s:11:\"role_advert\";"' . '
//WHERE NAME = "Advert"')->execute();
//        Yii::$app->db->createCommand('UPDATE auth_item SET data = ' . '"s:14:\"role_webmaster\";"' . '
//WHERE NAME = "Wm"')->execute();
//        Yii::$app->db->createCommand('UPDATE auth_item SET data = ' . '"s:13:\"role_operator\";"' . '
//WHERE NAME = "Operator"')->execute();
//        /** Create AdvertMoney rows **/
//        $adverts = User::find()->where(['role' => User::ROLE_ADVERTISER])->all();
//        foreach ($adverts as $advert) {
//            /** @var User $advert * */
//            $advertMoney = new AdvertMoney();
//            $advertMoney->advert_id = $advert->id;
//            $advertMoney->currency_id = \common\models\finance\Currency::USD;
//            $advertMoney->money = 0;
//            $advertMoney->save();
//        }
    }
}
