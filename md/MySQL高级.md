##  MySQL高级
[mysql在线手册](https://dev.mysql.com/doc/refman/8.0/en/show-profile.html)
### 1.linux系统安装MySQL
### 2.索引
- 回表
  基于非主键索引的查询，需要额外扫描主键索引的过程。
- 覆盖索引
  一个索引覆盖了查询语句需要的所有字段。减少树的搜索次数。
> 直接返回索引上的字段，无需回表  

- 最左前缀匹配
- 索引下推(索引条件下推-index condition pushdown|ICP)
  - mysql5.6引入索引下推
  - 储存引擎在索引遍历过程中，对索引中包含的字段先做判断，直接过滤掉不满足条件的记录，减少IO次数
  - 适用于innodb,myisam引擎的查询。innodb聚簇索引不能下推
  - explain执行计划中的extra有using index conditio表示使用了索引下推。
  - 开启或关闭索引下推。默认开启。
```mysql
set optimizer_switch = 'index_condition_pushdown=on|off'
```

### 3.视图
#### 3.1 视图概述
  视图(view)是一种虚拟存在的表。并不在数据库中实际存在。通俗的将，视图就是一条select语句执行后返回的结果集。
  相对于普通表的优点：
- 简单：使用视图的用户完全不用关心后面对应的表结构，关联条件和筛选条件
- 安全：使用视图的用户只能访问他们被允许查询的结果集(外包项目运用居多)
- 数据独立：源表增加列对视图没有影响；源表修改列则可通过修改视图解决
#### 3.2 创建或修改视图
创建视图语法：
```mysql
create [or replace] [algorithm = {undefined | merge | temptable}]
view view_name [(column_list)]
as select_statement
[with [cascaded| local] check option]
```
修改视图语法：
```mysql
alter [algorithm = {undefined | merge | temptable}] 
view view_name [(column_list)]
as select_statement
[with [cascaded| local] check option]
```
```text
选项：
[with [cascaded| local] check option] 决定了是否允许更新数据使记录不在满足视图的条件。
local : 只要满足本视图的条件就可以更新
cascaded : 必须满足所有针对该视图的条件才能更新。
```
#### 3.3 查看或删除视图
显示所有的表和视图
`show tables;`
显示某张表或视图的详情
`show table status like 'tb_seller';`
删除视图语法：
```mysql
drop view [if exists] view_name [,view_name] ...
```
### 4.存储过程和函数
#### 4.1 存储过程和函数概述
		存储过程和函数是 事先经过编译并存储在数据库中的一段SQL语句的集合，调用存储过程和函数可以简化应用开发人员的很多工作，减少数据在数据库和应用服务器之间的传输，提高数据处理效率。
		两者区别： 函数必须有返回值，二存储过程可以没有
		函数： 是一个有返回值的过程；
		过程：是一个没有返回值的函数；
#### 4.2 创建存储过程
``` SQL
CREATE PROCEDURE procedure_name([paramater[,,,]])
BEGIN
  -- SQL语句
END$
```
示例：
```mysql
delimiter $
create procedure pro_test1()
begin
	select 'hello world';
end$
```
<font color='#FF0000'>知识小贴士</font>
delimiter	  该关键字用来声明SQL语句的分隔符，告诉MySQL解释器，该段命令是否已经结束了，mysql是否可以执行了。默认情况下，delimiter是分号;。在命令行客户端中，如果有一行命令以分号结束，那么回车后，mysql将会执行该命令。  
<font color='blue'>需求： 用存储过程插入10万条数据</font>
```mysql
-- 创建表
CREATE TABLE `t` (
 `id` INT(11) NOT NULL AUTO_INCREMENT,
 `a` INT(11) NOT NULL DEFAULT 0,
 `b` INT(11) NOT NULL DEFAULT 0,
 PRIMARY KEY(`id`),
 KEY `a`(`a`) USING BTREE,
 KEY `b`(`b`) USING BTREE
 ) ENGINE=INNODB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='测试表';
-- 用存储过程插入10万条数据
DELIMITER ;;
CREATE PROCEDURE idata()
BEGIN
  DECLARE i INT;
  SET i=1;
  WHILE(i<=100000) DO 
    INSERT INTO t VALUES (i,i,i);
    SET i=i+1;
  END WHILE;
END;;
DELIMITER ;
CALL idata();
```
#### 4.3 调用存储过程
```mysql
call procedure_name();
```
#### 4.4 查看存储过程
```Mysq
-- 查看homestead数据库中所有的存储过程
select `name` from mysql.proc where db='homestead';
-- 查看存储过程的状态信息
show procedure status\G;
-- 查看某个存储过程的定义
show create procedure homestead.procedure_test1 \G;
```
#### 4.5 删除存储过程
```mysql
drop procedure [if exists] procedure_name;
```
#### 4.6 语法
存储过程是可以编程的，意味着可以使用变量，表达式，控制结构，来完成比较复杂的功能。
##### 4.6.1 变量
- declare   
通过 declare可以定义一个局部变量，该变量的作用范围只能在begin...end块中。
```mysql
declare var_name[,...] type [default value]
```
示例：
```mysql
delimiter $
create procedure pro_test2()
begin
	declare num int default 5;
	select num + 10;
end$
```
- set
直接赋值使用set，可以赋值常量或者表达式，具体语法如下：
```mysql
set var_name = expr[, var_name = expr] ...
```
示例：
```mysql
create procedure pro_test3()
begin
	declare name varchar(20);
	set name = 'mysql';
	select name;
end$
```
- select ... into 方式进行赋值
```mysql
create procedure pro_test4()
begin 
	declare num int;
	select count(*) into num from film;
	select num;
end$
```
##### 4.6.2 if结构
语法结构
```mysql
if search_condition then statement_list
	[elseif search_condition then statement_list] ...
	[else statement_list]
end if;
```
需求：
```mysql
根据定义的身高变量，判定当前身高所属的身材类型
> 180 --> 身材高挑
170 - 180 --> 标准身材
< 170 --> 一般身材
```
示例：
```mysql
create procedure pro_test5()
begin
	declare height int default 190;
	declare descs varchar(10) default '';
	if height > 180 then 
		set descs = '身材高挑';
	elseif height <= 180 and height >=170 then
		set descs = '标准身材';
	else
		set descs = '一般身材';
	end if;
	select concat('身高为：',height,'，对应的身材类型为：',descs) as content;
end$
```
##### 4.6.3 传递参数
语法格式：
```mysql
create procedure procedure_name([in/out/inout] 参数名 参数类型)
...
in : 该参数可以作为输入，即需要调用方传入值，默认类型
out : 该参数作为输出，即可以作为返回值
inout : 既可以作为输入参数，也可以作为输出参数
```
**IN - 输入**
需求：
```mysql
根据定义的身高变量，判定当前身高所属的身材类型
```
示例：
```mysql
create procedure pro_test6(in height int)
begin
	declare descs varchar(10) default '';
	if height > 180 then 
		set descs = '身材高挑';
	elseif height <= 180 and height >=170 then
		set descs = '标准身材';
	else
		set descs = '一般身材';
	end if;
	select concat('身高为：',height,'，对应的身材类型为：',descs) as content;
end$
```
调用：
```mysql
call pro_test6(165)$
```
**OUT - 输出**
示例：
```mysql
create procedure pro_test7(in height int, out descs varchar(10))
begin
	if height > 180 then 
		set descs = '身材高挑';
	elseif height <= 180 and height >=170 then
		set descs = '标准身材';
	else
		set descs = '一般身材';
	end if;
end$
```
调用：
```mysql
call pro_test7(174, @descs)$
select @descs$
```
<font color='#FF0000'>小知识</font>
@descs ：这种变量在变量名称前面加上'@'符号，叫用户会话变量，代表整个会话过程都是有作用的，这个类似于全局变量一样。
@@global.sort_buffer_size : 这种在变量前加上'@@'符号，叫做 系统变量
##### 4.6.4 case结构
语法结构：
```mysql
方式一:
case case_value
	when when_value then statement_list
	[when when_value then statament_list] ... 
	[else statement_list]
end case;
方式二:
case
	when search_condition then statement_list
	[when search_condition then statement_list] ...
	[else statement_list]
end case;
```
需求：
```mysql
给定一个月份，然后计算出所在的季度
```
示例：
```mysql
create procedure pro_test8(in month int)
begin
	declare result varchar(50) default '';
	case 
		when month >=1 and month <= 3 then
			set result = '第一季度';
		when month >=4 and month <= 6 then
			set result = '第二季度';
		when month >= 7 and month <= 9 then
			set result = '第三季度';
		else 
			set result = '第四季度';
	end case;
	select concat('当前的月份为：', month, '，计算出的季度为：', result) as content;
end$
```
##### 4.6.5 while循环
语法结构：
```mysql
while search_condition do
	statement_list
end while;
```
需求
```mysql
计算从1加到n的值
```
示例：
```msyql
create procedure pro_test9(in num int)
begin
	declare total,i int default 0;
	while i <= num do 
		set total = total + i;
		set i = i + 1;
	end while;
	select concat('从1加到', num, '的值为：', total) as content;
end$
```
##### 4.6.6 repeat结构
有条件的循环控制语句，当满足条件的时候就退出循环。while是满足条件才执行，repeat是满足条件就退出循环。
语法结构：
```mysql
repeat
	statement_list
	until search_condition
end repeat;
```
```msyql
计算从1加到n的值
```
示例：
```mysql
create procedure pro_test10(in num int)
begin
	declare total int default 0;
	repeat
		set total = total + num;
		set num = num - 1;
		until num = 0
	end repeat;
	select concat('从1加到', num, '的值为：', total) as content;
end$
```
<font color='#FF0000'>注意</font> until后面的条件判断不能有分号';'。
##### 4.6.7 loop语句
loop实现简单的循环，退出循环的条件需要使用其他语句定义，通常可以使用leave语句实现，具体语法如下：
```mysql
[begin_label:] loop
	statement_list
end loop [end_label];
```
如果不在 statement_list 中增加退出循环语句，那么loop语句可以实现简单的死循环
##### 4.6.8 leave语句
用来从标注的流程构造中退出，通常和begin...end或者循环一起使用。示例 loop 和 leave 的简单例子，退出循环：
```mysql
用 loop 和 leave 实现计算从1加到n的值
```
示例：
```mysql
create procedure pro_test11(in num int)
begin
	declare total int default 0;
	c: loop
		set total = total + num;
		set num = num - 1;
		if num <= 0 then
			leave c;
		end if;
	end loop c;
	select concat('从1加到', num, '的值为：', total) as content;
end$
```
##### 4.6.9 游标/光标
游标是用来存储查询结果集的数据类型。在存储过程和函数中可以使用游标对结果集进行循环处理。其具体操作有游标的声明、open、fetch、close。语法分别如下：
- 声明游标:
```mysql
declare cursor_name cursor for select_statement;
```
open 游标：
```mysql
open cursor_name;
```
- fetch 游标：
```mysql
fetch cursor_name into var_name[,var_name] ...;
```
- close 游标：
```mysql
close cursor_name;
```
示例： 利用游标查询emp中的数据，并逐行进行展示
初始化脚本:
```mysql
create table emp (
	id int(11) not null auto_increment,
	name varchar(50) not null comment '姓名',
	age int(11) comment '年龄',
	salary int(11) comment '薪水',
	primary key (id)
)engine = innodb default charset=utf8;
insert into emp values (null, '金毛狮王', 55, 3800),(null, '白眉鹰王', 60, 4000),(null, '青翼蝠王', 38, 2800),(null, '紫衫龙王', 42, 1800);
```
```mysql
create procedure pro_test12()
begin
	declare e_id,e_age,e_salary int default 0;
	declare e_name varchar(50) default '';
	declare has_data int default 1;
	declare emp_result cursor for select * from emp;
	declare exit handler for not found set has_data=0;
	open emp_result;
	repeat
		fetch emp_result into e_id,e_name,e_age,e_salary;
		select concat('id=',e_id,',name=',e_name,',age=',e_age,',薪水为：',e_salary) as content;
		until has_data=0
	end repeat;
	close emp_result;
end$
```
#### 4.7 存储函数
语法结构：
```mysql
create function function_name([param type, ...])
returns type
begin
	statement_list...
end;
```
案例：
定义一个存储函数，计算满足条件的总记录数：
```mysql
create function count_salary(num int)
returns int
begin
	declare e_count int default 0;
	select count(*) into e_count from emp where salary=num;
	return e_count;
end$
```
调用：
```mysql
select count_salary(3800) as count;
```
删除：
```msyql
drop function count_salary;
```
### 5.触发器
#### 5.1 介绍
		触发器是与表有关的数据库对象，指在 insert/update/delete 之前或之后，触发并执行触发器中定义的SQL语句集合。触发器可以协助应用在数据库端确保数据的完整性，日志记录，数据校验等操作。
	使用别名old和new来引用触发器中发生变化的记录内容，这与其他数据库是相似的。现在MySQL还只支持行级触发，不支持语句级触发。
| 触发器类型 | new和old的使用                                   |
| ---------- | ------------------------------------------------ |
| insert     | new表示将要或已经新增的数据                      |
| update     | old表示修改之前的数据，new表示将要或修改后的数据 |
| delete     | old表示将要或已经删除的数据                      |
#### 5.2 创建触发器
语法结构：
```mysql
create trigger trigger_name
before/after insert/update/delete
on tbl_name
[ for each row] -- 行级触发器
begin
	trigger_statement;
end;
```
需求
```mysql
通过触发器记录 emp 表的数据变更日志，包含新增，修改，删除;
```
首先创建一张日志表：
```mysql
create table emp_logs(
	id int(11) not null auto_increment,
	operation varchar(20) not null comment '操作类型 insert/update/delete',
	operate_time datetime not null comment '操作时间',
	operate_id int(11) not null comment '操作表的ID',
	operate_params text comment '操作参数',
	primary key (id)
) engine=innodb default charset=utf8;
```
insert型触发器，完成插入数据时的日志记录:
```mysql
create trigger emp_logs_insert_trig
after insert
on emp
for each row
begin 
	insert into emp_logs values (null,'insert',now(),new.id,concat('插入后(id:',new.id,',name:',new.name,',age:',new.age,',salary:',new.salary,')'));
end$
-- 插入数据
insert into emp values (null, '刘德华', 40, 10000);
insert into emp values (null,'张学友',45,15000),(null,'郭富城',48,13000),(null,'黎明',48,9000);
```
update型触发器
```mysql
create trigger emp_logs_update_trig
after update
on emp
for each row
begin
	insert into emp_logs values (null,'update',now(),old.id,concat('修改前(id:',old.id,',name:',old.name,',age:',old.age,',salary:',old.salary,');修改后(id:',new.id,',name:',new.name,',age:',new.age,',salary:',new.salary,')'));
end$
-- 修改数据
update emp set salary=40000 where id=5;
```
delete型触发器
```mysql
create trigger epm_logs_delete_trig
after delete
on emp
for each row
begin
	insert into emp_logs values (null,'delete',now(),old.id,concat('删除前(id:',old.id,',name:',old.name,',age:',old.age,',salary:',old.salary,')'));
end$
-- 删除数据
delete from emp where id = 9;
```
#### 5.3 删除触发器
语法结构：
```mysql
drop trigger [schema_name.]trigger_name;
```
没有指定schema_name(库名)，默认为当前数据库
#### 5.4 查看触发器
可以通过 show triggers 命令查看触发器状态、语法等信息。
语法结构：
```mysql
show triggers;
```
### 6 体系结构
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/jiegou.jpg)   
整个MySQL Server由以下组成
- Connection Pool : 连接池组件
- Management Service & Utilities : 管理服务和工具组件
- SQL Interface : SQL接口组件
- Parser : 查询分析器组件
- Optimizer : 优化器组件
- Caches & Buffers : 缓存池组件
- Pluggable Storage Engines : 储存引擎
- File System : 文件系统   
 1）连接层   
 ​    最上层是一些客户端和链接服务，包含本地sock通信和大多数基于客户端/服务端工具实现的类似于TCP/IP的通信。主要完成一些类似于连接处理、授权认证、及相关的安全方案。在该层上引入了线程池的概念，为通过认证安全接入的客户端提供线程。同样在该层上可以实现基于SSL的安全链接。服务器也会为安全接入的每个客户端验证它所具有的操作权限。  
 2）服务层   
   第二层架构主要完成大多数的核心服务功能，如SQL接口，并完成缓存的查询，SQL的分析和优化，部分内置函数的执行。所有夸储存引擎的功能也在这一层实现，如过程，函数等。在该层，服务器会解析查询并创建相应的内部解析树，并对其完成相应的优化如确定表的查询顺序，是否利用索引等，最后生成相应的执行操作。如果是select语句，服务器还会查询内部缓存，如果缓存空间足够大，这样在解决大量读操作的环境中能够很好的提升系统性能。   
 3）引擎层   
   存储引擎层，存储引擎真正的负责了MySQL中数据的存储和提取，服务器通过API和存储引擎进行通信。不同的存储引擎具有不同的功能，这样我们就可以根据自己的需要，来选取合适的存储引擎。  
 4）存储层   
   数据存储层，主要是将数据存储在文件系统之上，并完成与存储引擎的交互。 
