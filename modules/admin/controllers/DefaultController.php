<?php

namespace app\modules\admin\controllers;

use Yii;
use app\components\SystemInfo;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\components\AccessRule;
use app\models\User;

class DefaultController extends Controller
{
    public $layout = 'main';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [
                            User::ROLE_ADMIN
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        if (Yii::$app->request->get('method') == 'sysinfo') {
            return json_encode([
                'stat' => SystemInfo::getStat(),
                'stime' => date('Y-m-d H:i:s'),
                'uptime' => SystemInfo::getUpTime(),
                'meminfo' => SystemInfo::getMemInfo(),
                'loadavg' => SystemInfo::getLoadAvg(),
                'diskinfo' => SystemInfo::getDiskInfo(),
                'netdev' => SystemInfo::getNetDev()
            ]);
        }
        return $this->render('index', [
            'time_start' => microtime(true),
            'stat' => SystemInfo::getStat(),
            'stime' => date('Y-m-d H:i:s'),
            'uptime' => SystemInfo::getUpTime(),
            'meminfo' => SystemInfo::getMemInfo(),
            'loadavg' => SystemInfo::getLoadAvg(),
            'diskinfo' => SystemInfo::getDiskInfo(),
            'netdev' => SystemInfo::getNetDev()
        ]);
    }
}
