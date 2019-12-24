<?php
/**
 * Created by PhpStorm.
 * User: tianci
 * Date: 2019/12/24
 * Time: 下午4:01
 */
class WebsocketTest {
    public $server;
    public function __construct() {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
        $this->server->on('open', function (swoole_websocket_server $server, $request) {

            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
//            var_dump($server);
           // var_dump();
            foreach ($server->getClientList() as $fd) {
                $server->push($fd, "this is server");
            }
        });
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->start();
    }
}
new WebsocketTest();