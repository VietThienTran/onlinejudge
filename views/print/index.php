<?php

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Print Sources';
$this->params['breadcrumbs'][] = ['label' => Html::encode($contest->title), 'url' => ['/contest/view', 'id' => $contest->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="print-source-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Print source code', ['create', 'id' => $contest->id], ['class' => 'btn btn-success']) ?> If you need to print the code for your teammates to check, please click this button to submit, and the staff will print it and send it to the team.
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->id, ['/print/view', 'id' => $model->id], ['target' => '_blank']);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'who',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->user->username) . ' [' . Html::encode($model->user->nickname) . ']', ['/user/view', 'id' => $model->user->id]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'status',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->status == \app\models\ContestPrint::STATUS_HAVE_READ) {
                        $text = '<p class="text-success"><strong>' . Yii::t('app', 'Already processed') . '</strong></p>';
                    } else {
                        $text = '<p class="text-danger"><strong>' . Yii::t('app', 'Not processed yet') . '</strong></p>';
                    }
                    return Html::a($text, ['/print/view', 'id' => $model->id]);
                },
                'format' => 'raw'
            ],
            'created_at:datetime',
            [
                'class' => 'yii\grid\ActionColumn'
            ],
        ],
    ]); ?>
</div>
