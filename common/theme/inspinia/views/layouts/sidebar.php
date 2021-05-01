<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">

        <?php
        use common\theme\inspinia\widgets\Menu;
        use yii\helpers\Url;

        /**
         * @var common\modules\user\models\tables\User $user
         */
        $user = Yii::$app->user->identity;
        $menu =  \common\modules\user\helpers\MenuHelper::getAssignedMenu(Yii::$app->user->id);

        if ( !empty($user) ) {
            if (isset($user->profile->avatar_path)) {
                $avatar = $user->profile->avatar_path;
            } else {
                $avatar = $directoryAsset . '/img/default-user-image.png';
            }
            $profileMenu = [
                [
                    'profile' => [
                        'logo' => 'AF',
                        'avatar' => is_null($avatar)? $directoryAsset . '/img/default-user-image.png':$avatar,
                        'name' => empty($user->profile->name)?$user->username:$user->profile->name,
                        'role' => 'Test',
                        'items' => [
                            '<a href="'. Url::to(['/user/profile']) . '">Profile</a>',
                            '',
                            '<a href="'. Url::to(['/user/logout']) . '" data-method="post">Logout</a>'
                        ]
                    ],
                    'options' => ['class' => 'nav-header'],
                ]
            ];
            $menu = array_merge($profileMenu, $menu);
        }

        echo Menu::widget(
            [
                'options' => ['class' => 'nav metismenu', 'id'=>'side-menu'],
                'submenuTemplate' => "\n<ul class='nav nav-second-level collapse' {show}>\n{items}\n</ul>\n",
                'items' => $menu//$menu,
//                'items' => Yii::$app->getModule('user')->getMenus()//$menu,
            ]
        ) ?>
    </div>
</nav>