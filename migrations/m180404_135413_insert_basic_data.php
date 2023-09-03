<?php

use app\migrations\BaseMigration;

class m180404_135413_insert_basic_data extends BaseMigration
{
    public function up()
    {
        $time = new \yii\db\Expression('NOW()');

        $this->insert('{{%problem}}', [
            'id' => 1000,
            'title' => 'A+B Problem',
            'description' => '<p>Cho 2 số nguyên. Tính tổng của 2 số đó</p>',
            'input' => '<p>Một dòng duy nhất gồm 2 số nguyên: $ a, b (0 \\leq a, b \\leq 100)$</p>',
            'output' => '<p>Một dòng duy nhất là tổng của 2 số nguyên $a, b$ </p>',
            'sample_input' => "a:3:{i:0;s:3:\"1 2\";i:1;s:0:\"\";i:2;s:0:\"\";}",
            'sample_output' => "a:3:{i:0;s:1:\"3\";i:1;s:0:\"\";i:2;s:0:\"\";}",
            'spj' => 0,
            'hint' => "<p>Tham khảo hướng dẫn thao tác với dữ liệu đầu vào/đầu ra tại <a href='https://greenhat1998.github.io'>Online Judge Document</a></p>",
            'time_limit' => 1,
            'memory_limit' => 128,
            'status' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $this->insert('{{%problem}}', [
            'id' => 1001,
            'title' => 'Tổng các số tự nhiên',
            'description' => '<p>Cho số tự nhiên $n$，tính tổng của dãy số $1 + 2 + ... + n$.</p>',
            'input' => '<p>Gồm nhiều dòng, mỗi dòng gồm 1 số tự nhiên $n (1 \\le n \\le 1000)$，kết thúc bằng dấu xuống dòng <code>EOF</code>.</p>',
            'output' => '<p>Với mỗi số tự nhiên $n$, in ra tổng $1 + 2 + ... + n$.</p>',
            'sample_input' => "a:3:{i:0;s:7:\"10\r\n100\";i:1;N;i:2;N;}",
            'sample_output' => "a:3:{i:0;s:8:\"55\r\n5050\";i:1;N;i:2;N;}",
            'spj' => 0,
            'hint' => "",
            'time_limit' => 1,
            'memory_limit' => 128,
            'status' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        $this->insert('{{%problem}}', [
            'id' => 1002,
            'title' => 'Leapyear',
            'description' => '<p>Cho một năm, hãy xác định xem đó có phải là năm nhuận hay không.</p>',
            'input' => '<p>- Dòng đầu tiên là  một số nguyên $t$ là số testcase.<br>- $t$ dòng tiếp theo, mỗi dòng là một số nguyên $n (1000 \\leq n \\leq 4000)$ là năm cần kiểm tra</p>',
            'output' => '<p>Gồm $t$ dòng, với mỗi số tự nhiên $n$, in ra <code>Yes</code> nếu đó là năm nhuận，ngược lại in ra <code>No</code>.</p>',
            'sample_input' => "a:3:{i:0;s:31:\"5\r\n2016\r\n2017\r\n2018\r\n2019\r\n2020\";i:1;N;i:2;N;}",
            'sample_output' => "a:3:{i:0;s:20:\"Yes\r\nNo\r\nNo\r\nNo\r\nYes\";i:1;N;i:2;N;}",
            'spj' => 0,
            'hint' =>  "",
            'time_limit' => 1,
            'memory_limit' => 128,
            'status' => 1,
            'created_at' => $time,
            'updated_at' => $time
        ]);
    }

    public function down()
    {
        $this->delete('{{%problem}}', ['id' => 1000]);
        $this->delete('{{%problem}}', ['id' => 1001]);
        $this->delete('{{%problem}}', ['id' => 1002]);
    }
}
