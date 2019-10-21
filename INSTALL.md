## Shopping System By Laravel

### 网购商城
- 基于Laravel框架构建的网购商城；
- 实现与SOA版本的对接功能模块划分；
- 基于php7.1+laravel，开发环境推荐使用vagrant 

### 开发&测试环境推荐 ###
- window下开发，利用xdebug远程联调：
	- xdebug的简单说明：`http://52explore.com/article/217`
- virtual Box的安装(5.1.30版本，5.2的版本之前提示兼容问题)： 
	- 下载地址：`https://www.virtualbox.org/wiki/Download_Old_Builds_5_1`
- vagrant的安装(2.0.0版本)： 
	- 下载地址：`https://www.vagrantup.com/`
	- vagrant的安装篇：`http://52explore.com/article/190`
- Homestead环境配置：
	- github地址：`https://github.com/laravel/homestead.git`
- Laravel Homestead Box安装(Laravel Homestead 是一个官方预封装的 Vagrant Box，提供了一个完美的开发环境，你无需在本地安装 PHP 、web 服务器或任何服务软件) :
	- 安装命令： `vagrant box add laravel/homestead`
	- 内置软件： 
		- Ubuntu 16.04
		- Git
		- PHP 7.1
		- Nginx
		- MySQL
		- MariaDB
		- Sqlite3
		- Postgres
		- Composer
		- Node (带有 Yarn、Bower、Grunt 和 Gulp)
		- Redis
		- Memcached
		- Beanstalkd
		- Mailhog
		- ngrok

#### 相关软件共享  ####
- `\\10.40.2.32\公共共享文档\软件\vagrant相关软件`
- 账号： wzh_doc 
- 密码： wzh_doc

#### 安装明细： ####

#### 1. virtualbox以及vargrant的安装直接略过。 ####

#### 2. 下载`laravel/homestead`的安装包： ####
- 在线安装（国内通常被墙了）：vagrant box add laravel/homestead
- 离线安装（本地安装）：可以考虑自己找一些laravel/homestead的box的源，或者公司其他同事已经下载好的，可repackage出来：
	- 假定下载好的离线box存储路径为：H:\DeveloperSoftWare\virtualbox-homestead4.0.box
	- 在目录下，创建一个metadata.json，用于对vagran box一些说明:
	```
	  {
	      "name": "laravel/homestead",
	      "versions": [{
	          "version": "4.0.0",
	          "providers": [{
	              "name": "virtualbox",
	              "url": "file:///H:/DeveloperSoftWare/virtualbox-homestead4.0.box"
	          }]
	      }]
	  }
	```
	- 执行`vagrant.exe box add metadata.json`，然后通过`vagrant.exe box list`就可以查看到：
	```
	$ vagrant.exe box list
	laravel/homestead (virtualbox, 4.0.0)
	```
- 上述步骤完成后，继续后续的laravel/homestead的配置安装步骤即可。

#### 3. Windows环境，利用git bash， 克隆Laravel官方的Vagrantbox环境配置到 `~/Homestead` ####
```
[进到家目录]
cd ~
git clone https://github.com/laravel/homestead.git Homestead
[选择稳定版本]
git checkout v6.5.0
[初始化，并创建Homestead.yaml配置文件]
bash init.sh
```

#### 4. 配置Homestead.yaml  ####
> Homestead.yaml属于Homestead的配置文件，虚拟主机的相关配置，以及目录映射（win-linux共享）可以通过在这块完成

```
[配置提供器]
provider: virtualbox	#如果用了其他虚拟机比如vmware_workstation，这块提供商会不同

[和宿主机器共享文件夹]
folders: # 默认以vboxsf文件类型挂载，vagrant会把window`本地的map目录`映射到linux的`to目录
    - map: F:\windowsShare
      to: /home/vagrant/www

# 针对nfs方式和rsync共享方式开发同学，可以忽略，了解即可。
folders:	# 以nfs文件类型挂载
    - map: ~/Code
      to: /home/vagrant/Code
      type: "nfs"

folders:	# 以rsync同步方式，共享文件
    - map: ~/Code
      to: /home/vagrant/Code
      type: "rsync"
      options:
          rsync__args: ["--verbose", "--archive", "--delete", "-zz"]
          rsync__exclude: ["node_modules"]

[配置 Nginx 站点，类似于Vhost配置 ]
# vagrant会把map定义域名解析linux的`to目录`（具体情况视个人本地目录做相应调整）
# params是服务器环境变量，php可以按项目指定版本
# PS: 建议大家统一站点域名，保证整体统一

