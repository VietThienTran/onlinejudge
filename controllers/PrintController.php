<?php

namespace app\controllers;

use app\components\BaseController;
use app\models\User;
use app\models\Contest;
use app\models\ContestPrint;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

class PrintController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['index', 'view', 'create', 'delete', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex($id)
    {
        $contest = Contest::findOne($id);
        if ($contest === null || $contest->scenario != Contest::SCENARIO_OFFLINE) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $query = ContestPrint::find()->where(['contest_id' => $contest->id])->with('user');
        } else {
            $query = ContestPrint::find()->where(['contest_id' => $contest->id, 'user_id' => Yii::$app->user->id])->with('user');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query->orderBy('id DESC'),
        ]);

        return $this->render('index', [
            'contest' => $contest,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $model->status = ContestPrint::STATUS_HAVE_READ;
            $model->update();
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate($id)
    {
        $model = new ContestPrint();
        $contest = Contest::findOne($id);
        if ($contest === null || $contest->scenario != Contest::SCENARIO_OFFLINE) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($model->load(Yii::$app->request->post())) {
            $model->contest_id = $contest->id;
            $model->save();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Submitted successfully'));
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'contest' => $contest,
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $cid = $model->contest_id;
        $model->delete();

        return $this->redirect(['index', 'id' => $cid]);
    }

    protected function findModel($id)
    {
        if (($model = ContestPrint::findOne($id)) !== null && !Yii::$app->user->isGuest) {
            if ($model->user_id == Yii::$app->user->id || Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                return $model;
            }
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
