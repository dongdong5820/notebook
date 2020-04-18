### 1.基础知识
[参考博客](https://myshell-note.readthedocs.io/en/latest/shell-07-%E4%B8%89%E5%89%91%E5%AE%A2-grep.html)

### 2.字符串和数组
### 3.运算符
### 4.流程控制
### 5.函数
### 6.正则表达式
### 7.文本三剑客
#### 7.1 grep
##### 概念
   grep(global search regular expression(RE) and print out the line，全面搜索正则表达式并把行打印出来)是一种强大的<font color='red'>文本搜索工具</font>，它能使用正则表达式搜索文本，并把匹配的行打印出来。
##### 语法格式
```shell
grep [option] ... pattern [file]
grep --help # 查看帮助
```
##### 选项
```text
-v : 反向选择，显示没有匹配到内容的行
-o : 仅显示匹配到的字符
-i : 忽略大小写
-n : 显示匹配的行号
-c : 统计匹配的行数
-q : 静默模式，不输出任务信息(脚本中常用)
-e : 多个选项间的逻辑or，如： grep -e 'cat' -e 'dog' file
-w : 匹配整个单词
-r : 文件夹中递归匹配
-m : 最多显示多少行
-E : 使用正则
-F : 相当于fgrep，不支持正则表达式
-H : 显示匹配的文件名和行号
-An : 显示匹配到字符后面n行
-Bn : 显示匹配到字符前面n行
-Cn : 显示前后各n行
--color=auto : 匹配的文本高亮显示
--include : 包含文件
--exclude : 排除文件
--exclude-dir : 排除目录
```
##### 模式(正则)
元字符：
```text
. : 匹配任意单个字符
[] : 匹配指定范围内的字符
[^] : 匹配指点范围外的任意字符
```
次数匹配(贪婪模式)：
```text
* : 匹配其前的字符任意次
? : 匹配其前的字符0或1次
\{m,n\} : m到n次
\{m,\} : 最少m次
\{0,n\} : 至多n次
\{m\} : m次
```
锚定符：
```text
# 单词锚定：
\< : 锚定词首, \<r..t
\> : 锚定词尾， root\>
# 行首行尾锚定：
^ : 行首，^root
$ : 行尾，root$
.* : 任意长度的任意字符
```
分组和引用：
```text
(abc) : 分组
\1 : 向后引用，引用前面的第一个'('和第一个')'模式匹配到的内容
```
##### 举例
```shell
# 查看系统中root和redis的用户信息
grep -e 'root' -e 'redis' /etc/passwd
# 查看系统中除root和redis用户以外的其他用户
grep -v -e 'root' -e 'redis' /etc/passwd
# 匹配字符串上下文
grep -C5 'include' /etc/nginx/nginx.conf
# 文件夹中递归匹配关键字并输出文件名和行号(日志搜索)
cd /opt/htdocs/sunyanzi002/gearbest-pc/storage/logs
grep -Hrin 'xxx@qq.com'
# 文件夹下递归匹配关键字,在指定(.php,.json结尾)文件中，排除指定目录(.git,vendor,log)
cd /opt/htdocs/sunyanzi002/gearbest-pc/
grep -Hrin -C5 'saveMenEmailSubscribe' --include={*.php,*.json} --exclude-dir={.git,vendor,log}
# 只查看监听80端口的服务
netstat -anl | grep -w 80
# 查看配置文件(去掉注释，去掉空行)
grep -Ev '^$|^#' /etc/redis/redis.conf
# 统计最近远程登录本机的ip地址，按数量倒序
last | grep -E -o "([[:digit:]]{1,3}.){3}[[:digit:]]{1,3}" |sort |uniq -c |sort -r
```
#### 7.2 sed
##### 概念
  sed(Stream EDitor)，流式编辑。<font color='red'>文本处理工具</font>。可处理，编辑文本文件。
##### 语法格式
##### 举例
#### 7.3 awk
##### 概念
   <font color='red'>优良的文本处理工具</font>。这种编程和及数据操作语言(其名称来自于创始人Alfred Aho 、Peter Weinberger 和 Brian Kernighan姓氏的首个字母)最大功能取决于一个人所拥有的知识。
##### 语法格式
```shell
awk [options] 'BEGIN{commands} /partern/{command1;command2} END{commands}' file1,file2...
```
处理机制：
```text
一次从文件中读取出一行，按照特定分隔符对其进行切片(默认空格)
1)读：awk从输入流(文件，管道或标准输入)中读入一行，然后将其载入内存
2)执行：对于每一行，所有awk命令按顺序执行。匹配模式进行过滤。
3)重复：重复1)2)两步直到文件结束。
```
###### 选项
```shell
-F ：指定分隔符
-f ：调用awk脚本
-v ：定义变量
```
###### 程序结构
```shell
# 开始块(Begin)
  BEGIN{commands}
  awk程序启动时(在处理输入流之前)执行，整个过程只执行一次；BEGIN关键字必须大写，开始块是可选项。
# 主体块(Body)
  /partern/{command1;command2}
  对于输入的每一行，都会执行一次主体部分命令。可通过/partern/过滤一些行。
# 结束块(End)
  END{commands}
  awk程序结束时(处理完输入流之后)执行，整个过程只执行一次；END关键字必须大写，结束块是可选项。
```
###### 输出
1、print
```awk
print item1,item2...
# 1)字段之间逗号隔开，输出时以空白字符分割
# 2)item可以省略，此时输出整行即print $0。若想输出空白,可print ""
```
2、printf
```awk
printf <format>,item1,item2...
# 1)format必须指定
# 2)不会自动打印换行符
```
format格式：
```shell
%c ：显示ascall码
%d,%i ： 十进制整数
%f ：浮点数
%s ：字符串
%% ： 显示%自身
- ： 左对齐(默认右对齐)
```
eg:
```awk
awk -F: '{printf "Username:%-26s Uid:%d\n",$1,$3}' /etc/passwd
```
###### 变量
记录变量：
```shell
FS ：field separator，输入字段分隔符(默认空白)
RS ：record separator，输入文本换行符(默认回车)。RS=""按段落读取
OFS ：output field separator,输出字符安分隔符
ORS ： output record separator,输出文本换行符
```
数据变量
```shell
$0 ：整行
$1...$n ： 第1个，第n个字段
NF ：number of fields，当前行的field个数
NR ：number of records，处理文件的行数，多个文件时会累加
FILENAME ： 处理的文件名称
```
自定义变量
```shell
# 变量名区分大小写
awk -v test="abc" 'BEGIN{print test}'
awk 'BEGIN{name="xiaoming";print name}'
```
###### 操作符
```shell
# 算术运算
+,-,*,/,^,%  加减乘除，乘方，取模
# 字符串操作
  无符号表示字符串连接
awk 'BEGIN{str1="Hello,";str2="World";str3=str1 str2;print str3}'
# 赋值操作符
=,+=,-=,*=,/=,^=,%=
awk -F: '{sum+=$3}END{print sum}' /etc/passwd
# 比较操作符
>,>=,<,<=,!=,==
# 模式匹配
~ ： 匹配  
!~ ：不匹配
# 逻辑运算
&&, ||, ! 与或非
# 函数调用
function_name(arg1,arg2...)
# 三元运算
selection ? if-true-expression : if-false-expression
awk -F: '{$3>=100?usertype="common user":usertype="sysadmin";printf "%15s:%s\n",usertype,$1}' /etc/passwd
```
###### 匹配模式partern
```shell
# empty：空模式，匹配每一行
# /regular exprssion/ : 仅处理被模式匹配到的行
df -Th | awk '/^\/dev/{print}'
# relational expression : 关系表达式,为真则处理，为假则过滤
awk -F: '$3>=100{print $1"\t"$3}' /etc/passwd
# 行范围
awk -F: 'NR>2&&NR<=10{print NR"\t"$1}' /etc/passwd
```
###### 控制语句
```shell
# if条件判断
if(condition){statements}[else{statements}]
awk -F: '{if(NR>2&&NR<=10){print NR"\t"$1}}' /etc/passwd
# swith语句
swith(expression){case val1 or /regexp/:statement1;case val2:statements;...;default:statement;}
# while循环
while(condition){statements}
  使用场景：对一行内多个字段逐一类似处理时使用
# do{statements}while(condition)
# for(variable assignment; condition; iteration process){for-body}
  遍历数组，如for(var in array){for-body}
# break, continue关键字
# delete array[index] 删除元素
```
###### 函数
```shell
# substr(str,start,[length]) 
  截取字符串,从start开始。字符串下标从1开始。
# index(str, find)
  在str中查找find子串并返回位置,从1开始。不存在则返回0。
# rand()
  返回0到1的一个随机数
awk 'BEGIN{print rand()}'
# length([s])
  返回指定字符串的长度，s未指定则返回整行长度。
awk -F: '{print $1"\t"length($1)}' /etc/passwd
# sub(find,replace,string)
# gsub(find,replace,string)
  sub函数只在第一个位置替换，gsub函数实现全部替换
echo "a b c 2020-04-18 a:b" | awk 'sub("-","",$4)'
echo "a b c 2020-04-18 a:b" | awk 'gsub("-","",$4)'
```
##### 举例
###### 1. 筛选IPv4地址
```shell
# 从ifconfig命令中筛选出除lo网卡之外的所有ipv4地址(ubuntu系统)
方法一：结合grep
ifconfig | grep Mask | grep -v '127.0.0.1' | awk '{print substr($2,6)}'
方法二：模式匹配
ifconfig | awk '/inet addr/ && !($2 ~ /^addr:127/){print substr($2,6)}'
方法三：按段落匹配
ifconfig | awk 'BEGIN{RS=""}!/lo/{print substr($7,6)}'
ifconfig | awk 'BEGIN{RS="";FS="\n"}!/lo/{$0=$2;FS=" ";$0=$0;FS="\n";print substr($2,6)}'
```
###### 2.读取配置文件中的某段
```shell
# 读取/etc/mysql/mysql.conf.d/mysqld.cnf文件中mysqld下面有效的内容
vim 1.awk
index($0,"[mysqld]"){
	print
	while((getline var) > 0) {
		if (var ~ /\[.*\]/) {
			exit
		}
		if (var ~ /^#/) {
			continue
		}
		print var
	}
}
# getline函数
# >0 : 表示读取到数据
# =0 : 表示遇到结尾EOF
# <0 : 表示读取报错
awk -f 1.awk /etc/mysql/mysql.conf.d/mysqld.cnf
```
###### 3.根据字段去重
```shell
# 去掉uid=xxx重复的行(1.txt)
2020-01-12 12:00_index?uid=123
2020-01-13 13:00_index?uid=123
2020-01-14 14:00_index?uid=333
2020-01-15 15:00_index?uid=9718
2020-01-16 16:00_index?uid=123
2020-01-17 17:00_index?uid=123
2020-01-18 18:00_index?uid=564
2020-01-19 19:00_index?uid=9718
```
```shell
awk -F? '{if(!arr[$2]++){print}}' 1.txt
awk -F? '!arr[$2]++{print}' 1.txt
```
###### 4.统计TCP连接状态数量
```shell
netstat -anl 2>/dev/null | awk '/^tcp/{arr[$6]++}END{for(i in arr){print i"\t"arr[i]}}'
```
###### 5.统计日志中各IP访问非200的次数
```shell
awk '$8!=200{arr[$1]++}END{for(i in arr){print arr[i],i}}' access.log | sort -k1nr | head -n 10
```
### 8.shell杂项
