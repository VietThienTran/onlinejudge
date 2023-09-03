<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\widgets\Alert;
use app\models\Contest;

AppAsset::register($this);

$this->registerJsFile('/js/jquery.countdown.min.js', ['depends' => 'yii\web\JqueryAsset']);
$model = $this->params['model'];
$status = $model->getRunStatus();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="shortcut icon" href="<?= Yii::getAlias('@web') ?>/images/favicon.ico">
    <style>
        .progress-bar {
            transition: none !important;
        }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <header id="header">
        <div class="container" style="height: 80px">
            <div class="page-header">
                <div class="logo pull-left">
                    <div class="pull-left">
                        <a class="navbar-brand" href="/site/index">
                            <img src="<?= Yii::getAlias('@web') ?>/images/logo.png" >
                        </a>
                    </div>
                    <div class="brand">
                        Online Judge    
                    </div>
                </div>
                <div class="pull-right">
                    <a></a>
                    <a href="/contest/index?lang=en"><img src="<?= Yii::getAlias('@web') ?>/images/uk.ico" alr="English" width="50px" height="50px"></a> <a href="/contest/index?lang=vi"><img src="<?= Yii::getAlias('@web') ?>/images/vn.ico" alr="Tiếng Việt" width="50px" height="50px"></a>
                    <br><?= Yii::t('app','Select language')?>
                </div> 
            </div>
        </div>
    </header>
    <?php
    NavBar::begin([
    ]);
    $menuItems = [
        ['label' => '<span class="glyphicon glyphicon-home"></span> ' . Yii::t('app', 'Home'), 'url' => ['/site/index']],
        ['label' => '<span class="glyphicon glyphicon-list"></span> ' . Yii::t('app', 'Problems'), 'url' => ['/problem/index']],
        ['label' => '<span class="glyphicon glyphicon-hourglass"></span> ' . Yii::t('app', 'Status'), 'url' => ['/solution/index']],
        [
            'label' => '<span class="glyphicon glyphicon-signal"></span> ' . Yii::t('app', 'Rating'),
            'url' => ['/rating/problem'],
            'active' => Yii::$app->controller->id == 'rating'
        ],
        [
            'label' => '<span class="glyphicon glyphicon-user"></span> ' . Yii::t('app', 'Group'),
            'url' => Yii::$app->user->isGuest ? ['/group/index'] : ['/group/my-group']
        ],
        ['label' => '<span class="glyphicon glyphicon-knight"></span> ' . Yii::t('app', 'Contests'), 'url' => ['/contest/index']],
        [
            'label' => '<span class="glyphicon glyphicon-info-sign"></span> '. Yii::t('app', 'Information'),
            'url' => ['/wiki/index'],
            'active' => Yii::$app->controller->id == 'wiki'
        ],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => '<span class="glyphicon glyphicon-new-window"></span> ' . Yii::t('app', 'Signup'), 'url' => ['/site/signup']];
        $menuItems[] = ['label' => '<span class="glyphicon glyphicon-log-in"></span> ' . Yii::t('app', 'Login'), 'url' => ['/site/login']];
    } else {
        if (Yii::$app->user->identity->isAdmin()) {
            $menuItems[] = [
                'label' => '<span class="glyphicon glyphicon-cog"></span> ' . Yii::t('app', 'System Management'),
                'url' => ['/admin'],
                'active' => Yii::$app->controller->module->id == 'admin'
            ];
        }
        $menuItems[] =  [
            'label' => '<span class="glyphicon glyphicon-user"></span> ' . Yii::$app->user->identity->nickname,
            'items' => [
                ['label' => '<span class="glyphicon glyphicon-home"></span> ' . Yii::t('app', 'Profile'), 'url' => ['/user/view', 'id' => Yii::$app->user->id]],
                ['label' => '<span class="glyphicon glyphicon-cog"></span> ' . Yii::t('app', 'Setting'), 'url' => ['/user/setting', 'action' => 'profile']],
                '<li class="divider"></li>',
                ['label' => '<span class="glyphicon glyphicon-log-out"></span> ' . Yii::t('app', 'Logout'), 'url' => ['/site/logout']],
            ]
        ];
    }
    
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-left navbar-right'],
        'items' => $menuItems,
        'encodeLabels' => false,
        'activateParents' => true
    ]);
    NavBar::end();
    ?>


    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <div class="contest-info">
            <div class="row">
                <div class="col-md-3 text-left hidden-print">
                    <strong><?= Yii::t('app', 'Start') ?> </strong>
                    <?= $model->start_time ?>
                </div>
                <div class="col-md-6 text-center">
                    <h2 class="contest-title">
                        <?= Html::encode($model->title) ?>
                        <?php if ($model->group_id != 0 && $model->isContestAdmin()): ?>
                            <small>
                                <?= Html::a('<span class="glyphicon glyphicon-cog"></span> ' . Yii::t('app', 'Setting'),
                                    ['/homework/update', 'id' => $model->id]) ?>
                            </small>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="col-md-3 text-right hidden-print">
                    <strong><?= Yii::t('app', 'End') ?> </strong>
                    <?= $model->end_time ?>
                </div>
            </div>
            <div class="progress hidden-print">
                <div class="progress-bar progress-bar-success" id="contest-progress" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 1%;">
                    <?php if ($status == $model::STATUS_NOT_START): ?>
                        Not start
                        <p><?= date('y-m-d H:i:s', time()) ?></p>
                    <?php elseif ($status == $model::STATUS_RUNNING): ?>
                        Running
                    <?php else: ?>
                        Contest is finished.
                    <?php endif; ?>
                </div>
            </div>
            <div class="text-center hidden-print">
                <strong><?= Yii::t('app', 'Now') ?></strong>
                <span id="nowdate"> <?= date("Y-m-d H:i:s") ?></span>
            </div>
        </div>
        <hr>
        <?php if ($status == $model::STATUS_NOT_START): ?>
            <div class="contest-countdown text-center">
                <div id="countdown"></div>
            </div>
            <?php if (!empty($model->description)): ?>
                <div class="contest-desc">
                    <?= Yii::$app->formatter->asMarkdown($model->description) ?>
                </div>
            <?php endif; ?>
        <?php elseif (!$model->canView()): ?>
            <?= $content ?>
        <?php else: ?>
            <div class="contest-view">
                <?php
                $menuItems = [
                    [
                        'label' => '<span class="glyphicon glyphicon-home"></span> ' . Yii::t('app', 'Information'),
                        'url' => ['contest/view', 'id' => $model->id],
                    ],
                    [
                        'label' => '<span class="glyphicon glyphicon-list"></span> ' . Yii::t('app', 'Problem'),
                        'url' => ['contest/problem', 'id' => $model->id],
                        'linkOptions' => ['data-pjax' => 0]
                    ],
                    [
                        'label' => '<span class="glyphicon glyphicon-signal"></span> ' . Yii::t('app' , 'Status'),
                        'url' => ['contest/status', 'id' => $model->id],
                        'linkOptions' => ['data-pjax' => 0],
                    ],
                    [
                        'label' => '<span class="glyphicon glyphicon-glass"></span> ' . Yii::t('app', 'Standing'),
                        'url' => ['contest/standing', 'id' => $model->id],
                    ],
                    [
                        'label' => '<span class="glyphicon glyphicon-comment"></span> ' . Yii::t('app', 'Clarification'),
                        'url' => ['contest/clarify', 'id' => $model->id],
                    ],
                ];
                if ($model->scenario == $model::SCENARIO_OFFLINE && $model->getRunStatus() == $model::STATUS_RUNNING) {
                    $menuItems[] = [
                        'label' => '<span class="glyphicon glyphicon-print"></span> Print Service',
                        'url' => ['/contest/print', 'id' => $model->id]
                    ];
                }
                if ($model->isContestEnd()) {
                    $menuItems[] = [
                        'label' => '<span class="glyphicon glyphicon-info-sign"></span> ' . Yii::t('app', 'Editorial'),
                        'url' => ['contest/editorial', 'id' => $model->id]
                    ];
                }
                echo Nav::widget([
                    'items' => $menuItems,
                    'options' => ['class' => 'nav nav-tabs hidden-print'],
                    'encodeLabels' => false
                ]) ?>
                <?php \yii\widgets\Pjax::begin() ?>
                <?= $content ?>
                <?php \yii\widgets\Pjax::end() ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="text-center">
        <p><b>Greenhat Online Judge - Power by Viet Thien Tran - Base on <a href="http://www.hustoj.org">HUSTOJ</a></b></p>
        <p class="text-info">Follow me at:   
        <a href="https://facebook.com/vietthientran.301"><img src="<?= Yii::getAlias('@web') ?>/images/facebook.ico"> Facebook   |   </a>
        <a href="https://www.youtube.com/channel/UCKenIExlFhPC0_ZC3kluKqQ"><img src="<?= Yii::getAlias('@web') ?>/images/youtube.ico"> Youtube   |   </a>
        <a href="https://www.github.com/VietThienTran"><img src="<?= Yii::getAlias('@web') ?>/images/github.ico"> Github </a>
        </p>
    </div>
