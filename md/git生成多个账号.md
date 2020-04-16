### 管理git生成多个ssh key
#### 问题阐述
   当有多个git账号的时候，比如一个github，用户自己一些开发活动。再来一个gitlab，一般是公司内部的git。这两者你的邮箱如果不同的话，就会涉及到一个问题，生成第二个git的key就会覆盖第一个key，导致必然一个用不了。
#### 问题解决
   我们可以在~/.ssh目录下新建一个config文件配置一下
#### 具体步骤
**1、生成第一个ssh key（这里我用gitlab，用的是公司邮箱）**
```shell
ssh-keygen -t rsa -C "xxx@globalegrow.com"
```
一路回车，之后在~/.ssh目录下会生成两个文件id_rsa,id_rsa.pub（一个公钥，一个私钥）
**2、生成第二个ssh key（这里我用github，用的是QQ邮箱）**
```shell
ssh-keygen -t rsa -C "xxx@qq.com"
```
这里不要一路回车，第一步让你选择存在key的时候写个名字，比如 id_rsa_github，之后一路回车，然后在~/.ssh目录下会生成两个文件id_rsa_github,id_rsa_github.pub。目录文件如图：
![](https://raw.githubusercontent.com/dongdong5820/bedOfImage/master/other/git-mutil.png)
**3、打开ssh-agent，添加私钥**

```shell
eval $(ssh-agent -s)
ssh-add ~/.ssh/id_rsa
ssh-add ~/.ssh/id_rsa_github
```
**4、创建并修改config文件**

```shell
# gitlab
Host gitlab.xxx.com
  HostName gitlab.xxx.com	// 这里填公司的gitlab网址
  PreferredAuthentications publickey
  IdentityFile ~/.ssh/id_rsa
  User username1

# github
Host github.com
  HostName github.com
  PreferredAuthentications publickey
  IdentityFile ~/.ssh/id_rsa_github
  User username2
```
**5、在github和gitlab上添加公钥即可(这里不再多说)， 测试**
```shell
ssh -vT git@gitlab.xxx.com
ssh -vT git@github.com
```
#### 补充一下
   如果之前有设置全局用户名和邮箱的话，需要unset一下
```shell
git config --global --unset user.name
git config --global --unset user.email
```
   然后再不同的仓库下设置局部的用户名和邮箱
```shell
git config user.name "username1"
git config user.name "youremail"
```