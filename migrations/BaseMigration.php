<?php

namespace app\migrations;

use yii\db\Migration;

class BaseMigration extends Migration
{
    protected function getTableOptions()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {

            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        return $tableOptions;
    }
}
