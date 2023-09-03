<?php

namespace app\modules\admin\models;

use Yii;
use yii\db\Query;
use yii\base\Model;
use app\models\Solution;

class SettingForm extends Model
{
    public $problem_data_path;

    public function rules()
    {
        return [
            [['problem_data_path'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'problem_data_path' => 'Problem Data Path',
        ];
    }
}
