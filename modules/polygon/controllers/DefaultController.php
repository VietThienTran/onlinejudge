<?php

namespace app\modules\polygon\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use app\modules\polygon\models\Problem;
use app\models\User;
use app\modules\polygon\models\ProblemSearch;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new ProblemSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }
}
