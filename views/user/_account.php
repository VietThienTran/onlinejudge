<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$verifyLink = Yii::$app->urlManager->createAbsoluteUrl(['/user/verify-email']);

if ($model->isVerifyEmail()) {
    $emailTemplate = '{label}<div class="input-group">{input}<div class="input-group-addon">Verified</div></div>{hint}{error}';
} else {
    $emailTemplate = '{label}<div class="input-group">{input}<div class="input-group-addon">
        Unverified. <a href="' . $verifyLink . '"> send verification link</a>
        </div></div>{hint}{error}';
}
?>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'username')->textInput() ?>

<?= $form->field($model, 'email', [
        'template' => $emailTemplate
])->textInput() ?>

<div class="form-group">
    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>
