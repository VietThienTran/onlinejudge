<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;

$this->title = $model->title;
$this->params['model'] = $model;
$problems = $model->problems;

$nav = [];
$nav[''] = 'All';
foreach ($problems as $key => $p) {
    $nav[$p['problem_id']] = chr(65 + $key) . '-' . $p['title'];
}
?>
<div class="wrap">
    <div class="container">
        <h1>
            <?= Html::a(Html::encode($model->title), ['view', 'id' => $model->id]) ?>
        </h1>
        <?php Modal::begin([
            'header' => '<h3>'.Yii::t('app','Attention!').'</h3>',
            'toggleButton' => ['label' => Yii::t('app', 'Show the submissions in frontend'), 'class' => 'btn btn-success'],
        ]); ?>
        <?= Html::a('Have read the above content and display the submission record', ['/admin/contest/status', 'id' => $model->id, 'active' => 1], ['class' => 'btn btn-danger']) ?>
        <?php Modal::end(); ?>

        <?= Html::a('Hide submission records in frontend', ['/admin/contest/status', 'id' => $model->id, 'active' => 2], ['class' => 'btn btn-default']) ?>
        <?= Html::a(
            'Download a record of submissions during the contest',
            ['/admin/contest/download-solution', 'id' => $model->id],
            ['class' => 'btn btn-primary', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'Download codes for correct answers during the contest, which can be used to check duplicates']
        ); ?>
        <?php Pjax::begin() ?>
        <?= Html::beginForm(
            ['/admin/contest/status', 'id' => $model->id],
            'get',
            ['class' => 'toggle-auto-refresh']
        ); ?>
        <div class="checkbox">
            <label>
                <?= Html::checkbox('autoRefresh', $autoRefresh) ?>
                Automatically refresh page
            </label>
        </div>
        <?= Html::endForm(); ?>
        <div class="solution-index" style="margin-top: 20px">
            <?= $this->render('_status_search', ['model' => $searchModel, 'nav' => $nav, 'contest_id' => $model->id]); ?>

            <?= GridView::widget([
                'layout' => '{items}{pager}',
                'dataProvider' => $dataProvider,
                'options' => ['class' => 'table-responsive'],
                'columns' => [
                    [
                        'attribute' => 'id',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::a($model->id, ['/solution/detail', 'id' => $model->id], ['target' => '_blank']);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'user',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::a(Html::encode($model->username) . '[' . Html::encode($model->user->nickname) . ']', ['/user/view', 'id' => $model->created_by]);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'label' => Yii::t('app', 'Problem'),
                        'value' => function ($model, $key, $index, $column) {
                            $res = $model->getProblemInContest();
                            if (!isset($model->problem)) {
                                return null;
                            }
                            if (!isset($res->num)) {
                                return $model->problem->title;
                            }
                            return Html::a(chr(65 + $res->num) . ' - ' . $model->problem->title,
                                ['/contest/problem', 'id' => $res->contest_id, 'pid' => $res->num]);
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'result',
                        'value' => function ($model, $key, $index, $column) {
                            if ($model->result == $model::OJ_CE || $model->result == $model::OJ_WA
                                || $model->result == $model::OJ_RE) {
                                return Html::a($model->getResult(),
                                    ['/solution/result', 'id' => $model->id],
                                    ['onclick' => 'return false', 'data-click' => "solution_info"]
                                );
                            } else {
                                return $model->getResult();
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'score',
                        'visible' => Yii::$app->setting->get('oiMode')
                    ],
                    [
                        'attribute' => 'time',
                        'value' => function ($model, $key, $index, $column) {
                            return $model->time . ' MS';
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'memory',
                        'value' => function ($model, $key, $index, $column) {
                            return $model->memory . ' KB';
                        },
                        'format' => 'raw'
                    ],
                    [
                        'attribute' => 'language',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::a($model->getLang(),
                                ['/solution/source', 'id' => $model->id],
                                ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                            );
                        },
                        'format' => 'raw'
                    ],
                    'code_length',
                    'created_at:datetime',
                ],
            ]); ?>
        </div>
<?php
$url = \yii\helpers\Url::toRoute(['/solution/verdict']);
$loadingImgUrl = Yii::getAlias('@web/images/loading.gif');
$js = <<<EOF
$('[data-toggle="tooltip"]').tooltip();
$(".toggle-auto-refresh input[name='autoRefresh']").change(function () {
    $(".toggle-auto-refresh").submit();
});
$('[data-click=solution_info]').click(function() {
    $.ajax({
        url: $(this).attr('href'),
        type:'post',
        error: function(){alert('error');},
        success:function(html){
            $('#solution-content').html(html);
            $('#solution-info').modal('show');
        }
    });
});
function updateVerdictByKey(submission) {
    $.get({
        url: "{$url}?id=" + submission.attr('data-submissionid'),
        success: function(data) {
            var obj = JSON.parse(data);
            submission.attr("waiting", obj.waiting);
            submission.text(obj.result);
            if (obj.verdict === "4") {
                submission.attr("class", "text-success")
            }
            if (obj.waiting === "true") {
                submission.append('<img src="{$loadingImgUrl}" alt="loading">');
            }
        }
    });
}
var waitingCount = $("strong[waiting=true]").length;
if (waitingCount > 0) {
    console.log("There is waitingCount=" + waitingCount + ", starting submissionsEventCatcher...");
    var interval = null;
    var testWaitingsDone = function () {
        var waitingCount = $("strong[waiting=true]").length;
        console.log("There is waitingCount=" + waitingCount + ", starting submissionsEventCatcher...");
        $("strong[waiting=true]").each(function(){
            updateVerdictByKey($(this));
        });
        if (interval && waitingCount === 0) {
            console.log("Stopping submissionsEventCatcher.");
            clearInterval(interval);
            interval = null;
        }
    }
    interval = setInterval(testWaitingsDone, 1000);
}
EOF;

if ($autoRefresh) {
    $js .= 'setTimeout(function(){ location.reload() }, 2000);'; 
}
$this->registerJs($js);
?>
        <?php Pjax::end() ?>
    </div>
</div>

<?php Modal::begin([
    'header' => '<h3>'.Yii::t('app','Information').'</h3>',
    'options' => ['id' => 'solution-info']
]); ?>
    <div id="solution-content">
    </div>
<?php Modal::end(); ?>