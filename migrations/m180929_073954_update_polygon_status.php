<?php

use yii\db\Migration;

class m180929_073954_update_polygon_status extends Migration
{

    public function safeUp()
    {
        $this->addColumn('{{polygon_status}}', 'created_by', 'INT NOT NULL');
        $this->addColumn('{{polygon_status}}', 'language', 'SMALLINT NULL DEFAULT NULL');
        $this->addColumn('{{polygon_status}}', 'source', 'TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL');
    }

    public function safeDown()
    {
        $this->dropColumn('{{polygon_status}}', 'created_by');
        $this->dropColumn('{{polygon_status}}', 'language');
        $this->dropColumn('{{polygon_status}}', 'source');
    }

}
