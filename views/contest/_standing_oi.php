<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use app\models\Contest;

$problems = $model->problems;
$first_blood = $rankResult['first_blood'];
$result = $rankResult['rank_result'];
$submit_count = $rankResult['submit_count'];

if (Yii::$app->user->isGuest || !Yii::$app->user->identity->isAdmin()) {
    if ($model->isScoreboardFrozen() || ($model->type == Contest::TYPE_OI && !$model->isContestEnd())) {
        echo '<p>'.Yii::t('app','Scoreboard is frozen').'</p>';
        return;
    }
}
?>
<table class="table table-bordered table-rank" style="margin-top: 15px">
    <thead>
    <tr>
        <th width="60px">Rank</th>
        <th width="200px">User</th>
        <?php if ($model->type == Contest::TYPE_OI): ?>
        <th width="80px">Total score</th>
        <th width="80px">Total score rejudge</th>
        <?php else: ?>
        <th width="80px">Total submit</th>
        <th width="80px">Total score</th>
        <?php endif; ?>
        <th width="80px">
            Time
            <span data-toggle="tooltip" data-placement="top" title="">
                <span class="glyphicon glyphicon-question-sign"></span>
            </span>
        </th>
        <?php foreach($problems as $key => $p): ?>
            <th>
                <?= Html::a(chr(65 + $key), ['/contest/problem', 'id' => $model->id, 'pid' => $key]) ?>
                <br>
                <span style="color:#7a7a7a; font-size:12px">
                    <?php
                    if (isset($submit_count[$p['problem_id']]['solved']))
                        echo $submit_count[$p['problem_id']]['solved'];
                    else
                        echo 0;
                    ?>
                    /
                    <?php
                    if (isset($submit_count[$p['problem_id']]['submit']))
                        echo $submit_count[$p['problem_id']]['submit'];
                    else
                        echo 0;
                    ?>
                </span>
            </th>
        <?php endforeach; ?>
    </tr>
    </thead>
    <tbody>
    <?php for ($i = 0, $ranking = 1; $i < count($result); $i++): ?>
    <?php $rank = $result[$i]; ?>
        <tr>
            <th>
                <?php
                if ($model->scenario == Contest::SCENARIO_OFFLINE && $rank['role'] != \app\models\User::ROLE_PLAYER) {
                    echo '*';
                } else {
                    echo $ranking;
                    $ranking++;
                }
                ?>
            </th>
            <th>
                <?= Html::a(Html::encode($rank['nickname']), ['/user/view', 'id' => $rank['user_id']]) ?>
            </th>
            <?php if ($model->type == Contest::TYPE_OI): ?>
            <th class="score-solved">
                <?= $rank['total_score'] ?>
            </th>
            <?php else: ?>
            <th>
                <?= $rank['solved'] ?>
            </th>
            <?php endif ?>
            <th class="score-time">
                <?= $rank['correction_score'] ?>
            </th>
            <th>
                <?= intval($rank['total_time']) ?>
            </th>
            <?php
            foreach($problems as $key => $p) {
                $score = '';
                $max_score = '';
                $css_class = ''; 
                $first = ''; 
                $second = ''; 
                if (isset($rank['solved_flag'][$p['problem_id']])) {
                    $css_class = 'solved-first'; 
                } else if (isset($rank['pending'][$p['problem_id']]) && $rank['pending'][$p['problem_id']]) {
                    $css_class = 'pending'; 
                } else if (isset($rank['score'][$p['problem_id']]) && $rank['max_score'][$p['problem_id']] > 0) {
                    $css_class = 'solved'; 
                } else if (isset($rank['score'][$p['problem_id']]) && $rank['max_score'][$p['problem_id']] == 0) {
                    $css_class = 'attempted';
                }
                if (isset($rank['score'][$p['problem_id']])) {
                    $score = $rank['score'][$p['problem_id']];
                    $max_score = $rank['max_score'][$p['problem_id']];
                    if ($model->type == Contest::TYPE_OI) {
                        $first = $score;
                        $second = $max_score;
                    } else if ($model->type == Contest::TYPE_IOI) {
                        $first = $max_score;
                        if (isset($rank['submit_time'][$p['problem_id']])) {
                            $min = intval($rank['submit_time'][$p['problem_id']]);
                            $second = sprintf("%02d:%02d", $min / 60, $min % 60);
                        }
                    }
                }
                if ((!Yii::$app->user->isGuest && $model->created_by == Yii::$app->user->id) || $model->isContestEnd()) {
                    $url = Url::toRoute([
                        '/contest/submission',
                        'pid' => $p['problem_id'],
                        'cid' => $model->id,
                        'uid' => $rank['user_id']
                    ]);
                    echo "<th class=\"table-problem-cell {$css_class}\" style=\"cursor:pointer\" data-click='submission' data-href='{$url}'>"
                        . "{$first}<br><small>{$second}</small></th>";
                } else {
                    echo "<th class=\"table-problem-cell {$css_class}\">{$first}<br><small>{$second}</small></th>";
                }
            }
            ?>
        </tr>
    <?php endfor; ?>
</table>
<?php
$js = "
$(function () {
    $('[data-toggle=\"tooltip\"]').tooltip()
})
$('[data-click=submission]').click(function() {
    $.ajax({
        url: $(this).attr('data-href'),
        type:'post',
        error: function(){alert('error');},
        success:function(html){
            $('#submission-content').html(html);
            $('#submission-info').modal('show');
        }
    });
});
";
$this->registerJs($js);
?>
<?php Modal::begin([
    'options' => ['id' => 'submission-info']
]); ?>
    <div id="submission-content">
    </div>
<?php Modal::end(); ?>

