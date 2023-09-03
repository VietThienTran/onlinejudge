<?php

use yii\helpers\Html;
use app\models\Solution;
use yii\widgets\ActiveForm;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;
?>
<div class="solutions-view">
    <h1>
        <?= Html::encode($model->title) ?>
    </h1>
    <?php if ($model->spj): ?>
        <p>
            Please fill in the special judgment procedure below. Refer to: <?= Html::a('Special Judge', ['/wiki/spj']) ?>
        </p>
        <hr>

        <?= Html::beginForm() ?>

        <div class="form-group">
            <?= Html::textInput('spjLang', 'C、C++', ['disabled' => true, 'class' => 'form-control']); ?>
            <p class="hint-block">Only support for C/C++.</p>
        </div>

        <div class="form-group">
            <?= Html::label(Yii::t('app', 'Spj'), 'spj', ['class' => 'sr-only']) ?>

            <?= \app\widgets\codemirror\CodeMirror::widget(['name' => 'spjContent', 'value' => $spjContent]);  ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= Html::endForm(); ?>
    <?php else: ?>
        <p>The problem is not an SPJ judgment. If you want to enable SPJ judgment, please go to the editing page and change Special Judge to Yes.</p>
    <?php endif; ?>
</div>
