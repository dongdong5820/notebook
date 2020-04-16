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

```shell
# 设置本地数据库文件名，默认为dump.rdb
dbfilename dump-6380.rdb
# 本地数据库文件是否压缩数据，默认为yes，采用LZF压缩
rdbcompression yes
# 是否进行rdb文件格式校验，写和读文件均进行
rdbchecksum yes
```
工作原理：
   前段线程执行，阻塞当前redis服务器，直到当前rdb过程完成为止，有可能造成长时间阻塞。<font color='red'>线上一般不采用</font>。
##### 2.2.2 bgsave命令
作用：手动启动后台保存操作，不立即执行。 <font color='red'>后台执行</font>   
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
# 满足限定时间second秒内key的变化数量达到指定changes个数即进行aof持久化
save second changes
```
参数：  
```text
second： 监控时间范围
changes：监控key的变化个数
```
举例：
```shell
save 900 1
save 300 10
save 60 10000
```
注意：  
  save配置中对于second与changes设置通常具有互补对应关系，尽量不要设置成包含关系；save配置启动后执行的是bgsave操作  

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
##### 2.3.1 写策略
**AOF持久化概念**  
   以独立日志的方式记录每次写命令。记录数据产生的过程。解决redis数据持久化时效性问题。 
**AOF写数据过程**
  写命令 --> AOF写命令刷新缓冲区-->.aof文件  
**三种写策略(appendfsync)**
```text
always(每次)： 每次写入操作同步到AOF文件
everysec(每秒)： 每秒将缓冲区的指令同步到AOF文件中。最多丢失1秒的数据
no(系统控制)：由操作系统控制同步到AOF文件的周期
```
**AOF写配置**
```shell
appendonly yes|no  # 默认不开启
appendfsync always|everysec|no # 同步策略
appendfilename filename #aof文件名
```
##### 2.3.2 AOF重写
**简介**  
   随着命令不断写入aof，文件越来越大，为了解决这个问题，redis引入了aof重写机制压缩文件体积。对同一个数据的若干条命令执行结果转化成最终结果数据对应的指令进行记录。<font color='red'>文件追加方式</font>
**作用**
```text
1、降低磁盘占用量，提高磁盘利用率
2、提高持久化效率，降低持久化写时间，提高IO性能
3、数据恢复用时，提高数据恢复效率
```
**重写规则**
```text
1、进程内已超时的数据不再写入文件
2、忽略无效指令。重写时使用进程内的数据直接生成，aof文件只保留最终数据的写入命令
3、对同一数据的多条写命令合并为一条命令(如lpush，hset等的合并)
	为防止数据量过大造成客户端缓冲区溢出，对list、set、hash、zset等类型，每条指令最多写入64个元素
