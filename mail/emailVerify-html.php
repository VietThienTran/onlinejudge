<?php
use yii\helpers\Html;


$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['site/verify-email', 'token' => $user->verification_token]);
?>
<div class="verify-email">
    <p>Hello <?= Html::encode($user->username) ?>,</p>

    <p>Please click the link below to verify your email: </p>

    <p><?= Html::a(Html::encode($verifyLink), $verifyLink) ?></p>
</div>
