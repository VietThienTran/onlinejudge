<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use app\models\Contest;

$this->title = $model->title;
$contest_id = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];
?>
<h1><?= Html::encode($model->title) ?></h1>

<?php Modal::begin([
    'header' => '<h2>' . Yii::t('app', 'Add participating user') . '</h2>',
    'toggleButton' => ['label' => Yii::t('app', 'Add participating user'), 'class' => 'btn btn-success'],
]);?>
<?= Html::beginForm(['contest/register', 'id' => $model->id]) ?>
    <div class="form-group">
        <?= Html::label(Yii::t('app', 'User'), 'user') ?>
        <?= Html::textarea('user', '',['class' => 'form-control', 'rows' => 10]) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>
<?php Modal::end(); ?>

<?php if ($model->scenario == Contest::SCENARIO_OFFLINE): ?>
    <?php Modal::begin([
        'header' => '<h2>' . Yii::t('app', 'Generate user for the contest') . '</h2>',
        'toggleButton' => ['label' => Yii::t('app', 'Generate user for the contest'), 'class' => 'btn btn-success'],
    ]);?>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($generatorForm, 'prefix')->textInput([
                'maxlength' => true, 'value' => 'c' . $model->id . 'user', 'disabled' => true
        ]) ?>

        <?= $form->field($generatorForm, 'team_number')->textInput(['maxlength' => true, 'value' => '50']) ?>

        <?= $form->field($generatorForm, 'names')->textarea(['rows' => 10]) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Generate'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    <?php Modal::end(); ?>
    <?= Html::a(Yii::t('app', 'Copy these accounts to distribute'), ['contest/printuser', 'id' => $model->id], ['class' => 'btn btn-default', 'target' => '_blank']) ?>
<?php endif; ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => Yii::t('app', 'Username'),
            'value' => function ($model, $key, $index, $column) {
                return Html::a($model->user->username, ['/user/view', 'id' => $model->user->id]);
            },
            'format' => 'raw'
        ],
        [
            'attribute' => Yii::t('app', 'Nickname'),
            'value' => function ($model, $key, $index, $column) {
                return Html::a($model->user->nickname, ['/user/view', 'id' => $model->user->id]);
            },
            'format' => 'raw'
        ],
        [
            'attribute' => 'user_password',
            'value' => function ($contestUser, $key, $index, $column) use ($model) {
                if ($model->scenario == Contest::SCENARIO_OFFLINE) {
                    return $contestUser->user_password;
                } else {
                    return 'Unable to provide password for online contest';
                }
            },
            'format' => 'raw',
            'visible' => $model->scenario == Contest::SCENARIO_OFFLINE
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model, $key) use ($contest_id) {
                    $options = [
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => 'Are you sure you want to delete it?',
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ];
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['contest/register', 'id' => $contest_id, 'uid' => $model->user->id]), $options);
                },
            ]
        ],
    ],
]); ?>
