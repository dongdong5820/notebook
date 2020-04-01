### 1、读写分离，主从复制
#### 1.1 原理
   在主库上记录二进制日志，在备库重放日志的方式实现**异步**数据复制。总的来说，复制有三个步骤：
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql-repl/replication.jpg)   
1）master记录二级制日志(binary log)：每次准备提交事务完成数据更新前，先记录二进制日志，然后告诉存储引擎提交事务  
2）slave将master的二进制日志复制到本地的中继日志(relay log)中：首先，备库先启动一个IO_Thread线程，负责和主库建立一个普通的客户端连接。如果该线程追赶上了主库，它将进入睡眠状态，直到主库有新的事件产生通知它才会被唤醒，将接收到的事件记录到中继日志中。  
3）备注的SQL_Thread线程执行最后一步：该线程从中继日志中读取事件并且在备库执行，当SQL线程赶上IO线程时，就会等待。中继日志通常记录在系统缓存中，故开销很低。SQL线程可以垦局配置选项来决定是否写入自己的二进制日志中。

#### 1.2 主从复制延迟
##### 1.2.1 产生延迟原因
- 主节点如果执行一个很大的事务，那么就会对主从延迟产生较大的影响。
- 网络延迟，日志较大，slave数量过多。
- 主节点上多线程写入，从节点只有单线程恢复。
##### 1.2.2 处理方法
- 大事务：将大事务分为小失误，分批更新数据。
- 减少slave数量，不要超过5个，减少单次事务的大小。
- mysql5.7之后，可以使用多线程复制（MGR复制架构）
#### 1.3 docker配置主从复制
参考文章: [docker配置mysql主从](https://www.mscto.com/cloud/246704.html)
##### 1.3.3 环境准备  
`linux版本：Ubuntu 18.04.1 LTS(homestead自带)`
`docker版本：Docker version 18.09.0`
##### 1.3.2 配置过程
1）获取mysql镜像(国内可能会比较慢，可以切换到阿里，腾讯等源)
``` shell
docker pull mysql
```
2）创建文件夹，配置文件my.cnf
``` shell
mkdir -p /usr/mysql/{master,slave}/data
cd /usr/mysql/master
vim my.cnf
tree
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql-repl/tree.png)
my.cnf文件内容

``` shell
[mysqld]
user = mysql
character-set-server = utf8mb4
# 身份验证插件(默认是 caching_sha2_password, 旧版navicat不支持)
default_authentication_plugin = mysql_native_password
# 临时文件目录(slave一定要设置，否则会报Last_Error: Unable to use slave's temporary directory /tmp - Can't read dir of '/tmp/' (Errcode: 13 - Permission denied)错误)
# tmpdir = /var/lib/mysql
secure-file-priv = NULL
# [必须]启用二进制日志
log-bin=mysql-bin
# [必须]集群中服务器唯一ID
server-id=1
# 复制的数据库
#binlog-do-db = homestead
# 不复制的数据库
binlog_ignore_db = mysql
# 复制的格式(mixed,statement,row.默认是statement)
# binlog-format = mixed
# 二进制日志自动删除/过期的天数。默认值为0，表示不自动删除
# expire_logs_days = 7
[client]
default-character-set = utf8mb4
[mysql]
default-character-set = utf8mb4
```
3）创建并运行mysql容器
映射宿主机的33061,33062端口
``` shell
cd /usr/mysql/master
docker run -p 33061:3306 --privileged=true --name mysql-master -v $PWD/my.cnf:/etc/mysql/my.cnf -v $PWD/mysql-files:/var/lib/mysql-files -v $PWD/data:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=123456 -d mysql:latest
cd /usr/mysql/slave
docker run -p 33062:3306 --privileged=true --name mysql-slave -v $PWD/my.cnf:/etc/mysql/my.cnf -v $PWD/mysql-files:/var/lib/mysql-files -v $PWD/data:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=123456 -d mysql:latest
docker ps
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql-repl/docker-ps.png)
4）主库中创建同步账号slave_1并授权
``` shell
docker exec -it mysql-master bash
mysql -uroot -p
# 这里使用slave_1用户进行主从复制, %为允许所有ip进行复制。
mysql > create user 'slave_1'@'%' identified by '123456';
mysql > grant replication slave on *.* to 'slave_1'@'%';
# 刷新权限
mysql > flush privileges;
# 查看主服务器状态(注意File, Position字段内容,下面有用到)
mysql > show master status;
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql-repl/master-status.png)
5） 配置slave的mysql同步信息
``` shell
docker exec -it mysql-slave bash
mysql -uroot -p
mysql > change master to 
master_host='192.168.10.11',
master_port=33061,
master_user='slave_1',
master_password='123456',
master_log_file='mysql-bin.000003',
master_log_pos=825;
mysql > start slave;
mysql > show slave status\G;
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql-repl/slave-success.png)
`tips:`
1）中途遇到如下错误：

