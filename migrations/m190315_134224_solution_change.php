<?php

use yii\db\Migration;

class m190315_134224_solution_change extends Migration
{

    public function safeUp()
    {
        $this->renameColumn('{{%solution_info}}', 'error', 'run_info');
        $this->addColumn('{{%solution}}', 'score', 'TINYINT UNSIGNED NOT NULL DEFAULT \'0\' AFTER pass_info');
    }

    public function safeDown()
    {
        $this->renameColumn('{{%solution_info}}', 'run_info', 'error');
        $this->dropColumn('{{%solution}}', 'score');
    }
}