<font color='blue'>简化体系结构：</font>  
- 连接器：管理客户端连接，验证权限
- 查询缓存：
- 分析器：词法分析，语法分析  -》 得到语法树
- 预处理器：
- 优化器：CBO|RBO --》 得到执行计划
- 执行器：执行SQL语句(从存储引擎获取数据)
- 存储引擎：innodb，myisam，memory
- 文件系统
### 7 存储引擎
### 8 优化SQL步骤
  当面对一个有SQL性能问题的数据库时，我们应该从何处入手进行系统的分析，以便能够尽快定位问题SQL并解决问题。  
#### 8.1 show status查看SQL执行效率
  MySQL客户端连接成功后，通过 show [session|global] status 命令可以提供服务器状态信息。该命令 可以根据需要加上参数'session'或'global'来显示session级(当前连接)和global级(自数据库上次启动至今)的统计结果。默认为session。  
    show status like 'innodb_rows_%';
    ![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/show-status-innodb.png) 
    show global status like 'Com_%';
    ![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/show-status-com.png)    
  [show status 参数详解](https://www.cnblogs.com/zuxing/articles/7761262.html)  
​    Com_xxx 表示每个xxx语句执行的次数(对于所有存储引擎的表操作都会累计)，我们通常比较关心的是以下几个统计参数。  

| 参数             | 含义               |
| ---------------- | ----------------------------------|
| Com_select       | 执行select操作的次数，一次查询只累加1。 |
| Com_insert       | 执行insert操作的次数，批量插入的insert操作，只累加1次。|
| Com_update       | 执行update操作的次数。|
| Com_delete       | 执行delete操作的次数。|
| Innodb_rows_inserted | 插入到InnoDB表的行数。|
| Innodb_rows_deleted | 从InnoDB表删除的行数。 |
| Innodb_rows_updated | 从InnoDB表内更新的行数。 |
| Innodb_rows_read | 从InnoDB表读取的行数。 |
| Connections | 试图连接到(不管是否成功)MySQL服务器的连接数 |
| Uptime | 服务器已经运行的时间(以秒为单位) |
| Slow_queries | 查询时间超过long_query_time秒的查询的个数 |
#### 8.2 show processlist定位低效率SQL
  可以通过以下两种方式定位效率较低的SQL语句。
  - 慢查询日志：通过慢查询日志定位那些执行效率较低的SQL语句，用--log-slow-queries[=file_name]选项启动时，mysqld写一个包含所有执行时间超过long_query_time秒的SQL语句日志文件。具体查看日志管理的相关部分。
  - show processlist : 慢查询日志在查询结束以后才记录，所以在应用反映执行效果出现问题的时候查询慢查询日志并不能定位为题，可以使用show processlist命令查看当前MySQL在进行的线程，包括线程的状态，是否锁表等，可以实时地查看SQL的执行情况，同时对一些锁表操作进行优化。 
    ![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/show-processlist.png)   
|  列名    | 含义 |
| ---- | ---- |
| 1）id列 | 系统分配的'connection_id'，可以使用函数connection_id()查看 |
| 2）user列 | 显示当前用户。若不是root，这个命令就只显示用户权限范围的sql语句 |
| 3）host列 | 显示这个语句是从哪个ip的哪个端口上发的，可以用来跟踪出现问题语句的用户 |
| 4）db列 | 显示这个进程目前连接的是哪个数据库 |
| 5）command列 | 显示当前执行的命令。取值为休眠(sleep)，查询(query)，连接(connect)等 |
| 6）time列 | 显示这个状态持续的时间，单位是秒 |
| 7）state列 | <font color='red'>显示当前sql语句的状态</font>。一个sql语句以查询为列，可能需要进过copying to tmp table、sorting result、sending data等状态才可完成 |
| 8）info列 | 显示这个sql语句 |
#### 8.3 explain分析执行计划
  通过以上步骤查询到效率低下的SQL语句后，可以通过explain或desc命令获取mysql如何执行select语句的信息，包括在select语句执行过程中表如何连接和连接的顺序。
  查询SQL语句的执行计划:
  ``` mysql
  explain select * from emp where id = 1;
  ```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/explain.png) 
