<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Modal;

$this->title = Yii::t('app', 'Problems');
?>
<div class="problem-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Problem'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Polygon Problem'), ['create-from-polygon'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Import Problem'), ['import'], ['class' => 'btn btn-success']) ?>
    </p>
    <hr>
    <p>
        <a id="available" class="btn btn-success" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="">
            Visible
        </a>
        <a id="reserved" class="btn btn-success" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="">
            Hidden
        </a>
        <a id="private" class="btn btn-success" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="">
            Private
        </a>
        <a id="delete" class="btn btn-danger" href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="">
            Delete
        </a>
    </p>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['id' => 'grid'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'name' => 'id',
            ],
            [
                'attribute' => 'id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->id, ['problem/view', 'id' => $key]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'title',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->title), ['problem/view', 'id' => $key]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'status',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->status == \app\models\Problem::STATUS_VISIBLE) {
                        return Yii::t('app', 'Visible');
                    } else if ($model->status == \app\models\Problem::STATUS_HIDDEN) {
                        return Yii::t('app', 'Hidden');
                    } else {
                        return Yii::t('app', 'Private');
                    }
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
                'format' => 'raw',
            ],
            [
                'attribute' => 'polygon_id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->polygon_problem_id, ['/polygon/problem/view', 'id' => $model->polygon_problem_id]);
                },
                'format' => 'raw',
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]);
    $this->registerJs('
    $(function () {
      $(\'[data-toggle="tooltip"]\').tooltip()
    })
    $("#available").on("click", function () {
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.\yii\helpers\Url::to(['/admin/problem/index', 'action' => \app\models\Problem::STATUS_VISIBLE]).'", 
           dataType: \'json\',
           data: {keylist: keys}
        });
    });
    $("#reserved").on("click", function () {
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.\yii\helpers\Url::to(['/admin/problem/index', 'action' => \app\models\Problem::STATUS_HIDDEN]).'", 
           dataType: \'json\',
           data: {keylist: keys}
        });
    });
    $("#private").on("click", function () {
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.\yii\helpers\Url::to(['/admin/problem/index', 'action' => \app\models\Problem::STATUS_PRIVATE]).'", 
           dataType: \'json\',
           data: {keylist: keys}
        });
    });
    $("#delete").on("click", function () {
        if (confirm("Are you sure you want to delete?")) {
            var keys = $("#grid").yiiGridView("getSelectedRows");
            $.post({
               url: "'.\yii\helpers\Url::to(['/admin/problem/index', 'action' => 'delete']).'", 
               dataType: \'json\',
               data: {keylist: keys}
            });
        }
    });
    ');
    ?>
</div>
