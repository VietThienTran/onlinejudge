<?php

namespace app\controllers;

use app\components\BaseController;
use app\models\Contest;
use app\models\UserProfile;
use Yii;
use app\models\User;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

class UserController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['setting', 'verify-email'],
                'rules' => [
                    [
                        'actions' => ['setting', 'verify-email'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        $contests = Yii::$app->db->createCommand('
                SELECT `cu`.`rating_change`, `cu`.`rank`, `cu`.`contest_id`, `c`.`start_time`, `c`.title
                FROM `contest_user` `cu`
                LEFT JOIN `contest` `c` ON `c`.`id`=`cu`.`contest_id`
                WHERE `cu`.`user_id`=:uid AND `cu`.`rank` IS NOT NULL ORDER BY `c`.`id`
            ', [':uid' => $model->id])->queryAll();

        $totalScore = Contest::RATING_INIT_SCORE;

        foreach ($contests as &$contest) {
            $totalScore += $contest['rating_change'];
            $contest['total'] = $totalScore;
            $contest['url'] = Url::toRoute(['/contest/view', 'id' => $contest['contest_id']]);
            $contest['level'] = $model->getRatingLevel($totalScore);
            $contest['start_time'] = strtotime($contest['start_time']);
        }

        return $this->render('view', [
            'model' => $model,
            'contests' => Json::encode($contests),
            'contestCnt' => count($contests)
        ]);
    }

    public function actionSetting($action = 'profile')
    {
        $model = User::findOne(Yii::$app->user->id);
        if ($model->role === User::ROLE_PLAYER) {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
        switch ($action) {
            case 'account':
                break;
            case 'profile':
                $model->scenario = 'profile';
                break;
            case 'security':
                $model->scenario = 'security';
                break;
            default:
                $action = 'profile';
                break;
        }
        $oldEmail = $model->email;
        $profile = UserProfile::findOne($model->id);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->validate()) {
                if ($model->scenario == 'security') {
                    $model->setPassword($model->newPassword);
                }
            }
            if ($oldEmail != $model->email) {
                $model->is_verify_email = User::VERIFY_EMAIL_NO;
            }
            $model->save();
            if ($profile->load(Yii::$app->request->post())) {
                $profile->save();
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Saved successfully'));
            return $this->refresh();
        }

        return $this->render('setting', [
            'model' => $model,
            'action' => $action,
            'profile' => $profile
        ]);
    }

    public function actionVerifyEmail()
    {
        $user = User::findOne(Yii::$app->user->id);
        $user->generateEmailVerificationToken();
        $user->update(false);
        $res = Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->setting->get('emailUsername') => Yii::$app->setting->get('ojName')])
            ->setTo($user->email)
            ->setSubject('Email verification - ' . Yii::$app->setting->get('ojName'))
            ->send();
        if ($res) {
            Yii::$app->session->setFlash('success', 'A verification link has been sent to your email: ' . $user->email .'，please check and click the verification link to confirm.');
        } else {
            Yii::$app->session->setFlash('error', 'Failed to send verification email.');
        }
        $this->redirect(['/user/setting', 'action' => 'account']);
    }

    protected function findModel($id)
    {
        if (is_numeric($id)) {
            $model = User::findOne($id);
        } else {
            $model = User::find()->where(['username' => $id])->one();
        }

        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
