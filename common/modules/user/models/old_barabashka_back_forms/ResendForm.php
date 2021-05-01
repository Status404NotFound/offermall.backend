<?php


namespace common\modules\user\models\forms;

use common\modules\user\UserFinder;
use common\modules\user\Mailer;
use common\modules\user\models\tables\Token;
use common\modules\user\models\tables\User;
use yii\base\Model;

/**
 * ResendForm gets user email address and if user with given email is registered it sends new confirmation message
 * to him in case he did not validate his email.
 *
 * @author makandy <makandy42@gmail.com>
 */
class ResendForm extends Model
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var UserFinder
     */
    protected $UserFinder;

    /**
     * @param Mailer $mailer
     * @param UserFinder $UserFinder
     * @param array  $config
     */
    public function __construct(Mailer $mailer, UserFinder $UserFinder, $config = [])
    {
        $this->mailer = $mailer;
        $this->UserFinder = $UserFinder;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'emailRequired' => ['email', 'required'],
            'emailPattern' => ['email', 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => \Yii::t('user', 'Email'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'resend-form';
    }

    /**
     * Creates new confirmation token and sends it to the user.
     *
     * @return bool
     */
    public function resend()
    {
        if (!$this->validate()) {
            return false;
        }

        $user = $this->UserFinder->findUserByEmail($this->email);

        if ($user instanceof User && !$user->isConfirmed) {
            /** @var Token $token */
            $token = \Yii::createObject([
                'class' => Token::className(),
                'user_id' => $user->id,
                'type' => Token::TYPE_CONFIRMATION,
            ]);
            $token->save(false);
            $this->mailer->sendConfirmationMessage($user, $token);
        }

        \Yii::$app->session->setFlash(
            'info',
            \Yii::t(
                'user',
                'A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.'
            )
        );

        return true;
    }
}
