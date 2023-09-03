<?php
use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
AppAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=0">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="shortcut icon" href="<?= Yii::getAlias('@web') ?>/images/favicon.ico">
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
                    <a href="?lang=en"><img src="<?= Yii::getAlias('@web') ?>/images/uk.ico" alr="English" width="50px" height="50px"></a> <a href="?lang=vi"><img src="<?= Yii::getAlias('@web') ?>/images/vn.ico" alr="Tiếng Việt" width="50px" height="50px"></a>
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
        'options' => ['class' => 'navbar-nav'],
        'items' => $menuItems,
        'encodeLabels' => false,
        'activateParents' => true
    ]);
    NavBar::end();
    ?>

    <?php
    if (!Yii::$app->user->isGuest && Yii::$app->setting->get('mustVerifyEmail') && !Yii::$app->user->identity->isVerifyEmail()) {
        $a = Html::a('Personal settings', ['/user/setting', 'action' => 'account']);
        $b = Yii::t('app','Please go to the settings page to verify your email');
        echo "<div class=\"container\"><p class=\"bg-danger\"> {$b}: {$a}</p></div>";
    }
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer" style="padding-top: 10px">
    <div class="text-center">
        <p><b>Greenhat Online Judge - Power by Viet Thien Tran - Base on <a href="http://www.hustoj.org">HUSTOJ</a></b><p>
        <p class="text-info">Follow me at:   
        <a href="https://facebook.com/vietthientran.301"><img src="<?= Yii::getAlias('@web') ?>/images/facebook.ico"> Facebook   |   </a>
        <a href="https://www.youtube.com/channel/UCKenIExlFhPC0_ZC3kluKqQ"><img src="<?= Yii::getAlias('@web') ?>/images/youtube.ico"> Youtube   |   </a>
        <a href="https://www.github.com/VietThienTran"><img src="<?= Yii::getAlias('@web') ?>/images/github.ico"> Github </a>
        </p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
