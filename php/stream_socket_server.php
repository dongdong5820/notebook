<?php
/*
 * socket编程-服务端，stream
 * 1、运行服务端 php stream_socket_server.php
 * 2、客户端终端使用 telnet ip port
 * 3、输入字符串(如 hello world | quit)
 */
// 创建socket
$socket = stream_socket_server("tcp://192.168.10.10:9201", $errno, $errstr, STREAM_SERVER_LISTEN);
if (false === $socket) die("$errstr($errno)");

while (1) {
    echo "waiting client...\n";

    // 阻塞等待客户端连接
    $conn = stream_socket_accept($socket, -1);
    if (false === $conn) {
        continue;
    }

    echo "new Client! fd:" . $conn . "\n";
    while (1) {
        // 读取消息
        $buffer = fread($conn, 1024);
        // 非正常关闭
        if (false === $buffer) {
            echo "fread fail\n";
            break;
        }
        // 去除首尾空格
        $msg = trim($buffer, "\n\r");
        // 强制关闭
        if ('quit' === $msg) {
            echo "line:" . __LINE__ . ",client closed.\n";
            fclose($conn);
            break;
        }

        echo "recv from client:" . $msg ." \n";
        fwirte($conn, "hello: " . $msg . "\n");
    }
}

// 关闭socket
fclose($socket);
