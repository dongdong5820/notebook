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
作用：  
  满足限定时间second范围内key的变化数量达到指定数量changes即进行持久化  
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
注意：  
  save配置中对于second与changes设置通常具有互补对应关系，尽量不要设置成包含关系；  
  save配置启动后执行的是bgsave操作  
##### 2.2.4 三种启动方式对比
| 方式           | save指令 | bgsave指令(save配置) |
| -------------- | -------- | -------------------- |
| 读写           | 同步     | 异步                 |
| 阻塞客户端指令 | 是       | 否                   |
| 额外内存消耗   | 否       | 是                   |
| 启动新进程     | 否       | 是                   |
##### 2.2.5 RDB优缺点
- 优点：
	- 压缩的二进制文件，存储效率高
	- 保存的是redis在某个时间节点的快照，非常适合于数据备份，全量复制
	- RDB恢复速度比AOF快很多
	- 应用：每X小时执行bgsave备份，并将RDB文件拷贝至远程服务器，用户灾难恢复
- 缺点：
	- 无法实施持久化，丢失数据可能性大 
	- 存储数据量大，效率低
	- 大数据量下IO性能较低
	- 基于fork创建子进程，产生内存额外消耗
	- redis众多版本对rdb文件格式未统一，数据格式不兼容
#### 2.3 AOF
##### 2.3.1 简介及策略基本操作
**AOF概念**  
  以独立日志的方式记录每次写命令。记录数据产生的过程。解决了redis数据持久化时效性问题。  
**AOF写数据过程**
  写命令 --> AOF写命令刷新缓冲区-->.aof文件  
**三种写策略(appendfsync)**
- always(每次)： 每次写入操作同步到AOF文件
- everysec(每秒)： 每秒将缓冲区的指令同步到AOF文件中。最多丢失1秒的数据
- no(系统控制)：由操作系统控制同步到AOF文件的周期
**配置**
```shell
appendonly yes|no  # 默认不开启
appendfsync always|everysec|no # 同步策略
appendfilename filename aof文件名
```
##### 2.3.2 重写概念与命令

##### 2.3.3 自动重写
#### 2.4 RDB和AOF区别

#### 2.5 持久化应用场景
### 3. 事务

### 4. 删除策略

### 5. redis.conf

### 6. 高级数据类型

### 7. redis集群