| 字段          | 含义 |
| ------------- | ---------------|
| id            | select查询的序列号。表示select子句或操作表的顺序，由大到小依次执行。 |
| select_type   | select的类型。常见的值有simple(简单表，不使用表连接或子查询)、primary(主查询，即外层的查询)、union(union中的第二个或后面的查询语句)、subquery(子查询中的第一select)等 |
| table         | 输出结果集的表|
| type          | <font color='red'>表的连接类型</font>。性能由好到差依次为：system > const > eq_ref > ref > ref or null > index_merge > indes_subquery > range > index > all |
| possible_keys | 查询时可能使用的索引 |
| key           | 实际使用的索引 |
| key_len       | 索引字段的长度|
| ref           | 查找值所用到的列或常量，常见的有：const(常量)、func、NULL、字段名(如film.id) |
| rows          | 扫描的行数，不是结果集的行数|
| extra         | 执行情况的说明和描述。distinct、using index、using where、using temporary、using filesort |
##### 8.3.1 环境准备
```mysql
create table `t_user` (
	`id` varchar(32) not null,
	`username` varchar(45) not null,
	`password` varchar(96) not null,
	`name` varchar(45) not null,
	primary key (`id`),
	unique key `unique_user_username` (`username`)
)engine=innodb default charset=utf8;
create table `t_role` (
	`id` varchar(32) not null,
	`role_name` varchar(255) default null,
	`role_code` varchar(255) default null,
	`description` varchar(255) default null,
	primary key (`id`),
	unique key `unique_role_name` (`role_name`)
)engine=innodb default charset=utf8;
create table `user_role` (
	`id` int(11) not null auto_increment,
	`user_id` varchar(32) default null,
	`role_id` varchar(32) default null,
	primary key (`id`),
	key `fk_user_id` (`user_id`),
	key `fk_role_id` (`role_id`),
	constraint `fk_ur_role_id` foreign key (`role_id`) references `t_role` (`id`) on delete no action on update no action,
	constraint `fk_ur_user_id`foreign key (`user_id`) references `t_user` (`id`) on delete no action on update no action
)engine=innodb default charset=utf8;
-- 插入数据
INSERT INTO `t_user`(`id`,`username`,`password`,`name`) VALUES 
('1','super',PASSWORD('123456'),'超级管理员'),
('2','admin',PASSWORD('123456'),'系统管理员'),
('3','itcast',PASSWORD('123456'),'test02'),
('4','stu1',PASSWORD('123456'),'学生1'),
('5','stu2',PASSWORD('123456'),'学生2'),
('6','tech1',PASSWORD('123456'),'老师1');
insert into `t_role`(`id`,`role_name`,`role_code`,`description`) values 
('5','学生','student','学生'),
('7','老师','teacher','老师'),
('8','教学管理员','teachmanager','教学管理员'),
('9','管理员','admin','管理员'),
('10','超级管理员','super','超级管理员');
insert into `user_role`(`id`,`user_id`,`role_id`) values
(null,'1','5'),
(null,'1','7'),
(null,'2','8'),
(null,'3','9'),
(null,'4','8'),
(null,'5','10');
```
##### 8.3.2 id
  select查询的序列号。表示select子句或操作表的顺序，由大到小依次执行。id相同时由上往下执行。
