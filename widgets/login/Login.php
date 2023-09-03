<?php
/**
 * @link http://www.iisns.com/
 * @copyright Copyright (c) 2015 iiSNS
 * @license http://www.iisns.com/license/
 */

namespace app\widgets\login;

use app\models\LoginForm;

class Login extends \yii\base\Widget
{
    public $visible = true;

    public function run()
    {
        if($this->visible) {
            $user = new LoginForm;
            if ($user->load(\Yii::$app->request->post()) && $user->login()) {
                \Yii::$app->getResponse()->refresh()->send();
                exit;
            } else {
                return $this->render('loginWidget', [
                    'user' => $user,
                ]);
            }
        }
    }
}
