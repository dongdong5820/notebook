###  1. redis安装
linux下载redis
```shell
cd /usr/local/package
wget http://download.redis.io/releases/redis-3.2.5.tar.gz
```
解压缩并剪切到相应目录
```shell
tar -xzvf redis-3.2.5.tar.gz
mv redis-3.2.5 /usr/local/redis
```
切换到相应目录并安装
```shell
cd /usr/local/redis/
make && make install
```
redis目录结构
```text
redis.conf  配置文件
src 里面有 redis-server redis-cli redis-sentinel redis-check-aof redis-check-rdb等二进制文件 
```
复制redis.conf内容到conf/6380.conf，修改之后再启动
```shell
mkdir conf
cat redis.conf | grep -v '#' | grep -v '^$' > ./conf/6380.conf
mkdir -p /data/redis
redis-server /usr/local/redis/conf/6380.conf
```
其中6380.conf文件内容
```shell
bind 127.0.0.1
port 6380
daemonize yes
pidfile /var/run/redis_6380.pid
dir /data/redis
logfile "6380.log"
```
查看是否启动，并用客户端登录
```shell
ps -ef | grep redis
redis-cli -p 6380
```
### 2. 持久化
#### 2.1 持久化简介
- 什么是持久化  
	利用永久性存储介质将数据进行保存，在特定时间将保存的数据进行恢复的机制
- 为什么持久化  
	防止数据意外丢失，确保数据安全
- 持久化方式  
	- 数据（快照）RDB
	- 过程（日志）AOF
#### 2.2 RDB
##### 2.2.1 save命令
作用：手动执行一次保存一次全量数据  
save指令相关配置：
- dbfilename dump-6380.rdb  
	设置本地数据库文件名，默认为dump.rdb
- rdbcompression yes  
	本地数据库文件是否压缩数据，默认为yes，采用LZF压缩
- rdbchecksum yes  
	是否进行rdb文件格式校验，写文件和读文件均进行    

工作原理：
前段线程执行，阻塞当前redis服务器，直到当前rdb过程完成为止，有可能会造成长时间阻塞。<font color='red'>线上一般不采用</font>。
##### 2.2.2 bgsave命令
作用：手动启动后台保存操作，但不是立即执行。 <font color='red'>后台执行</font>   
工作原理：

```text
1、发送指令bgsave
2、返回消息(Backgroud saving started)，调用系统fork函数生成子进程
3、子进程去创建rdb文件
```
PS：bgsave命令是针对save阻塞问题做的优化。redis内部所有涉及到rdb操作都采用bgsave方式  
相关配置：

```shell
# 后台存储过程中如果出现错误，是否终止保存操作
stop-writes-on-bgsave-error yes
```
##### 2.2.3 save配置
<font color='red'>自动执行</font>,redis服务器发起指令(基于条件)  
配置：
```shell
save second changes
```
作用：满足限定时间second范围内key的变化数量达到指定数量changes即进行持久化  
参数：  
```text
second： 监控时间范围
changes：监控key的变化量
```
举例：
```shell
save 900 1
save 300 10
save 60 10000
```

#### 2.3 AOF
#### 2.4 RDB和AOF区别
#### 2.5 持久化应用场景
### 3. 事务

### 4. 删除策略

### 5. redis.conf

### 6. 高级数据类型

### 7. redis集群

