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
   grep(global search regular expression(RE) and print out the line，全面搜索正则表达式并把行打印出来)是一种强大的文本搜索工具，它能使用正则表达式搜索文本，并把匹配的行打印出来。
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
#### 7.3 awk
### 8.shell杂项