##### 8.3.3 select_type
表示select的类型，常见值如下：
|select_type|含义|
|--|--|
|simple|简单的select语句，不包含子查询或union|
|primary|查询中若包含任何复杂的子查询，最外层查询标记为该标识|
|subquery|在select或where列表中包含子查询|
|derived|在from列表中包含的子查询，被标记为derived(衍生)|
|union|若第二个select出现在union之后，则标记为union；若union包含在from子句的子查询中，外层select被标记为derived|
|union result|从union表获取结果的select|
```mysql
# simple
explain select * from t_role;
# primary subquery
explain select * from t_role where id=(select id from t_role where role_name='学生');
```
##### 8.3.4 table
展示这一行的数据来自于哪张表
##### 8.3.5 type
显示的连接类型，取值为：
|type|含义|
|--|--|
|null|不访问任何表，索引，直接返回结果|
|system|不进行磁盘IO。表只有一行记录(等于系统表)。const的特例，一般不会出现。|
|const|PK或unique上等值查询。通过索引一次就找到了。|
|eq_ref|PK或unique上join查询，等值匹配，对于前表的每一行(row),后表只有一行命中。|
|ref|非唯一索引，等值匹配，可能命中多行|
|range|索引上的范围查找。如where之后出现between, <, >,in等|
|index|索引上的全集扫描，只遍历了索引树。如innoDB的count操作|
|all|全表扫描|
<font color='red'>一般来说，我们需要保证查询至少达到range级别，最好达到ref</font>