```mysql
Last_Error: Unable to use slave's temporary directory /tmp - Can't read dir of '/tmp/' (Errcode: 13 - Permission denied)
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql-repl/slave-error.png)
在slave从库配置文件中的[mysqld]下方添加配置
``` mysql
tmpdir=/var/lib/mysql
```
2）show slave status命令结果的重要参数
```
Master_Log_File 主库的binlog文件名
Read_Master_Log_Pos 同步主库binlog文件的位置偏移量
Relay_Log_File	从库中继日志文件名
Relay_Log_Pos	从库回放中继日志位置偏移量
Slave_IO_Running 从库IO线程是否运行
Slave_SQL_Running 从库SQL线程是否运行
Seconds_Behind_Master 主备复制延迟
Relay_Master_Log_File 回放对应主库的log文件名
Exec_Master_Log_Pos 回放对应主库的log文件偏移量
Retrieved_Gtid_Set 备库收到的所有日志的gtid集合
Executed_Gtid_Set 备库所以已经执行完成的gtid集合
```
Master_Log_File，Read_Master_Log_Pos 表示读到主库的最新位点
Relay_Master_Log_File， Exec_Master_Log_Pos 表示备库执行的最新位点

##### 1.3.3 用到的其他命令
``` shell
# 查看容器日志
docker logs -f mysql-slave
# 容器状态，启动，停止，删除
docker ps [-a]
docker start|stop|rm container_name
# mysql命令
mysql > stop slave;
mysql > reset slave;
```
DDL（data definition language）数据定义语句：定义或改变表结构，数据类型等。常见命令create,alter,drop等
DML（data manipulation language）数据操作语句：操作数据库里的数据。常见命令select,update,insert,delete等
GTID(global transaction identifer)全局事务ID
### 2. MySQL服务器监控
#### 2.1 监控指标
主要参考命令：
```sql
show master status;
show slave status;
show global status like 'xxx';
show global variables like 'xxx';
```
- 查询吞吐量
QPS： Com_select，执行select语句的数量
TPS： Com_insert+Com_update+Com_delete总数
- 查询执行性能
performance_schema库：events_statements_summary_by_digest表的指标(微秒为单位)
慢查询：
```sql
show variables like 'long_query_time';
show global status like 'slow_queries'; # 慢查询数量
```
- 连接情况
```sql
max_connections 最大连接数
threads_connected 已经建立的连接
threads_running 正在运行的连接
connection_errors_internal 由于服务器内部本身导致的连接错误
aborted_connects 尝试与服务器建立连接但失败的次数
connection_errors_max_connections 由于达到最大连接导致连接失败的次数
```
- 缓冲池使用情况
```sql
innodb_buffer_pool_page_total bp中页总数
buffer_pool_utilization bp页的使用率
innodb_buffer_pool_read_requests bp读请求字节数
innodb_buffer_pool_reads bp读请求次数
```
更多想起参数请看 《mysql监控指标.xls》
