<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $verifyCode;
    public $studentNumber;

    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['studentNumber', 'integer'],
            ['username', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This username has already been taken.'],
            ['username', 'string', 'max' => 16, 'min' => 4],
            ['username', 'match', 'pattern' => '/^(?!_)(?!.*?_$)(?!\d{4,16}$)[a-z\d_]{4,16}$/i', 'message' => 'Username can only contain numbers, letters, underscores, and not pure numbers, and the length is between 4 and 16 characters'],
            ['username', 'match', 'pattern' => '/^(?!c[\d]+user[\d])/', 'message' => 'Use c+number+user+number as the account name reserved by the system'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\app\models\User', 'message' => 'This email address has already been taken.'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6, 'max' => 16],
            ['verifyCode', 'captcha']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username'),
            'password' => Yii::t('app', 'Password'),
            'email' => Yii::t('app', 'Email'),
            'verifyCode' => Yii::t('app', 'Verify Code'),
            'studentNumber' => Yii::t('app', 'Student Number')
        ];
    }

    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        $user = new User();
        $user->username = $this->username;
        $user->nickname = $this->username;
        $user->email = $this->email;
        $user->is_verify_email = User::VERIFY_EMAIL_NO;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        if (Yii::$app->setting->get('mustVerifyEmail')) {
            $user->generateEmailVerificationToken();
            if (!$this->sendEmail($user)) {
                Yii::$app->session->setFlash('error',
                    'Failed to send verification email.');
                return null;
            }
            $user->status = User::STATUS_INACTIVE;
        } else {
            $user->status = User::STATUS_ACTIVE;
        }
        if (!$user->save()) {
            return null;
        }
        Yii::$app->db->createCommand()->insert('{{%user_profile}}', [
            'user_id' => $user->id,
            'student_number' => $this->studentNumber
        ])->execute();
        return $user;
    }

    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->setting->get('emailUsername') => Yii::$app->setting->get('ojName')])
            ->setTo($this->email)
            ->setSubject('Account Registration - ' . Yii::$app->setting->get('ojName'))
            ->send();
    }
}