```
**重写方式**
- 手动重写
```redis
bgrewriteaof
```
bgrewriteaof工作原理：
```text
1、发送指令bgrewriteaof
2、返回消息： Background append only file rewriting started
3、后台调用fork函数生成子进程，重写aof文件，完成返回消息(日志中可查看Background AOF rewrite finished successfully) 
```
- 自动重写
```shell
auto-aof-rewrite-min-size size
auto-aof-rewrite-percentage percentage
```
**自动重写详解**
1、触发条件设置

```sehl
auto-aof-rewrite-min-size 64mb
auto-aof-rewrite-percentage 100
```
​    redis会在aof文件比上次完成重写后aof文件的容量大至少100%，且aof文件容量大于64mb时开启一个`bgrewriteaof`进程。
2、触发比对参数(运行指令`info persistence`可获取详情)
```shell
aof_current_size # 当前aof文件大小
aof_base_size # 上一次完成重写后aof文件大小
```
3、触发条件(同时满足)
```shell
aof_current_size > auto-aof-rewrite-min-size
(aof_current_size - aof_base_size) / aof_base_size >= auto-aof-rewrite-percentage
```
**重写流程**
```text
1、执行aof重写请求。如果当前进程正在执行bgsave操作，重写命令会等待bgsave执行完后再执行。
2、父进程执行fork创建子进程。
3、fork完成后，主进程会继续响应其他命令。所有写命令会继续写入到aof_buf中，并根据appendfsync策略持久化到aof文件中。
4、子进程共享fork操作时内存数据，对于fork操作后生成的数据，主进程会单独开辟一块aof_rewrite_buf保存。
5、子进程根据内存快照，按照命令合并规则写入到新的aof文件中。每次批量写入磁盘的数量由aof-rewrite-incremental-fsync参数控制，默认32mb，避免单次刷盘数据过多造成硬盘阻塞。
6、新aof文件写入完成后，子进程发信号给父进程，父进程更新统计信息。
7、父进程将aof_rewrite_buf(aof重写缓冲区)的数据写入到新的aof文件中
8、使用新aof文件替换老文件，完成aof重写。
实际上：redis执行一个命令后，它会同时将写命令发送到aof_buf和aof_rewrite_buf
```
**aof文件还原数据的流程**
```text
1、创建一个不带网络连接的伪客户端。redis的命令只能在客户端上下文执行。
2、从aof文件中分析并读取一条命令。
3、使用伪客户端执行该命令。
4、反复执行2，3直到aof文件中所有命令都被处理完。
```
#### 2.4 RDB和AOF区别
| 持久化方式   | RDB                     | AOF                      |
| ------------ | ----------------------- | ------------------------ |
| 占用磁盘空间 | 小（数据级：压缩）      | 大（指令级：重写）       |
| 存储速度     | 慢                      | 快                       |
| 恢复速度     | 快                      | 慢                       |
| 数据安全性   | 会丢失数据(一般每X小时) | 依据写策略决定(一般秒级) |
| 资源消耗     | 高/重量级               | 低/轻量级                |
| 自动优先级   | 低                      | 高                       |
#### 2.5 RDB和AOF选择
```text
1、不能承受数分钟以内的数据丢失，对业务数据非常敏感，选用AOF。
2、能承受数分钟以内的数据丢失，且追求大数据集的恢复速度，选用RDB。如灾难恢复。
3、双保险策略，同时开启RDB和AOF。重启后，redis优先使用AOF恢复数据，降低丢失数据的量。
```
### 3. 事务
#### 3.1 事务基本操作
**定义**
   redis事务将一些列预定义命令包装成一个整体。当执行时，一次性按照添加顺序依次执行，中间不会被打断或干扰。
**基本操作**
```redis
# 开启事务
multi
# 执行事务
exec
# 取消事务
discard
```
**事务中的错误**
1、命令语法错误：整体事务所有命令都不执行包括那些正确的命令
```redis
multi
set name liudehua
get name
ll name xiaoshi
exec
```
   上面事务报错，所有命令均不执行
2、运行错误：运行正确的命令会执行，运行错误的命令不会执行
```redis
multi
set name liangchaowei
get name
lpush name a b c
exec
```
   上面事务中set，get命令执行，lpush不执行
#### 3.2 锁
**watch监视**
```redis
# 对key添加监视锁，在执行exec前如果key发生变化，终止事务执行(必须在事务外watch)
watch key1 [key2 ...]
# 取消所有key的监视(只能取消本次会话中监视的key)
unwatch
```
   例如：
| session A                       | session B    |
| ------------------------------- | ------------ |
| watch name;  multi;  set aa cc; |              |
|                                 | set name 123 |
| exec                            |              |
   上面例子session A中的事务exec执行时返回nil，事务未执行
**分布式锁**
```redis
# 设置一个公共锁
setnx lock-key value
# 删除公共所
del lock-key
# 设置公共锁的过期时间(解决死锁)
expire lock-key seconds
```
### 4. 删除策略
#### 4.1 过期数据
通过ttl指令获取数据的状态：
```text
XX：具有时效性的数据
-1：永久有效的数据
-2：已过期 或 已被删除 或未定义的数据
```
#### 4.2 数据删除策略
又名：<font color='red'>过期策略</font>，缓存的key过期了，redis如何处理？
expires数据集：存储所有设置了过期时间的key
存储形式： 物理地址 =》 过期时间。eg：0x0110=>135954124
##### 4.2.1 定时删除
- 创建一个定时器，当key设置了过期时间且已过期，由定时器任务立即执行删除
- 优点：节约内存，到期就删除，快速释放掉不必要的内存占用
- 缺点：CPU压力大，无论CPU负载多高，均占用CPU，会影响redis服务器响应时间和吞吐量
- 总结：用处理器性能换取存储空间（时间换空间）
##### 4.2.2 惰性删除
- 数据到达过期时间，不做处理。等下次访问该数据时(`expireIfNeeded()`方法)：
	- 如果未过期，返回数据
	- 如果过期，删除，返回不存在
- 优点：节约CPU性能，发现必须删除的时候才删除
- 缺点：内存压力大，会出现长期占用内存的数据
- 总结：用存储空间换取处理器性能（空间换时间）
##### 4.2.3 定期删除
- 流程
```text
1、redis启动服务器初始化时，读取配置serve.hz的值(`info server`查看)，默认为10
2、每秒钟执行sever.hz次`serverCron()`->`databasesCron()`->`activeExpireCycle()`
3、activeExpireCycle对每个expires[*]逐一检测，每次执行250ms/server.hz时长
4、对某个expires[*]检测时，随机挑选W个key检测
	1）若key过期，删除key
	2）若一轮中删除的key的数量>W*25%,循环该过程
	3）若一轮中删除的key的数量<=W*25%,检查下一个expires[*],0-15循环
	4）W取值=ACTIVE_EXPIRE_CYCLE_LOOKUPS_PER_LOOP属性值