</footer>
<?php $this->endBody() ?>
<script>
    var client_time = new Date();
    var diff = new Date("<?= date("Y/m/d H:i:s")?>").getTime() - client_time.getTime();
    var start_time = new Date("<?= $model->start_time ?>");
    var end_time = new Date("<?= $model->end_time ?>");
    $("#countdown").countdown(start_time.getTime() - diff, function(event) {
        $(this).html(event.strftime('%D:%H:%M:%S'));
    });
    function clock() {
        var h, m, s, n, y, mon, d;
        var x = new Date(new Date().getTime() + diff);
        y = x.getYear() + 1900;
        if (y > 3000) y -= 1900;
        mon = x.getMonth() + 1;
        d = x.getDate();
        h = x.getHours();
        m = x.getMinutes();
        s = x.getSeconds();

        n = y + "-" + mon + "-" + d + " " + (h >= 10 ? h : "0" + h) + ":" + (m >= 10 ? m : "0" + m) + ":" + (s >= 10 ? s : "0" + s);
        document.getElementById('nowdate').innerHTML = n;
        var now_time = new Date(n);
        if (now_time < end_time) {
            var rate = (now_time - start_time) / (end_time - start_time) * 100;
            document.getElementById('contest-progress').style.width = rate + "%";
        } else {
            document.getElementById('contest-progress').style.width = "100%";
        }
        setTimeout("clock()", 1000);
    }
    clock();

    $(document).ready(function () {
        var socket = io(document.location.protocol + '//' + document.domain + ':2120');
        var uid = '<?= Yii::$app->user->isGuest ? session_id() : Yii::$app->user->id ?>';
        socket.on('connect', function(){
            socket.emit('login', uid);
        });
        socket.on('msg', function(msg){
            alert(msg);
        });

        $('.pre p').each(function(i, block) {  
            hljs.highlightBlock(block);
        });
    });
</script>
</body>
</html>
<?php $this->endPage() ?>
