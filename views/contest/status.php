<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use app\models\Contest;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $searchModel app\models\SolutionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $data array */

$this->title = $model->title;
$this->params['model'] = $model;
$problems = $model->problems;

$nav = [];
$nav[''] = 'All';
foreach ($problems as $key => $p) {
    $nav[$p['problem_id']] = chr(65 + $key) . '-' . $p['title'];
}
$userInContest = $model->isUserInContest();
$isContestEnd = $model->isContestEnd();
?>
<div class="solution-index" style="margin-top: 20px">
    <?php if ($model->isScoreboardFrozen()) :?>
        <p class="text-center">现已是封榜状态，榜单将不再实时更新，只显示封榜前的提交及您个人的所有提交记录。</p>
    <?php endif; ?>
    <?php Pjax::begin() ?>
    <?php if ($model->type != Contest::TYPE_OI || $isContestEnd): ?>
    <?= $this->render('_status_search', ['model' => $searchModel, 'nav' => $nav, 'contest_id' => $model->id]); ?>
    <?php endif; ?>

    <?= GridView::widget([
        'layout' => '{items}{pager}',
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->id, ['/solution/detail', 'id' => $model->id], ['target' => '_blank']);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'who',
                'value' => function ($model, $key, $index, $column) {
                    if (isset($model->user)) {
                        return Html::a($model->user->colorname, ['/user/view', 'id' => $model->created_by]);
                    }
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
                'value' => function ($solution, $key, $index, $column) use ($model, $userInContest, $isContestEnd) {
                    // OI 比赛模式未结束时不返回具体结果
                    if ($model->type == Contest::TYPE_OI && !$isContestEnd) {
                        return Yii::t('app', 'Pending');
                    }
                    $otherCan = ($isContestEnd && Yii::$app->setting->get('isShareCode'));
                    $createdBy = (!Yii::$app->user->isGuest && ($model->created_by == Yii::$app->user->id || Yii::$app->user->id == $solution->created_by));
                    if ($otherCan || $createdBy || $model->type == Contest::TYPE_HOMEWORK || ($userInContest && $isContestEnd)) {
                        return Html::a($solution->getResult(),
                            ['/solution/result', 'id' => $solution->id],
                            ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                        );
                    } else {
                        return $solution->getResult();
                    }
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'score',
                'visible' => $model->type == Contest::TYPE_IOI || $model->type == Contest::TYPE_HOMEWORK ||
                            ($model->type == Contest::TYPE_OI && $isContestEnd)
            ],
            [
                'attribute' => 'time',
                'value' => function ($solution, $key, $index, $column) use ($model, $isContestEnd) {
                    // OI 比赛模式未结束时不返回具体结果
                    if ($model->type == \app\models\Contest::TYPE_OI && !$isContestEnd) {
                        return "－";
                    }
                    return $solution->time . ' MS';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'memory',
                'value' => function ($solution, $key, $index, $column) use ($model, $isContestEnd) {
                    // OI 比赛模式未结束时不返回具体结果
                    if ($model->type == \app\models\Contest::TYPE_OI && !$isContestEnd) {
                        return "－";
                    }
                    return $solution->memory . ' KB';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'language',
                'value' => function ($solution, $key, $index, $column) use ($model, $isContestEnd) {
                    $otherCan = ($isContestEnd && Yii::$app->setting->get('isShareCode'));
                    if ($solution->canViewSource() || $otherCan) {
                        return Html::a($solution->getLang(),
                            ['/solution/source', 'id' => $solution->id],
                            ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                        );
                    } else {
                        return $solution->getLang();
                    }
                },
                'format' => 'raw'
            ],
            'code_length',
            'created_at:datetime',
        ],
    ]); ?>
<?php
$url = \yii\helpers\Url::toRoute(['/solution/verdict']);
$loadingImgUrl = Yii::getAlias('@web/images/loading.gif');
$js = <<<EOF
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
    var waitingQueue = [];
    $("strong[waiting=true]").each(function(){
        waitingQueue.push($(this));
    });
    waitingQueue.reverse();
    var testWaitingsDone = function () {
        updateVerdictByKey(waitingQueue[0]);
        var waitingCount = $("strong[waiting=true]").length;
        while (waitingCount < waitingQueue.length) {
            if (waitingCount < waitingQueue.length) {
                waitingQueue.shift();
            }
            if (waitingQueue.length === 0) {
                break;
            }
            updateVerdictByKey(waitingQueue[0]);
            waitingCount = $("strong[waiting=true]").length;
        }
        console.log("There is waitingCount=" + waitingCount + ", starting submissionsEventCatcher...");
        
        if (interval && waitingCount === 0) {
            console.log("Stopping submissionsEventCatcher.");
            clearInterval(interval);
            interval = null;
        }
    }
    interval = setInterval(testWaitingsDone, 500);
}
EOF;
$this->registerJs($js);
?>
    <?php Pjax::end() ?>
</div>
<?php Modal::begin([
    'options' => ['id' => 'solution-info']
]); ?>
<div id="solution-content">
</div>
<?php Modal::end(); ?>
