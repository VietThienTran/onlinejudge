<?php

use yii\helpers\Html;

$this->title = $model->title;
$problems = $model->problems;
$rank_result = $model->getOIRankData(false);
$first_blood = $rank_result['first_blood'];
$result = $rank_result['rank_result'];
$submit_count = $rank_result['submit_count'];

$this->registerAssetBundle('yii\bootstrap\BootstrapPluginAsset');
?>
<div class="wrap">
    <div class="container">
        <div class="alert alert-warning alert-dismissible fade in hidden-print" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
        </div>
        <div class="row">
            <div class="col-md-3 text-left">
                <strong>Start </strong>
                <?= $model->start_time ?>
            </div>
            <div class="col-md-6 text-center">
                <h2 class="contest-title"><?= Html::encode($model->title) ?></h2>
            </div>
            <div class="col-md-3 text-right">
                <strong>End </strong>
                <?= $model->end_time ?>
            </div>
        </div>
        <table class="table table-bordered table-rank">
            <thead>
            <tr>
                <th width="60px">Rank</th>
                <th width="120px">Username</th>
                <th width="120px">Nickname</th>
                <th width="80px">Total score</th>
                <th width="80px">Rejudge score</th>
                <th>
                    Total time
                    <span data-toggle="tooltip" data-placement="top" title="Only count the total time required to accepted all the problems (unit: minutes）">
                        <span class="glyphicon glyphicon-question-sign"></span>
                    </span>
                </th>
                <?php foreach($problems as $key => $p): ?>
                    <th>
                        <?= chr(65 + $key) ?>
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
                        if ($model->scenario == \app\models\Contest::SCENARIO_OFFLINE && $rank['role'] != \app\models\User::ROLE_PLAYER) {
                            echo '*';
                        } else {
                            echo $ranking;
                            $ranking++;
                        }
                        ?>
                    </th>
                    <th>
                        <?= Html::encode($rank['username']); ?>
                    </th>
                    <th>
                        <?= Html::encode($rank['nickname']); ?>
                    </th>
                    <th class="score-solved">
                        <?= $rank['total_score'] ?>
                    </th>
                    <th class="score-time">
                        <?= $rank['correction_score'] ?>
                    </th>
                    <th>
                        <?= intval($rank['total_time']) ?>
                    </th>
                    <?php
                    foreach($problems as $key => $p) {
                        $score = "";
                        $max_score = "";
                        if (isset($rank['score'][$p['problem_id']])) {
                            $score = $rank['score'][$p['problem_id']];
                            $max_score = $rank['max_score'][$p['problem_id']];
                        }
                        echo "<th class=\"table-problem-cell\">{$score}<br><small>{$max_score}</small></th>";
                    }
                    ?>
                </tr>
            <?php endfor; ?>
            </tbody>
        </table>
    </div>
</div>

<footer class="footer" style="padding-top: 10px">
    <div class="text-center">
        <p>Greenhat Online Judge - Power by Viet Thien Tran - Base on <a href="http://www.hustoj.org">HUSTOJ</a></p>
        <p class="text-info">Follow me at:   
        <a href="https://facebook.com/vietthientran.301"><img src="<?= Yii::getAlias('@web') ?>/images/facebook.ico"> Facebook   |   </a>
        <a href="https://www.youtube.com/channel/UCKenIExlFhPC0_ZC3kluKqQ"><img src="<?= Yii::getAlias('@web') ?>/images/youtube.ico"> Youtube   |   </a>
        <a href="https://www.github.com/VietThienTran"><img src="<?= Yii::getAlias('@web') ?>/images/github.ico"> Github </a>
        </p>
    </div>
</footer>
