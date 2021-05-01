<?php

namespace common\modules\user;

use common\modules\user\models\tables\BaseProfile;
use common\modules\user\models\tables\Token;
use common\modules\user\models\tables\User;
use yii\base\BaseObject;
use yii\db\ActiveQuery;

/**
 * Finder provides some useful methods for finding active record models.
 *
 * @author makandy <makandy42@gmail.com>
 */
class UserFinder extends BaseObject
{
    /**
     * @property ActiveQuery $userQuery
     * @property ActiveQuery $tokenQuery
     * @property ActiveQuery $profileQuery
     */

    /** @var ActiveQuery */
    protected $userQuery;

    /** @var ActiveQuery */
    protected $tokenQuery;

    /** @var ActiveQuery */
    protected $profileQuery;

    /**
     * @return ActiveQuery
     */
    public function getUserQuery()
    {
        return $this->userQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getTokenQuery()
    {
        return $this->tokenQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getProfileQuery()
    {
        return $this->profileQuery;
    }

    /** @param ActiveQuery $userQuery */
    public function setUserQuery(ActiveQuery $userQuery)
    {
        $this->userQuery = $userQuery;
    }

    /** @param ActiveQuery $tokenQuery */
    public function setTokenQuery(ActiveQuery $tokenQuery)
    {
        $this->tokenQuery = $tokenQuery;
    }

    /** @param ActiveQuery $profileQuery */
    public function setProfileQuery(ActiveQuery $profileQuery)
    {
        $this->profileQuery = $profileQuery;
    }

    /**
     * Finds a user by the given id.
     *
     * @param int $id User id to be used on search.
     *
     * @return User
     */
    public function findUserById($id)
    {
        return $this->findUser(['id' => $id])->one();
    }

    /**
     * Finds a user by exists.
     *
     * @param string $username Username to be used on search.
     *
     * @return User
     */
    public function isUsernameExists($username)
    {
        return $this->findUser(['username' => $username])->exists();
    }

    /**
     * Finds a user by the given username.
     *
     * @param string $username Username to be used on search.
     *
     * @return User
     */
    public function findUserByUsername($username)
    {
        return $this->findUser(['username' => $username])->one();
    }

    /**
     * Finds a user by the given email.
     *
     * @param string $email Email to be used on search.
     *
     * @return User
     */
    public function findUserByEmail($email)
    {
        return $this->findUser(['email' => $email])->one();
    }

    /**
     * Finds a user by the given username or email.
     *
     * @param string $usernameOrEmail Username or email to be used on search.
     *
     * @return User
     */
    public function findUserByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * Finds a user by the given condition.
     *
     * @param mixed $condition Condition to be used on search.
     *
     * @return User|ActiveQuery
     */
    public function findUser($condition)
    {
        return $this->userQuery->where($condition);
    }

    /**
     * Finds a token by user id and code.
     *
     * @param mixed $condition
     *
     * @return Token|ActiveQuery
     */
    public function findToken($condition)
    {
        return $this->tokenQuery->where($condition);
    }

    /**
     * Finds a token by params.
     *
     * @param integer $userId
     * @param string $code
     * @param integer $type
     *
     * @return Token
     */
    public function findTokenByParams($userId, $code, $type)
    {
        return $this->findToken([
            'user_id' => $userId,
            'code' => $code,
            'type' => $type,
        ])->one();
    }

    /**
     * Finds a profile by user id.
     *
     * @param int $id
     *
     * @return null|BaseProfile
     */
    public function findProfileById($id)
    {
        return $this->findProfile(['user_id' => $id])->one();
    }

    /**
     * Finds a profile.
     *
     * @param mixed $condition
     *
     * @return BaseProfile|ActiveQuery
     */
    public function findProfile($condition)
    {
        return $this->profileQuery->where($condition);
    }
}
