### 1.主从复制
#### 1.1简介
   主从复制即将master节点数据及时，有效地复制到slave中。
**作用**

```text
1、读写分离：master写，slave读
2、负载均衡：slave分担master负载，提高redis服务器并发量和吞吐量
3、故障转移：当master出现问题时，由slave提供服务，实现快速故障转移(哨兵)
4、数据冗余：实现数据热备份，持久化之外的一种数据冗余方式
```
#### 1.2复制工作流程
**总述**

```text
主从复制分为3个阶段：
1、建立连接阶段(准备阶段)
2、数据同步阶段
3、命令传播阶段
```
##### 1.2.1建立连接阶段
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/redis/redis-repl-01.png)
```text
1、设置master的地址和端口，保存master信息
2、建立socket连接
3、发送ping命令（定时器任务）
4、身份验证
5、发送slave端口信息
主从连接成功！slave保存master的地址和端口，master保存slave的端口，之间创建了socket
```
###### 连接方式
```shell
# 方式一：客户端发送命令
slaveof <masterip> <masterport>
# 方式二：启动服务器参数
redis-server --slaveof <masterip> <masterport>
# 方式三：服务器配置
slaveof <masterip> <masterport>
```
###### 授权访问
```shell
# master配置文件设置密码
requirepass <passwd>
# master客户端发送命令设置密码
config set requirepass <passwd>
config get requirepass
# slave客户端发送命令设置密码
auth <passwd>
# slave配置文件设置密码
masterauth <passwd>
# 启动客户端设置密码
redis-cli -a <passwd>
```
##### 1.2.2数据同步阶段
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/redis/redis-repl-02.png)
```text
1、请求同步数据
2、创建RDB文件，同步全量数据
3、恢复RDB文件，恢复全量数据
4、请求部分同步数据
5、恢复部分同步数据
数据同步完成！slave和master完成数据克隆
```
<font color='red'>全量同步(RDB文件) + 部分同步(复制缓冲区中的命令)</font>
###### master说明
```text
1、数据同步应该避开流量高峰，避免master阻塞影响业务正常执行
2、复制缓冲区设置过小，会导致部分复制时发现数据已丢失，必须进行二次全量复制，进入死循环
repl-backlog-size 1mb
```
###### slave说明
```text
1、避免复制时服务器响应阻塞或数据不同步，建议关闭此期间的对外服务
slave-serve-stale-data yes|no
2、数据同步阶段，可以理解master是slave的一个客户端，向slave发送命令
3、多个slave同时对master请求同步，master发送的rdb文件增多，对带宽冲击大，建议错峰
```
###### 运行ID(runid)
```text
1、每台服务器每次运行的身份识别码，一台服务器多次运行生成多个runid
2、随机的40位十六进制字符，如83727f7a0de36b7cd5cfb6b8c72155100ce679a3
3、主从复制中，master首次连接slave时，会将自己runid发送给slave，slave保存此runid
```
###### 复制积压缓冲区
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/redis/redis-repl-03.png)
```text
1、配置参数: repl-backlog-size 1mb
2、偏移量(offset)+字节值
3、master通过不同的offset区分不同slave当前数据传播的差异
4、master记录已发送信息对应的offset
5、slave记录已接受信息对饮的offset
```
###### 复制偏移量(offset)
```text
1、一个数字，描述复制缓冲区中的指令字节位置
2、master复制偏移量：记录发送给所有slave的指令字节对应位置(多个)，发送一次记录一次
3、slave复制偏移量：记录接受master发送过来的指令字节对应位置(一个)，接受一次记录一次
4、作用：对比master与slave的差异，恢复数据使用
```
##### 1.2.3命令传播阶段
![]()
###### 心跳机制
   进入命令传播阶段，master和slave使用心跳机制进行维护，实现双方连接保持在线。