5、参数current_db用于记录activeExpireCycle进入哪个expires[*]执行
6、若activeExpireCycle执行时间到期，下次从current_db继续向下执行
```
- <font color='red'>周期性轮循redis库中的expires字典中一定数量的key并清除其中已过期的key。采用随机抽取的策略，利用过期数据占比的方式控制删除频率</font>
- 特点：
	- CPU性能占用设置有峰值，检测频率可自定义设置
	- 内存压力不是很大，长期占用内存的冷数据会被持续清理
- 总结：周期性抽查存储空间（随机抽查，重点抽查）
##### 4.2.4 对比
| 删除策略 | 内存占用         | CPU消耗                 | 总结               |
| -------- | ---------------- | ----------------------- | ------------------ |
| 定时删除 | 节约内存，无占用 | 不分时段占用CPU，频率高 | 时间换空间         |
| 惰性删除 | 内存占用严重     | 延时执行，CPU利用率高   | 空间换时间         |
| 定期删除 | 内存定期随机清理 | 每秒花费固定CPU资源     | 随机抽查，重点抽查 |
#### 4.3 逐出算法
又名：<font color='red'>内存淘汰策略</font>，当新写入的key，内存不足时，redis如何处理？
##### 4.3.1 简介
​    redis执行每一个写命令前，都会调用`freeMemoryIfNeeded()`检测内存是否充足。若内存不满足新加入数据的最低存储要求，redis要临时删除一些数据为当前指令清理存储空间。清理数据的策略成为逐出算法（淘汰算法）
​    逐出数据不是100%能够清理出足够可使用的内存空间，如果不成功则反复执行。当对所有数据尝试完毕后，还不能满足要求，将抛出如下错误：
```redis
(error)OOM command not allowed when used memory > 'maxmemory'
```
##### 4.3.2 配置
```shell
# 最大可使用内存
maxmemory
# 每次选取待删除数据的个数(不会全库扫描，而是随机获取数据作为待检测删除数据)
maxmemory-samples
# 淘汰策略
maxmemory-policy
```
##### 4.3.3 逐出|淘汰策略
- 会过期的数据集server.db[i].expires
	- 1.volatile-lru：最近最少使用的数据淘汰
	- 2.volatile-lfu：最近使用次数最少的数据淘汰
	- 3.volatile-ttl：更早过期时间的数据淘汰
	- 4.volatile-random：过期数据随机淘汰
- 所有数据集server.db[i].dict
	- 5.allkeys-lru：最近最少使用的数据淘汰
	- 6.allkeys-lfu：最近使用次数最少的数据淘汰
	- 7.allerys-random：所有数据随机淘汰
- 放弃
	- 8.noeviction(驱逐)：禁止驱逐数据
```shell
maxmemory-policy volatile-lru
```
```text
lru：Least Recently Used 最近最少使用
lfu：Least Frequently Used 最近使用次数最少
```
### 5. 高级数据类型
#### 5.1 Bitmaps(位图)
应用场景：redis应用于信息状态统计
eg：判断某个用户是否访问过搜索页面
1、设置指定key对应偏移量上的bit值，value只能是0或1

```redis
setbit key offset value
```
2、获取指定key对应偏移量上的bit值
```redis
getbit key offset
```
3、对指定key按位进行交、并、非、异或操作，并将结果保存到destKey中
```redis
bitop op destKey key1 [key2...]
```
op：and（交），or（并），not（非），xor（异或）
4、统计指定key中1的数量
```redis
bitcount key [start end]
```
5、在指定key中寻址第一个为0或1的bit位置
```redis
bitpos key 0|1
```
#### 5.2 HyperLogLogs
应用场景：redis应用于独立信息统计（基数统计）
基数：数据集中去重后元素的个数
eg：计算用户每天在搜索框中执行的唯一查询，即搜索页面UV统计
1、添加数据
```redis
pfadd key element [element ...]
```
2、统计数据
```redis
pfcount key [key ...]
```
3、合并数据
```redis
pfmerge destkey sourcekey [sourcekey ...]
```
#### 5.3 GEO
应用场景：redis应用于地理位置计算
eg：微信附近的人，美团/饿了么
1、添加坐标点
```redis
geoadd key longitude latitude member [longitude latitude member ...]
geoadd city 113.501 22.405 shenzhen 110.500 20.501 nanjing
```
2、获取坐标点
```redis
geopos key member [member ...]
geopos city shenzhen nanjing
```
3、计算坐标点距离
```redis
geodist key member1 member2 [unit]
geodist city shenzhen nanjing
```
unit：m（米，默认），km（千米），mi（英里），ft（英尺）
4、根据坐标求范围内的数据
```redis
georadius key longitude latitude radius m|km|ft|mi [withcoord] [withdist] [withhash] [count count]
georadius city 113 22 100 km
```
5、根据点求范围内的数据
```redis
georadiusbymember key member radius m|km|ft|mi [withcoord] [withdist] [withhash] [count count]
georadiusbymember city shenzhen 1000 km
```
6、获取指定点对应的坐标hash值
```redis
geohash key member [member ...]
geohash city shenzhen nanjing
```
### 6.其他
#### 配置选项
```redis
# 设置选项
config set parameter value
# 获取选项
config get parameter
```
#### 客户端与服务器
##### auth
```redis
# 解锁密码保护的服务器
auth password
```
   举例：
```redis
config set requirepass 123456
quit
# 重新登录 redis-cli
auth 123456
ping
```
##### info
```redis
# 查看服务器信息
info [section]
#section可能的值：server，clients，memory，persistence，stats，replication，CPU，cluster，keyspace
```
  详情解释可参看 [info-section](http://redisdoc.com/client_and_server/info.html)
#### 内部命令
```redis
# 复制时用到的命令
sync
psync
```
