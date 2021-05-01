<?php
use common\modules\user\migrations\Migration;

/**
 * @author makandy
 * <makandy42@gmail.com>
 */
class m000000_100000_user extends Migration {
    public function up()
    {
        /**
         * Table {{%user}}
         * */
        $this->createTable('{{%user}}', [
            'id'                   => $this->primaryKey(),                      //Идентификатор
            'username'             => $this->string(255)->notNull()->unique(),  //Логин
            'email'                => $this->string(255)->notNull()->unique(),  //Почта
            'password_hash'        => $this->string(60)->notNull(),             //Пароль
            'auth_key'             => $this->string(32)->notNull(),             //Ключ аутентификации
            'access_token'         => $this->string(1023)->null(),               //Доступ по токену
            'unconfirmed_email'    => $this->string(255)->null(),               //Неподтвержденная ел. почта
            'flags'                => $this->integer()->notNull()->defaultValue(0),
            'last_login_at'        => $this->timestamp()->null(),               //Дата последнего входа
            'blocked_at'           => $this->dateTime()->null(),                //Дата блокирования пользователя
            'created_at'           => $this->dateTime()->notNull(),             //Дата создания пользователя
//            'updated_at'           => $this->timestamp()->notNull(),            //Дата обновления данных пользователя
            'updated_at'           => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',            //Дата обновления данных пользователя
            'confirmed_at'         => $this->dateTime()->null(),                //
            'access_token_expired_at' => $this->string(),
            'registration_ip' => $this->string(),
            'role' => $this->integer(4)->notNull(),
        ], $this->tableOptions);
        $this->createIndex('{{%user_unique_username}}', '{{%user}}', 'username', true);
        $this->createIndex('{{%user_unique_email}}', '{{%user}}', 'email', true);


        /**
         * Table {{%user_child}}
         * */
        $this->createTable('{{%user_child}}', [
            'parent'               => $this->integer()->notNull(),
            'child'                => $this->integer()->notNull(),
            'PRIMARY KEY (parent, child)',
        ], $this->tableOptions);
        $this->addForeignKey('{{%fk_user_parent}}', '{{%user_child}}', 'parent', '{{%user}}', 'id', $this->cascade, $this->cascade);
        $this->addForeignKey('{{%fk_user_child}}', '{{%user_child}}', 'child', '{{%user}}', 'id', $this->cascade, $this->cascade);


        /**
         * Table {{%base_profile}}
         * */
        $this->createTable('{{%base_profile}}', [
            'user_id'               => $this->integer()->notNull()->append('PRIMARY KEY'),
            'name'                  => $this->string(255)->null(),              //Имя пользователя
            'phone_number'          => $this->bigInteger(13)->null(),           //Номер телефона пользователя
            'location'              => $this->string(255)->null(),              //Место нахождения
            'avatar'                => 'LONGBLOB',                                     //Аватарка
            'timezone'              => $this->string(40)->null(),               //Часовой пояс пользователя
            'notification_audio'    => 'LONGBLOB',                                     //Аудио напоминание о новых ордерах
            'notification_audio_name'    => $this->string(255)->null()          //Название аудио напоминания о новых ордерах
        ], $this->tableOptions);
        $this->addForeignKey('{{%fk_user_base_profile}}', '{{%base_profile}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);


        /**
         * Table {{%token}}
         * */
        $this->createTable('{{%token}}', [
            'user_id'    => $this->integer()->notNull(),
            'code'       => $this->string(32)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'type'       => $this->smallInteger()->notNull(),
        ], $this->tableOptions);
        $this->createIndex('{{%token_unique}}', '{{%token}}', ['user_id', 'code', 'type'], true);
        $this->addForeignKey('{{%fk_user_token}}', '{{%token}}', 'user_id', '{{%user}}', 'id', $this->cascade, $this->restrict);


        /**
         * Table {{%menu}}
         * */
        $this->createTable('{{%menu}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'parent' => $this->integer(),
            'route' => $this->string(),
            'order' => $this->integer(),
            'data' => $this->binary(),
        ], $this->tableOptions);
        $this->addForeignKey('{{%fk_menu_menu}}', '{{%menu}}', 'parent', '{{%menu}}', 'id', $this->set_null, $this->cascade);
        $this->addData();
    }

    public function down() {
        $this->dropTable('{{%menu}}');
        $this->dropTable('{{%token}}');
        $this->dropTable('{{%base_profile}}');
        $this->dropTable('{{%user_child}}');
        $this->dropTable('{{%user}}');
    }

    public function addData() {
        /**
         * Added menu
         */
        $this->insert('{{%menu}}',[ 'name'=>'Admin' ]);
        $this->insert('{{%menu}}',[ 'name'=>'Users',        'parent' =>1, 'route' =>'/user/admin/index',        'order' =>0 ]);
        $this->insert('{{%menu}}',[ 'name'=>'RBAC',         'parent' =>1,                                       'order' =>1 ]);
        $this->insert('{{%menu}}',[ 'name'=>'Assignments',  'parent' =>3, 'route' =>'/user/assignment/index',   'order' =>0 ]);
        $this->insert('{{%menu}}',[ 'name'=>'Roles',        'parent' =>3, 'route' =>'/user/role/index',         'order' =>1 ]);
        $this->insert('{{%menu}}',[ 'name'=>'Permision',    'parent' =>3, 'route' =>'/user/permission/index',   'order' =>2 ]);
        $this->insert('{{%menu}}',[ 'name'=>'Rules',        'parent' =>3, 'route' =>'/user/rule/index',         'order' =>3 ]);
        $this->insert('{{%menu}}',[ 'name'=>'Route',        'parent' =>3, 'route' =>'/user/route/index',        'order' =>4 ]);
        $this->insert('{{%menu}}',[ 'name'=>'Menu',         'parent' =>3, 'route' =>'/user/menu/index',         'order' =>5 ]);

    }
}