<?php

use yii\bootstrap\Nav;
$model = $this->params['model'];
?>
<?php $this->beginContent('@app/views/layouts/main.php'); ?>
<div class="col-md-2">
    <?= Nav::widget([
        'options' => ['class' => 'nav nav-pills nav-stacked'],
        'items' => [
            ['label' => Yii::t('app', 'Home'), 'url' => ['/admin/default/index']],
            ['label' => Yii::t('app', 'News'), 'url' => ['/admin/news/index']],
            ['label' => Yii::t('app', 'Problem'), 'url' => ['/admin/problem/index']],
            ['label' => Yii::t('app', 'User'), 'url' => ['/admin/user/index']],
            ['label' => Yii::t('app', 'Contest'), 'url' => ['/admin/contest/index']],
            ['label' => Yii::t('app', 'Rejudge'), 'url' => ['/admin/rejudge/index']],
            ['label' => Yii::t('app', 'Setting'), 'url' => ['/admin/setting/index']],
            ['label' => Yii::t('app', 'Polygon System'), 'url' => ['/polygon']]
        ],
    ]) ?>
</div>
<div class="col-md-10">
    <div class="problem-header">
        <?= \yii\bootstrap\Nav::widget([
            'options' => ['class' => 'nav nav-pills'],
            'items' => [
                ['label' => Yii::t('app', 'Preview'), 'url' => ['/admin/problem/view', 'id' => $model->id]],
                ['label' => Yii::t('app', 'Edit'), 'url' => ['/admin/problem/update', 'id' => $model->id]],
                ['label' => Yii::t('app', 'Solution'), 'url' => ['/admin/problem/solution', 'id' => $model->id]],
                ['label' => Yii::t('app', 'Tests Data'), 'url' => ['/admin/problem/test-data', 'id' => $model->id]],
                ['label' => Yii::t('app', 'Verify Data'), 'url' => ['/admin/problem/verify', 'id' => $model->id]],
                ['label' => Yii::t('app', 'SPJ'), 'url' => ['/admin/problem/spj', 'id' => $model->id]],
                ['label' => Yii::t('app', 'Subtask'), 'url' => ['/admin/problem/subtask', 'id' => $model->id]]
            ],
        ]) ?>
    </div>
    <hr>
    <?= $content ?>
</div>
<?php $this->endContent(); ?>
<script type="text/javascript">
    $(document).ready(function () {
        var socket = io(document.location.protocol + '//' + document.domain + ':2120');
        var uid = <?= Yii::$app->user->isGuest ? session_id() : Yii::$app->user->id ?>;
        socket.on('connect', function () {
            socket.emit('login', uid);
        });
        socket.on('msg', function (msg) {
            alert(msg);
        });
    })
</script>
