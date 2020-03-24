<?php
/**
 * socket编程-服务端，步骤
 * 1、运行服务端 php socket_tcp_server.php
 * 2、客户端终端使用 telnet ip port
 * 3、输入字符串(如 hello world | quit)
 */

/*
 * 创建一个套接字(通讯节点)
 * domain:可用的地址/协议; AF_INET(ipv4),AF_INET6(ipv6),AF_UNIX(本地通讯协议)
 * type:套接字类型; SOCK_STREAM(tcp协议基于此流式套接字), SOCK_DGRAM(udp协议基于此数据报文套接字)
 * protocol:常见协议; SOL_TCP 和 SOL_UDP
*/
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
while(1) {
    $conn = socket_accept($socket);
    if (!$conn) {
        echo "accept server fail:" . socket_strerror(socket_last_error()) . "\n";
        break;
    }

    echo "client connect success.\n";
    parseRecv($conn);
}

// 关闭socket
socket_close($socket);

/**
 * 解析客户端消息(协议:换行符\n)
 * @param $conn
 */
function parseRecv($conn)
{
    // 实际接收到的消息
    $recv = '';
    // 循环读取消息
    while(1) {
        // 每次读取100byte
        $buffer = socket_read($conn, 100);
        if (false === $buffer || '' === $buffer) {
            echo "line:" . __LINE__ . ",client closed.\n";
            socket_close($conn);
            break;
        }

        // 解析单次消息，协议:换行符
        $pos = strpos($buffer, "\n");
        if (false === $pos) {
            // 消息未读取完，继续读取
            $recv .= $buffer;
        } else {
            // 消息读取完，去除换行符及空格
            $recv .= trim(substr($buffer, 0, $pos+1));

            // 客户端主动断开连接
            if ('quit' == $recv) {
                echo "line:" . __LINE__ . ",client closed.\n";
                socket_close($conn);
                break;
            }

            echo "recv: $recv \n";
            // 发送消息
            socket_write($conn, "$recv \n");

            // 清空消息，准备下一次接收
            $recv = '';
        }
    }
}