##### 8.3.6 key
```text
possible_keys: 显示可能用到的索引
key: 实际使用的索引
key_len: 索引中使用的字节数。该值为索引字段最大可能长度，并非实际使用长度。长度越短越好
```
##### 8.3.7 rows
扫描的行数，不等于结果集的行数
##### 8.3.8 extra
展示其他的额外执行计划信息。取值为：
|extra|含义|
|--|--|
|using filesort|使用了外部'文件排序'|
|using temporary|排序时使用了临时表。常见于 order by 和 group by|
|using index|相应的select使用了覆盖索引，效率不错|
|using where|使用了where条件|
#### 8.4 show profile分析SQL
支持show profiles 和 show profile语句。帮助我们时间都耗费到哪里去了。了解sql执行的过程。
```mysql
# 查看mysql是否支持profile
select @@have_profiling;
# profiling默认是关闭的，查看是否开启
select @@profiling;
# 开启profiling
set profiling = 1;
```
开启profiling后，执行一堆命令，在执行 `show profiles` 命令，查看sql执行的耗时
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/show-profiles.png)
通过 `show profile for query query_id` 命令查看SQL执行过程中线程状态和消耗时间
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/show-profile-id.png)
```text
Tips:
  sending data表示mysql线程开始访问数据行并把结果集返回给客户端，而不仅仅是放回给客户端的时间。在sending data下，需要做大量的磁盘读取操作，所以耗时一般比较长。
```
#### 8.5 trace分析优化器执行计划
通过trace文件能够了解为什么优化器选择A计划，而不是B计划。
打开trace，设置格式为json，并设置最大能够使用的内存大小
```mysql
set optimizer_trace="enabled=on",end_markers_in_json=on;
set optimizer_trace_max_mem_size=1000000;
```
执行sql语句
```mysql
select * from t_user where id=4;
```
最后，检查information_schema.optimizer_trace表就可以知道mysql是如何执行sql的：
```mysql
select * from information_schema.optimizer_trace\G;
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/optimizer-trace.png)
### 9 索引的使用
#### 9.1 准备环境
```mysql
create table tb_seller(
	`sellerid` varchar(100),
	`name` varchar(100),
	`nickname` varchar(50),
	`password` varchar(60),
	`status` varchar(1),
	`address` varchar(100),
	`createtime` datetime,
	primary key (`sellerid`)
)engine=innodb default charset=utf8mb4;
insert into tb_seller(`sellerid`,`name`,`nickname`,`password`,`status`,`address`,`createtime`) values 
('alibaba','阿里巴巴','阿里小店',password('123456'),1,'北京市','2088-01-01 12:00:00'),
('baidu','百度科技有限公司','百度小店',password('123456'),1,'北京市','2088-01-01 12:00:00'),
('huawei','华为科技有限公司','华为小店',password('123456'),0,'北京市','2088-01-01 12:00:00'),
('itcast','传智播客教育科技有限公司','传智播客',password('123456'),1,'北京市','2088-01-01 12:00:00'),
('itheima','黑马程序员','黑马程序员',password('123456'),0,'北京市','2088-01-01 12:00:00'),
('xiaomi','小米科技有限公司','小米官方旗舰店',password('123456'),2,'北京市','2088-01-01 12:00:00'),
('sina','新浪科技有限公司','新浪官方旗舰店',password('123456'),0,'北京市','2088-01-01 12:00:00'),
('qiandu','千度科技','千度小店',password('123456'),0,'武汉市','2088-01-01 12:00:00');
create index idx_seller_name_sta_addr on tb_seller(name,status,address);
```
#### 9.2 避免索引失效
##### 9.2.1 全值匹配，索引生效，执行效率高
```mysql
explain select * from tb_seller where name='阿里巴巴' and status='1' and address='北京市';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index01.png)
##### 9.2.2 最左前缀法则
  如果索引了多列，要遵守最左前缀法则。查询要从索引的最左前列开始，并且不能跳过索引中列。
