<?php
/*
 * socket编程-服务端，select模式
 * 1、运行服务端 php socket_select.php
 * 2、客户端终端使用 telnet ip port
 * 3、输入字符串(如 hello world | quit)
 * 4、查看服务器监听端口 netstat -anl | grep xxx
 */

// 创建socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$socket) die('create server fail:' . socket_strerror(socket_last_error()) . "\n");

// 绑定
$ip = '192.168.10.10';
$port = 9201;
$ret = socket_bind($socket,$ip,$port);
if (!$ret) die('bind server fail:' . socket_strerror(socket_last_error()) . "\n");

// 监听
$ret = socket_listen($socket,2);
if (!$ret) die('listen server fail:' . socket_strerror(socket_last_error()) . "\n");
echo "waiting client...\n";

// 阻塞等待客户端连接
$clients = [$socket];
$recvs = [];
while(1) {
    // 拷贝一份，socket_select会修改 $read
    $read = $clients;
    $ret = @socket_select($read, $write = NULL, $except = NULL, 0);
    if ($ret < 1) {
        continue;
    }

    foreach ($read as $k=>$client) {
        if ($socket === $client) {
            // 若是新连接，阻塞等待客户端连接
            $conn = socket_accept($socket);
            if (!$conn) {
                die('accept server fail:' . socket_strerror(socket_last_error()) . "\n");
                break;
            }
            $clients[] = $conn;

            echo "client connect success. fd:" . $conn . "\n";

            // 获取客户端IP地址
            socket_getpeername($conn,$addr,$port);
            echo "client addr: $addr, port: $port\n";

            // 获取服务端Ip地址
            socket_getsockname($conn,$addr,$port);
            echo "server addr: $addr, port: $port\n";

            echo "total:" . (count($clients) -1) . " client\n";
        } else {
            // 注意：后面使用 $client 而不是 $conn
            if (!isset($recvs[$k]))  $recvs[$k] = ''; // 兼容可能没有值的情况

            // 每次读取100byte
            $buffer = socket_read($client, 100);
            if (false === $buffer || '' === $buffer) {
                echo "line:" . __LINE__ . ",client closed.\n";
                // 删除掉 $clients 中的元素
                unset($clients[array_search($client, $clients)]);
                // 关闭本次连接
                socket_close($client);
                break;
            }

            // 解析单次消息
            $pos = strpos($buffer, "\n");
            if (false === $pos) {
                // 消息未读取完，继续读取
                $recvs[$k] .= $buffer;
            } else {
                // 消息读取完毕，去除换行符及空格
                $recvs[$k] .= trim(substr($buffer, 0, $pos+1));

                // 客户端主动断开
                if ('quit' === $recvs[$k]) {
                    echo "line:" . __LINE__ . ",client closed.\n";
                    // 删除掉 $clients 中的元素
                    unset($clients[array_search($client, $clients)]);
                    // 关闭本次连接
                    socket_close($client);
                    break;
                }

                echo "recv from client:" . $recvs[$k] ." \n";
                // 回复消息
                socket_write($client, "hello: " . $recvs[$k] . "\n");
                // 清空消息，准备下一次接受
                $recvs[$k] = '';
            }
        }
    }
}

// 关闭socket
socket_close($socket);
