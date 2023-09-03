<?php
namespace app\components;

use Yii;
use yii\web\Controller;

class BaseController extends Controller
{

    public function init()
    {
        parent::init();
        $this->setLanguage();
    }

    public function setLanguage()
    {
        if(isset($_GET['lang']) && $_GET['lang'] != "") {

            Yii::$app->language = htmlspecialchars($_GET['lang']);

            $cookies = Yii::$app->response->cookies;

            $cookies->add(new \yii\web\Cookie([
                'name' => 'lang',
                'value' => htmlspecialchars($_GET['lang']),
                'expire' => time() + (365 * 24 * 60 * 60),
            ]));
        } elseif (isset(Yii::$app->request->cookies['lang']) &&
            Yii::$app->request->cookies['lang']->value != "") {
            
            Yii::$app->language = Yii::$app->request->cookies['lang']->value;
        } elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']);
            Yii::$app->language = $lang[0];
        } else {
            Yii::$app->language = 'vi';
        }
    }
}