<?php

use yii\db\Migration;

class m190519_024551_problem_solution extends Migration
{

    public function safeUp()
    {
        $this->addColumn('{{%problem}}', 'solution', 'TEXT AFTER tags');
        $this->addColumn('{{%polygon_problem}}', 'solution', 'TEXT AFTER tags');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%problem}}', 'solution');
        $this->dropColumn('{{%polygon_problem}}', 'solution');
    }
}
