<?php
/**
 * Created by PhpStorm.
 * User: ihor
 * Date: 12.05.17
 * Time: 12:23
 */

namespace common\services\callcenter;
use Yii;
use yii\helpers\ArrayHelper;

class PhoneLineService
{
    public $user;
    public $user_parent;

    public function __construct()
    {
        $this->user = Yii::$app->user->identity;
        $this->user_parent = $this->user->getParent()->one();
    }

    public function getOwnerList(){

        $users = [];


        if ($this->user->getData() == 'role-admin') {

            $users = $this->user->getAllUser('role-advertiser');
            $users = ArrayHelper::map($users, 'id', 'username');

        }else{

            if ($this->user->getData() == 'role-advertiser'){

                $users[$this->user->id] = $this->user->username;

            }else{
                if ($this->isFirstLevelUser()) {
                    $users[$this->user->id] = $this->user->username;
                }
                else{
                    $users[$this->user_parent->id] = $this->user_parent->username;
                }
            }

        }
        //var_dump(ArrayHelper::map($users, 'id', 'username'));
        return $users;
    }

    public function isFirstLevelUser(){
        if ($this->user_parent == null) return true;
        else return false;
    }

    public function isFreeLine(){

    }
}