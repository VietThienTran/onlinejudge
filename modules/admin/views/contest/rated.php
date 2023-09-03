<?php

use yii\helpers\Html;
use app\models\Contest;
use yii\grid\GridView;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];
?>
<div class="contest-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr>
    <p>
        Clicking the button below will calculate the points of the user who participated in the contest. The calculated points are used to rank in the leaderboard. Repeated clicks are counted only once.
    </p>
    <?php if ($model->getRunStatus() == Contest::STATUS_ENDED): ?>
        <?= Html::a('Rated', ['rated', 'id' => $model->id, 'cal' => 1], ['class' => 'btn btn-success']) ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'user',
                    'value' => function ($model, $key, $index, $column) {
                        return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user->id]);
                    },
                    'format' => 'raw'
                ],
                'rating_change'
            ],
        ]); ?>
    <?php else: ?>
        <p>The contest is not finish yet, please calculate the points after the contest is finish.</p>
    <?php endif; ?>
</div>
