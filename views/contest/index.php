<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Contest;

$this->title = Yii::t('app', 'Contests');
?>
<div class="contest-index">

    <?= GridView::widget([
        'layout' => '{items}{pager}',
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'table-responsive'],
        'columns' => [
            [
                'attribute' => 'title',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->title), ['/contest/view', 'id' => $key]);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'status',
                'value' => function ($model, $key, $index, $column) {
                    $link = Html::a(Yii::t('app', 'Register »'), ['/contest/register', 'id' => $model->id]);
                    if (!Yii::$app->user->isGuest && $model->isUserInContest()) {
                        $link = '<span class="well-done">' . Yii::t('app', 'Registration completed') . '</span>';
                    }
                    if ($model->status == Contest::STATUS_VISIBLE &&
                        !$model->isContestEnd() &&
                        $model->scenario == Contest::SCENARIO_ONLINE) {
                        $column = $model->getRunStatus(true) . ' ' . $link;
                    } else {
                        $column = $model->getRunStatus(true);
                    }
                    $userCount = $model->getContestUserCount();
                    return $column . ' ' . Html::a(' <span class="glyphicon glyphicon-user"></span>x'. $userCount, ['/contest/user', 'id' => $model->id]);
                },
                'format' => 'raw',
            ],
            'start_time',
            'end_time',
        ],
    ]); ?>
</div>
