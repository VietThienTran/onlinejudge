<?php
use Workerman\Worker;
use PHPSocketIO\SocketIO;

include __DIR__ . '/vendor/autoload.php';

$uidConnectionMap = array();

$last_online_count = 0;

$last_online_page_count = 0;

$sender_io = new SocketIO(2120);

$sender_io->on('connection', function($socket){
    $socket->on('login', function ($uid)use($socket){
        global $uidConnectionMap, $last_online_count, $last_online_page_count;
        if(isset($socket->uid)){
            return;
        }
        $uid = (string)$uid;
        if (!isset($uidConnectionMap[$uid])) {
            $uidConnectionMap[$uid] = 0;
        }
        ++$uidConnectionMap[$uid];
        $socket->join($uid);
        $socket->uid = $uid;
    });

    $socket->on('disconnect', function () use($socket) {
        if(!isset($socket->uid)) {
            return;
        }
        global $uidConnectionMap, $sender_io;
        if(--$uidConnectionMap[$socket->uid] <= 0) {
            unset($uidConnectionMap[$socket->uid]);
        }
    });
});

$sender_io->on('workerStart', function(){
    $inner_http_worker = new Worker('Text://0.0.0.0:2121');
    $inner_http_worker->onMessage = function ($http_connection, $buffer) {
        global $uidConnectionMap;
        global $sender_io;
        $data = json_decode($buffer, true);
        if (isset($data['uid'])) {
            $sender_io->to($data['uid'])->emit('msg', $data['content']);
        } else {
            $sender_io->emit('msg', $data['content']);
        }
        return $http_connection->send('ok');
    };
    $inner_http_worker->listen();
});

Worker::runAll();
