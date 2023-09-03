<?php

use yii\helpers\Url;

$this->title = $model->title;
$this->registerJsFile('@web/js/scrollboard.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerCssFile('@web/css/scrollboard.css');

$start_time = $model->start_time;
$lock_time = $model->lock_board_time;
$problem_count = $model->getProblemCount();
$url = Url::toRoute(['contest/scroll-scoreboard', 'id' => $model->id, 'json' => true]);
$this->registerJs("
    function getSubmitList() {
        var data = new Array();
        $.ajax({
            type: \"GET\",
            content: \"application/x-www-form-urlencoded\",
            url: \"{$url}\",
            dataType: \"json\",
            data: {},
            async: false,
            success: function(result) {
                for (var key in result.data) {
                    var sub = result.data[key];
                    data.push(new Submit(sub.submitId, sub.username, sub.alphabetId, sub.subTime, sub.resultId));
                }
            },
            error: function() {
                alert(\"Failed to get Submit data\");
            }
        });
        return data;
    }
    function getTeamList() {
        var data = new Array();
        $.ajax({
            type: \"GET\",
            content: \"application/x-www-form-urlencoded\",
            url: \"{$url}\",
            dataType: \"json\",
            async: false,
            data: {},
            success: function(result) {
                for (var key in result.data) {
                    var team = result.data[key];
                    data[team.username] = new Team(team.username, team.nickname, null, 1);
                }
            },
            error: function() {
                alert(\"Failed to get Team data\");
            }
        });
        return data;
    }
", \yii\web\View::POS_END);

$this->registerJs("
var board = new Board(
    {$problem_count},
    new Array({$numberOfGoldMedals}, {$numberOfSilverMedals}, {$numberOfBronzeMedals}),
    StringToDate(\"{$start_time}\"),
    StringToDate(\"{$lock_time}\")
);
board.showInitBoard();
$('html').keydown(function(e) {
    if (e.keyCode == 13) {
        board.keydown();
    }
});
");
