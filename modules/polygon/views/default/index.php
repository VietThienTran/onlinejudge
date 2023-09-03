<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Polygon System');
?>
<h2><?= $this->title ?></h2>
<p><?= Yii::t('app', 'Professional way to prepare programming contest problem') ?></p>
<hr>
<p>Base on <a href="https://polygon.codeforces.com/" target="_blank">Codeforces Polygon</a></p>
<div class="well">
    <ul>
        <li>Fill in the basic information of the problem</li>
        <li>Prepare test data</li>
        <li>Verify that the problem is correct</li>
    </ul>
</div>
<p>Note: Any user can use Polygon System to prepare problems, but user can only view the problems created by themselves, and the administrator has view all problems.</p>

<hr>
<div class="problem-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Problem'), ['/polygon/problem/create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php echo $this->render('/problem/_search', ['model' => $searchModel]); ?>
    <br>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->id, ['problem/view', 'id' => $key]);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'title',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->title), ['problem/view', 'id' => $key]);
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'created_by',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->user) {
                        return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user->id]);
                    }
                    return '';
                },
                'format' => 'raw'
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'controller' => 'problem'
            ],
        ],
    ]); ?>
</div>