sites:
    - map: local-vagrant-5.6
      to: /home/vagrant/www/testProject/5.6
      php: "5.6"
      params:
        - key: ENV
          value: DEV
    - map: local-vagrant
      to: /home/vagrant/www/openplatform/m/public
      php: "7.1"
    - map: m-soa.demoshow.net
      to: /home/vagrant/www/openplatform/m/public
      php: "7.1"
    - map: www-soa.demoshow.net
      to: /home/vagrant/www/openplatform/www/public
      php: "7.1"
```

#### 5. 解析Host ####
```
192.168.10.10	homestead.test	local-vagrant local-vagrant-5.6
192.168.10.10	m-soa.gearbest.net
192.168.10.10	www-soa.gearbest.net
```

#### 6. vagrant的常见操作 ####
```
vagrant up 			# 启动vagrant box，也就是linux虚拟机；
vagrant box list	# 查看当前的vagrant box清单
vagrant.exe box repackage laravel/homestead virtualbox 4.0.0	#打包vagrantbox
vagrant ssh			# 连接vagrant box
vagrant reload		# 加载最新的VagrantFile，并重启vagrant box
其他跟多的，可以自行vagrant --help
```

#### 7. www-soa.gearbest.com初始化 ####
```
mkdir -p ~/www/openplatform/
cd ~/www/openplatform/
git clone http://gitlab.egomsl.com/dz-department/php-openplatform/www-soa www

[配置composer国内代理，安装php依赖]
composer config -g repo.packagist composer https://packagist.phpcomposer.com
composer install

[配置淘宝代理，安装nodejs依赖]
npm install -g cnpm --registry=https://registry.npm.taobao.org
cnpm install 或 yarn（推荐）
```

### 常见问题 ###

#### 1. vagrant box安装问题 ####
> 仔细查看文档，检测是否存在遗漏，win下的bash环境，建议直接用git-bash就可以了（不推荐用cygwin的，需要安装的相关依赖包比较多）

#### 2. PHP的composer包安装慢？ ####
> 修改 composer 的全局配置文件，参考`https://pkg.phpcomposer.com/`

```
composer config -g repo.packagist composer https://packagist.phpcomposer.com
```

#### 3. 前端npm安装慢？ ####
> 改用淘宝代理，参考`https://npm.taobao.org/`

```
$ npm install -g cnpm --registry=https://registry.npm.taobao.org
```

#### 4. 在Homestead.yaml更改的配置reload后没有生效？ ####
> 尝试 vagrant provision 或者 vagrant reload --provision命令重新加载配置

```
vagrant provision
或者
vagrant reload --provision
```

#### 5. 虚拟化错误 ####
> 检测自己机器的BIOS下面的虚拟化是否已经开启，Intel的CPU如果之前没有用过虚拟机之类的，很可能Intel的CPU虚拟化没有开启。

### 其他 ###

#### Redis ####
> vagrant上面的Redis服务是bind的本地的，可以改成`bind 0.0.0.0`，这样就可以从Host机器通过Redis Desk版本访问；

#### DB(可忽略) ####
> 考虑到商城一部分数据是基于SOA服务来的，另一部分需要基于DB，这块目前以及有OBS这块的数据库做存储了，如果还有需要，可以参考下列步骤：

```
[创建DB]
mysql> create database gb_shopping_system CHARACTER SET = 'utf8mb4' collate = 'utf8mb4_unicode_ci';
Query OK, 1 row affected (0.01 sec)

```

### 目录树 ###
```
[根目录]
www/
├── app
├── bootstrap
├── config
├── database
├── node_modules
├── public
├── resources
├── routes
├── storage
├── tests
└── vendor

[app目录]
app/
├── Combines
├── Console
├── Exceptions
├── Gadgets
├── Http
├── Models
├── Providers
└── Servers
```

#### 目录说明 ####
- 根目录方面，和L-框架基本上没有区别，不做赘述（vendor、node_modules都是不随版本管控的）
- app目录：
	- 业务关连的紧密的：
		- Combines: 组合器层，负责Models、Servers等基础数据服务的组装器，涉及基础源数据的处理和加工，用于提供给到Controller使用和调用，对应的缓存尽量在这层做；
		- Models: DB模型层，用户针对对应模块纯DB的相关操作，不应涉及对应的业务逻辑处理（或者仅可能的精简逻辑处理）；
		- Servers: SOA服务层，服务这块主要是涉及SOA服务（但不限SOA服务）；若是非SOA的服务，无需继承对应的SoaServer基类。
		- Exceptions: 针对不同类型的异常统一处理地方（对异常不清楚的，可以先不用考这块）；
	- Providers:
		- 偏框架和整体Web应用的服务提供，而非业务的服务提供；（比如Redis、路由、认证、广播、Response等）

### Happy development !! ###

### 框架剩余内容 ###
1. 多语言问题；
2. 日志问题；