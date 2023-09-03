<?php

use yii\helpers\Html;

$this->title = Html::encode($model->title);
$this->params['model'] = $model;
?>
<div class="contest-editorial">
    <div style="padding: 50px">
        <?php
        if ($model->editorial != NULL) {
            echo Yii::$app->formatter->asMarkdown($model->editorial);
        } else {
            echo 'No editorial yet. Please come back later';
        }
        ?>
    </div>
</div>
