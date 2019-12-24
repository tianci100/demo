<?php
/**
 * Created by PhpStorm.
 * User: tianci
 * Date: 2019/12/18
 * Time: 上午10:37
 */
class WebSocket {
    public $socket = null;
    public $sockets = [];
    public $users = [];
    public function __construct()
    {
        $this->socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        $this->sockets = [$this->socket]; //
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket,'127.0.0.1',6768);
        socket_listen($this->socket,8);
    }
    public function run() {
        echo '开始';
        while (1) {
            $a = $b= null;
            $sockets = $this->sockets;
            $read_num = socket_select($sockets, $a, $b, NULL);
            if (false === $read_num) {
                echo 'socket没数字';
                return;
            }
            foreach ($sockets as $socket) {
                if($socket == $this->socket) {
                   $m_socket = socket_accept($this->socket);
                   $key = uniqid();
                   $this->sockets[]= $m_socket;
                   $this->users[(int)$m_socket] = [
                     'user'=>$m_socket,
                     'name'=>$key,
                     'type'=>false
                   ];
                    echo'1';

                }else {
                    echo '2';
//                    $buffer='';
                    $bytes = socket_recv($socket,$buffer,2048,0);
                    if($bytes ==0) {
                        $res_msg = json_encode(['code'=>10002,'msg'=>$this->users[(int)$socket]['name']]);
                        unset($this->users[(int)$socket]);
//                        socket_close($socket);
                    }else {
                        if(!$this->users[(int)$socket]['type']){
                            $this->handShake($socket, $buffer);
                            continue;
                        }else {
                            $msg = $this->msg_decode($buffer);
                            echo $msg."\n";
                        }
                    }
                }
            }
        }
    }
    public function handShake($socket,$buffer){
        $key = '';
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $buffer, $match)) {
            $key = $match[1];
        }
        $new_key = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
        $new_message .= "Upgrade: websocket\r\n";
        $new_message .= "Sec-WebSocket-Version: 13\r\n";
        $new_message .= "Connection: Upgrade\r\n";
        $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
        socket_write($socket, $new_message, strlen($new_message));
        $this->users[(int)$socket]['type'] = true;
        echo '握手成功';
    }
    private function msg_decode( $buffer )
    {
        $len = $masks = $data = $decoded = null;
        $len = ord($buffer[1]) & 127;
        if ($len === 126) {
            $masks = substr($buffer, 4, 4);
            $data = substr($buffer, 8);
        }
        else if ($len === 127) {
            $masks = substr($buffer, 10, 4);
            $data = substr($buffer, 14);
        }
        else {
            $masks = substr($buffer, 2, 4);
            $data = substr($buffer, 6);
        }
        for ($index = 0; $index < strlen($data); $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

}
$socket = new WebSocket();
$socket->run();




//while (1){
//
//        // 向客户端传递一个信息数据
//        // 从客户端获取得的数据
//   echo 'a';
//    if(!$buffer) {
//        $connection = @socket_accept($socket);
//        $bytes = @socket_recv($connection, $data, 2048, 0);
//        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match)) {
//            $key = $match[1];
//        }
//        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
//        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
//            "Upgrade: websocket\r\n" .
//            "Connection: Upgrade\r\n" .
//            "Sec-WebSocket-Accept: " . $acceptKey . "\r\n\r\n";
//        // 写入socket
//        socket_write($connection, $upgrade, strlen($upgrade));
//        echo '链接成功';
//        $buffer = true;
//    }else {
//        printf("echo: " . $data . "\n");
//        $bytes = @socket_recv($connection, $data, 2048, 0);
//        $s = json_encode(['data'=>'666']);
//        $a = socket_write($connection, $s,strlen($s));
//
//    }
//}