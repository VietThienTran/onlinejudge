<?php

namespace app\controllers;

use app\components\BaseController;
use app\models\Contest;
use Yii;
use app\models\Solution;
use app\models\SolutionSearch;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;

class SolutionController extends BaseController
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
        ];
    }

    public function actionIndex()
    {
        $searchModel = new SolutionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionVerdict($id)
    {
        $query = Yii::$app->db->createCommand('SELECT id,result,contest_id FROM {{%solution}} WHERE id=:id', [
            ':id' => $id
        ])->queryOne();

        if (!empty($query['contest_id'])) {
            $contest = Solution::getContestInfo($query['contest_id']);
            if ($contest['type'] == Contest::TYPE_OI) {
                $query['result'] = 0;
            }
        }
        $res = [
            'id' => $query['id'],
            'verdict' => $query['result'],
            'waiting' => $query['result'] <= Solution::OJ_WAITING_STATUS ? 'true' : 'false',
            'result' => Solution::getResultList($query['result'])
        ];
        return json_encode($res);
    }

    public function actionSource($id)
    {
        $this->layout = false;
        $model = $this->findModel($id);

        return $this->render('source', [
            'model' => $model,
        ]);
    }

    public function actionResult($id)
    {
        $this->layout = false;
        $model = $this->findModel($id);

        return $this->render('result', [
            'model' => $model,
        ]);
    }

    public function actionDetail($id)
    {
        $this->layout = 'main';
        $model = $this->findModel($id);

        return $this->render('detail', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Solution::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
