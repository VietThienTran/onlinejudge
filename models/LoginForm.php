<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\captcha\Captcha;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;
    public $verifyCode;

    private $_user = false;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
            ['verifyCode', 'captcha', 'on' => 'withCaptcha']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'Username Or Email'),
            'password' => Yii::t('app', 'Password'),
            'rememberMe' => Yii::t('app', 'Remember Me'),
            'verifyCode' => Yii::t('app', 'Verify Code')
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addError('username', Yii::t('app', 'Username does not exist.'));
            } elseif (!$user->validatePassword($this->password)) {
                $this->addError('password', Yii::t('app', 'Incorrect password.'));
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByLoginID($this->username);
        }

        return $this->_user;
    }
}