```mysql
explain select * from tb_seller where name='阿里巴巴';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index02.png)
跳过索引中的列，则不走索引
```mysql
explain select * from tb_seller where status='1';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index03.png)
##### 9.2.3 范围查询右边的列不能使用索引
```mysql
explain select * from tb_seller where name='阿里巴巴' and status='1' and address='北京市';
explain select * from tb_seller where name='阿里巴巴' and status > '1' and address='北京市';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index04.png)
根据前面的两个字段name，status查询走了索引，但是最后一个条件address没有走索引。
##### 9.2.4 不要再索引列上进行预算操作，否则索引失效
```mysql
explain select * from tb_seller where substring(name,3,2)='科技';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index05.png)
##### 9.2.5 字符串索引列不加单引号，造成索引失效
```mysql
explain select * from tb_seller where name='科技' and status='1';
explain select * from tb_seller where name='科技' and status=1;
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index06.png)
##### 9.2.6 尽量使用覆盖索引，避免select *
```mysql
explain select * from tb_seller where name='科技' and status='1' and address='北京市';
explain select sellerid,name,status,address from tb_seller where name='科技' and status='1' and address='北京市';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index07.png)
如果查询列，超出索引，innodb会进行回表，降低性能。
```text
Tips:
using index : 使用覆盖索引的时候就会出现
using where : 查找使用了where子句
using index condition : 查询使用了ICP-索引条件下推优化
```
##### 9.2.7 in走索引，not in索引失效
```mysql
explain select * from tb_seller where sellerid in('itcast','baidu','huawei');
explain select * from tb_seller where sellerid not in('itcast','baidu','huawei');
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index12.png)
##### 9.2.8 用or分割的条件，只有没有索引的列，那么涉及的索引都不会被用到
```mysql
explain select * from tb_seller where name='小米科技' and createtime='2088-01-01 12:00:00';
explain select * from tb_seller where name='小米科技' or createtime='2088-01-01 12:00:00';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index08.png)
name字段有索引，createtime没有索引，中间用or连接则都不走索引
##### 9.2.9 以%开头的like模糊查询，索引失效
尾部模糊匹配，走索引。头部模糊匹配，不走索引
```mysql
explain select * from tb_seller where name like '%小米';
explain select * from tb_seller where name like '小米%';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index09.png)
##### 9.2.10 若全表扫表比索引快，则不适用索引
```mysql
create index idx_address on tb_seller (address);
show index fron tb_seller;
explain select * from tb_seller where address='武汉市';
explain select * from tb_seller where address='北京市';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index10.png)
在address字段上建立索引，用参数‘武汉市’走索引，‘北京市’不走索引。
##### 9.2.11 is NULL, is not NULL 有时索引失效
```mysql
explain select * from tb_seller where name is null;
explain select * from tb_seller where name is not null;
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/index11.png)
##### 9.2.12 单列索引和复合索引
尽量使用复合索引，少使用单列索引
```mysql
create index idx_name_sta_address on tb_seller(name,status,address);
相当于创建了三个索引：
name, name+status, name+status+address
```
创建单列索引，数据库查询时会选择一个<font color='red'>最优索引</font>(索引基数最大)使用，不会使用全部索引。
#### 9.3 查看索引使用情况
```mysql
show status like 'Handler_read%';
show global status like 'Handler_read%';
```
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/mysql/handler_read.png)
|变量|含义|
|--|--|
|handler_read_first|做全索引扫描的次数。若较高，表示server正执行大量全索引扫描|
|Handler_read_key|一个行被索引值读的次数。越高索引性能约好|
|Handler_read_last|读取最后一个索引的请求次数|
|Handler_read_next|按照键顺序读下一行的请求数。用范围或索引扫描来查索引列时该值增加|
|Handler_read_prev|按照键顺序读前一行的请求数。主要用于order by ... desc|
|Handler_read_rnd|根据固定位置读一行的请求数。较高则效率低|
|Handler_read_rnd_next|在数据文件中读下一行的请求数。较高则表索引不正确|
详细解释见官网  
[server status varibles](https://dev.mysql.com/doc/refman/5.7/en/server-status-variables.html#statvar_Handler_read_first)

### 10 SQL优化