```shell
# 流程
slave: 请求同步 replconf ack offset
master: 回复 +continue offset
slave: 收到 +coutinue,执行bgrewriteaof
```
**master心跳**
```text
1、指令: ping
2、周期：repl-ping-slave-period决定，默认10秒
3、作用：判断slave是否在线
4、查看：info replication。 lag项维持在0或1视为正常
```
**slave心跳**
```text
1、指令：replconf ack {offset}
2、周期：1秒
3、作用：汇报自己的offset，获取最新变更指令； 判断master是否在线
```
当slave多数掉线，或延迟过高时，master将关闭写功能，停止数据同步
```shell
# slave数量少于2，或者所有slave延迟大于10秒时，关闭master写功能
min-slaves-to-write 2
min-slaves-max-lag 10
```
#### 1.3常见问题
##### 频繁全量复制
原因一：master重启runid发生变化，导致全部slave全量复制   
```text
优化：
1、master内使用runid相同的策略创建master_relid变量，并发送给slave
2、master关闭时执行shutdown save，进行RDB持久化，将runid和offset保存到rdb文件
3、master重启后加载rbd文件，恢复数据，runid和offset加载到内存中
作用： master重启后runid不变，保证所有slave认为还是之前的master
```
原因二：复制缓冲区过小，断网后slave的offset越界，触发全量复制
```text
优化：修改复制缓冲区大小 repl-backlog-size
```
##### 频繁的网络中断
现象一：master的cpu占用过高或slave频繁断开连接
```text
优化：设置合理的超时时间，确实是否释放slave 
repl-timeout 60 #超过60秒释放slave
```
现象二：slave与master断开
```text
原因：master发送ping频率较低，master设置较短超时时间，ping丢包
优化：提高ping的频率(秒为单位)
repl-ping-slave-period 10
```
一般建议 repl-timeout最少是repl-ping-slave-period的5-10倍，否则slave很容易判定超时断开
### 2.哨兵
#### 2.1哨兵简介
##### 2.1.1什么是哨兵
   一个分布式系统，用户对主从结构中的每台服务器进行<font color='red'>监控</font>,当出现故障时通过<font color='red'>投票机制选择</font>新的mater并将所有slave连接到新的master。Redis2.8版本开始引入。   
