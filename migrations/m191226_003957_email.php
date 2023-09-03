<?php

use yii\db\Migration;
use yii\db\Schema;

class m191226_003957_email extends Migration
{

    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'is_verify_email', $this->smallInteger()->notNull()->defaultValue(0));
        $this->addColumn('{{%user}}', 'verification_token', $this->string()->notNull()->defaultValue(''));
        $this->insert('{{%setting}}', ['key' => 'passwordResetTokenExpire', 'value' => '7200']);
        $this->insert('{{%setting}}', ['key' => 'mustVerifyEmail', 'value' => '0']);
        $this->insert('{{%setting}}', ['key' => 'emailHost', 'value' => 'smtp.exmail.chuyenlethanhtong.edu.vn']);
        $this->insert('{{%setting}}', ['key' => 'emailUsername', 'value' => 'no-reply@chuyenlethanhtong.edu.vn']);
        $this->insert('{{%setting}}', ['key' => 'emailPassword', 'value' => '8hVeA6LN4LPqwHei']);
        $this->insert('{{%setting}}', ['key' => 'emailPort', 'value' => '465']);
        $this->insert('{{%setting}}', ['key' => 'emailEncryption', 'value' => 'ssl']);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'is_verify_email');
        $this->dropColumn('{{%user}}', 'verification_token');
        $this->delete('{{%setting}}', ['key' => 'passwordResetTokenExpire']);
        $this->delete('{{%setting}}', ['key' => 'mustVerifyEmail']);
        $this->delete('{{%setting}}', ['key' => 'emailHost']);
        $this->delete('{{%setting}}', ['key' => 'emailUsername']);
        $this->delete('{{%setting}}', ['key' => 'emailPassword']);
        $this->delete('{{%setting}}', ['key' => 'emailPort']);
        $this->delete('{{%setting}}', ['key' => 'emailEncryption']);
    }
}
