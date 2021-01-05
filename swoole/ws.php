<?php

use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Illuminate\Support\Facades\Redis;

$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    $user_id = $request->get['user_id'];
    if(!$user_id){
        $response->end();
        return false;
    }
    ##将user_id加入在线集合中
    Redis::sadd("SWOOLE:ONLINE_USER_ID", (string)$user_id);
    ##将user_id和fd绑定
    Redis::hset("SWOOLE:USER_ID_BIND_FD", (string)$user_id, (string)$request->fd);
    ##将fd和user_id绑定
    Redis::hset("SWOOLE:FD_BIND_USER_ID", (string)$request->fd, (string)$user_id);

    $key = base64_encode(
        sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        )
    );

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // WebSocket connection to 'ws://127.0.0.1:9502/'
    // failed: Error during WebSocket handshake:
    // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }
    $response->status(101);
    $response->end();
});

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});

$server->on('close', function ($ser, $fd) {
    ##通过fd查找user_id
    $user_id = Redis::hget("SWOOLE:FD_BIND_USER_ID", (string)$fd);
    if($user_id){
        ##将user_id从在线集合中删除
        Redis::srem("SWOOLE:ONLINE_USER_ID", (string)$user_id);
        ##将user_id和fd绑定
        Redis::hdel("SWOOLE:USER_ID_BIND_FD", (string)$user_id);
        ##将fd和user_id绑定
        Redis::hset("SWOOLE:FD_BIND_USER_ID", (string)$fd);
    }

    echo "client {$fd} closed\n";
});

$server->start();