##### 2.1.2作用
```text
1)监控
	不断检查master和slave是否正常。
2)通知(提醒)
	当被监控的服务器出现问题时，向其他(哨兵，客户端)服务器发送通知。
3)自动故障转移
	断开master和slave连接，选择一个slave作为新的master，将其他slave连接到新的master，告知客户端新的服务器地址。
4)配置提供者
	客户端(predis,jedis等)通过哨兵节点+masterName获取主节点信息。
```
#### 2.2哨兵模式搭建
##### 2.2.1一主两从配置
```shell
# 一主(6380)两从(6381,6382)
cd /usr/local/redis/conf
# 6380.conf, 6381.conf, 6382.conf
# 批量替换关键字
sed 's/6381/6382/g' 6381.conf > 6382.conf
```
##### 2.2.2哨兵配置(3个)
```shell
# sentinel-26380.conf, sentinel-26381.conf, sentinel-26382.conf
cd /usr/local/redis
grep -Ev '^$|^#' sentinel.conf > ./conf/sentinel-26380.conf
# 编辑sentinel-26380.conf
port 26380
dir /data/redis
daemonize yes  
logfile "26380.log"
sentinel monitor mymaster 127.0.0.1 6380 2
sentinel down-after-milliseconds mymaster 30000
sentinel parallel-syncs mymaster 1
sentinel failover-timeout mymaster 180000
# 编辑sentinel-26381.conf, sentinel-26382.conf
sed 's/26380/26381/g' sentinel-26380.conf > sentinel-26381.conf
sed 's/26380/26382/g' sentinel-26380.conf > sentinel-26382.conf
```
##### 2.2.3启动主从，哨兵
```shell
# 启动主从
redis-server /usr/local/redis/conf/6380.conf
redis-server /usr/local/redis/conf/6381.conf
redis-server /usr/local/redis/conf/6382.conf
# 启动哨兵
redis-sentinel /usr/local/redis/conf/sentinel-26380.conf
redis-sentinel /usr/local/redis/conf/sentinel-26381.conf
redis-sentinel /usr/local/redis/conf/sentinel-26382.conf
```
##### 2.2.4验证
```shell
# 连接sentinel服务器
redis-cli -p 26380
info sentinel 指令有下面信息则说明哨兵搭建成功
master0:name=mymaster,status=ok,address=127.0.0.1:6380,slaves=2,sentinels=3
```
##### 2.2.5故障转移
```shell
ps -ef | grep redis
# 测试关闭从节点
kill -9 19602
+sdown slave 127.0.0.1:6382 127.0.0.1 6382 @ mymaster 127.0.0.1 6380
# 测试关闭主节点
redis-server /usr/local/redis/conf/6382.conf
+reboot slave 127.0.0.1:6382 127.0.0.1 6382 @ mymaster 127.0.0.1 6380
-sdown slave 127.0.0.1:6382 127.0.0.1 6382 @ mymaster 127.0.0.1 6380
kill -9 19586
+sdown master mymaster 127.0.0.1 6380 (主观下线)
+odown master mymaster 127.0.0.1 6380 #quorum 2/2 (可观下线)
+new-epoch 1 (进行第一轮选举，选举执行故障转移的sentinel,执行故障转移)
+switch-master mymaster 127.0.0.1 6380 127.0.0.1 6382 (选举6382作为新的master)
+slave slave 127.0.0.1:6381 127.0.0.1 6381 @ mymaster 127.0.0.1 6382 (6381连接新的master)
+slave slave 127.0.0.1:6380 127.0.0.1 6380 @ mymaster 127.0.0.1 6382 (6380连接新的master)
+sdown slave 127.0.0.1:6380 127.0.0.1 6380 @ mymaster 127.0.0.1 6382 (6380下线)
```
##### 2.2.6下线主机6380重新启动后，作为从节点
```shell
# 重新启动6380
redis-server /usr/local/redis/conf/6380.conf
-sdown slave 127.0.0.1:6380 127.0.0.1 6380 @ mymaster 127.0.0.1 6382
```
#### 2.3哨兵工作原理
**主从切换**
```text
哨兵在主从切换过程中经历三个阶段：
1）监控
2）通知
3）故障转移
```
##### 2.3.1监控
*同步各个节点的状态信息*
```text
1、获取各个sentinel的状态: ping，publish，subcribe
2、获取master的状态：info(cmd连接)
	master的runid,role,各个slave的详细信息
3、获取slave的状态：info(cmd连接)
	slave的runid，role，master_host|port,offset...
```
##### 2.3.2通知
```text
1、各个sentinel节点形成网状结构，相互同步消息
2、sentinel向主从节点发送publish sentinel:hello获取主从节点状态
3、将获取到主从节点状态同步到sentinel网状结构的其他sentinel节点
```
##### 2.3.3故障转移
```text
1、sentinelA向master发送hello，长时间未收到回复，标记master挂了(sri_s_down)
2、其他sentinel节点收到master挂了的消息后，同时向master发送hello，发现确实挂了，标记master(sri_o_down)
3、sentinel通过‘投票机制’选举出执行故障转移的sentinel-领头者(sentinelA)
4、sentinelA从服务器列表挑选新的master
	1）在线的，排除响应慢的，排除与原master断开时间长的
	2）优先原则：优先级()-> offset大 -> runid小
5、sentinelA发送指令
	1)向新的master发送slaveof no one
	2)向其他slave发送slaveof 新的master host port
```
**总结**
```text
1、监控
	同步信息
2、通知
	保持联通
3、故障转移
	1)发现问题
	2)竞选负责人
	3)优选新master
	4)新master上任，其他slave切换master，原master作为slave故障恢复后连接
```
```shell
# 杀掉所有redis-server进程
kill -9 $(ps -ef | grep redis-server | grep -Ev 'usr|grep' | awk '{print $2}')
```
### 3.集群
### 4.企业级解决方案