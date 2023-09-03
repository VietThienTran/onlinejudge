<?php

use yii\db\Migration;

class m181226_104846_fix_contest_announcement extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropPrimaryKey('pk_contest_id', 'contest_announcement');
        $this->addColumn('contest_announcement', 'id','INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        $this->createIndex('pk_contest_id', 'contest_announcement', 'contest_id');
    }

    public function safeDown()
    {
        $this->dropColumn('contest_announcement', 'id');
        $this->dropIndex('pk_contest_id', 'contest_announcement');
        $this->addPrimaryKey('pk_contest_id', 'contest_announcement', 'contest_id');
    }

}
