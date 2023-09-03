<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Solution;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;

$model->setSamples();
?>

<?php if ($model->spj): ?>
    <p>Please fill in the special judgment procedure below. Refer to: <?= Html::a('Special Judge', ['/wiki/spj']) ?>
    </p>
    <?php $form = ActiveForm::begin(); ?>


    <?= $form->field($model, 'spj_lang')->textInput([
        'maxlength' => true, 'value' => 'C、C++', 'disabled' => true
    ])->hint('Only support for C/C++.') ?>

    <?= $form->field($model, 'spj_source')->widget('app\widgets\codemirror\CodeMirror'); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
<?php else: ?>
    <p>The problem is not an SPJ judgment. If you want to enable SPJ judgment, please go to the editing page and change Special Judge to Yes.</p>
<?php endif; ?>
