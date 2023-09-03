<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\components\AccessRule;
use app\modules\admin\models\GenerateUserForm;
use app\models\User;
use app\models\UserSearch;

class UserController extends Controller
{
    public $layout = 'main';

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
        $searchModel = new UserSearch();
        $generatorForm = new GenerateUserForm();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($generatorForm->load(Yii::$app->request->post())) {
            $generatorForm->generateUsers();
            return $this->refresh();
        }

        if (Yii::$app->request->get('action') && Yii::$app->request->isPost) {
            $ids = Yii::$app->request->post('ids');
            $action = Yii::$app->request->post('action');
            $value = Yii::$app->request->post('value');
            if ($action === 'setRole') {
                Yii::$app->db->createCommand()->update('{{%user}}', [
                    'role' => $value
                ], ['id' => $ids])->execute();
            } else if ($action === 'setStatus') {
                Yii::$app->db->createCommand()->update('{{%user}}', [
                    'status' => $value
                ], ['id' => $ids])->execute();
            }
            return $this->refresh();
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'generatorForm' => $generatorForm
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $newPassword = Yii::$app->request->post('User')['newPassword'];
            $role = Yii::$app->request->post('User')['role'];
            if (!empty($newPassword)) {
                Yii::$app->db->createCommand()->update('{{%user}}', [
                    'password_hash' => Yii::$app->security->generatePasswordHash($newPassword)
                ], ['id' => $model->id])->execute();
            } else if (!empty($role)) {
                $model->role = intval($role);
                $model->save();
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Saved successfully'));
            return $this->refresh();
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        Yii::$app->session->setFlash('error', 'Deleting users is not currently supported');

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
