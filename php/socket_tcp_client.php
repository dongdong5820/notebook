<?php
/**
 * socket编程-客户端
 */
// 创建连接
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$socket) die('create server fail:' . socket_strerror(socket_last_error()) . "\n");

// 连接server(开启一个套接字连接)
$ip = '192.168.10.10';
$port = 9201;
$ret = socket_connect($socket,$ip,$port);
if (!$ret) die('client connect fail:' . socket_strerror(socket_last_error()) . "\n");

// 发送消息
$buffer = "hello, I am client\n";
socket_write($socket,$buffer);

// 读取server端消息
$buffer = socket_read($socket, 1024);
echo "from server: $buffer \n";

// 关闭socket
socket_close($socket);

