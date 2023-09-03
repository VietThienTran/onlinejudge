<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

$this->title = $model->title;
$this->params['model'] = $model;

if ($discuss != null) {
    return $this->render('_clarify_view', [
        'clarify' => $discuss,
        'newClarify' => $newClarify
    ]);
}
?>
<div style="margin-top: 20px">
    <?php
    if ($dataProvider->count > 0) {
        echo GridView::widget([
            'layout' => '{items}{pager}',
            'dataProvider' => $dataProvider,
            'options' => ['class' => 'table-responsive', 'style' => 'margin:0 auto;width:50%;min-width:600px'],
            'columns' => [
                [
                    'attribute' => Yii::t('app', 'Announcement'),
                    'value' => function ($model, $key, $index, $column) {
                        return Yii::$app->formatter->asMarkdown($model->content);
                    },
                    'format' => 'html'
                ],
                [
                    'attribute' => 'created_at',
                    'options' => ['width' => '150px'],
                    'format' => 'datetime'
                ]
            ],
        ]);
        echo '<hr>';
    }
    ?>
    <div class="well">
        <?= Yii::t('app','Give the content you need to clarify here.')?>
    </div>

    <?= GridView::widget([
        'dataProvider' => $clarifies,
        'columns' => [
            [
                'attribute' => 'User',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->user->colorname, ['/user/view', 'id' => $model->user->id]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'title',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->title), [
                        '/contest/clarify',
                        'id' => $model->entity_id,
                        'cid' => $model->id
                    ], ['data-pjax' => 0]);
                },
                'format' => 'raw'
            ],
            'created_at',
            'updated_at'
        ]
    ]); ?>

    <div class="well">
        <?php if ($model->getRunStatus() == \app\models\Contest::STATUS_RUNNING): ?>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($newClarify, 'title', [
            'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">" . Yii::t('app', 'Title') . "</span>{input}</div>{error}",
        ])->textInput(['maxlength' => 128, 'autocomplete'=>'off'])->label(false) ?>

        <?= $form->field($newClarify, 'content')->widget('app\widgets\editormd\Editormd'); ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
        <?php else: ?>
        <p><?= Yii::t('app', 'The contest has ended.') ?></p>
        <?php endif; ?>
    </div>
</div>
