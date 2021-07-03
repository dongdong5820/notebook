## 依赖管理

- 父项目做依赖管理

```xml
<!-- 父依赖 -->
<parent>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-parent</artifactId>
    <version>2.4.4</version>
    <relativePath/> <!-- lookup parent from repository -->
</parent>

<!-- 他的父项目 -->
<parent>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-dependencies</artifactId>
    <version>2.4.4</version>
</parent>
<!-- 这里几乎申明了所有开发中常用依赖的版本号，自动版本仲裁机制 -->
```

- 开发导入starter场景启动器

  ```xml
  1、见到很多 spring-boot-starter-* : *代表场景
  2、只要引入starter，这个场景的所有常规需要的依赖我们都自动引入
  3、SpringBoot所有支持的场景
  https://docs.spring.io/spring-boot/docs/current/reference/html/using-spring-boot.html#using-boot-starter
  4、见到的 *-spring-boot-starter: 第三方为我们提供的简化开发的场景启动器
  5、所有场景启动器最底层的依赖
  <dependency>
      <groupId>org.springframework.boot</groupId>
      <artifactId>spring-boot-starter</artifactId>
      <version>2.4.4</version>
      <scope>compile</scope>
  </dependency>
  ```

- 无需关注版本号，自动版本仲裁

  ```text
  1、引入依赖默认都可以不写版本
  2、引入非版本仲裁的jar，要写版本号
  ```

## 自动配置

- @SpringBootApplication注解

  ```xml
  @SpringBootApplication 等同于
  @SpringBootConfiguration
  @EnableAutoConfiguration
  @ComponentScan(excludeFilters = { @Filter(type = FilterType.CUSTOM, classes = TypeExcludeFilter.class),@Filter(type = FilterType.CUSTOM, classes = AutoConfigurationExcludeFilter.class) })
  ```

## 配置文件

加载顺序： springboot启动会扫描以下位置的`application.properties`和`application.yml`文件作为springboot的默认配置文件：

```text
1、优先级1：项目路径下config文件夹配置文件
2、优先级2：项目路径下配置文件
3、优先级3：资源路径下config文件夹配置文件
4、优先级4：资源路径下配置文件
同一目录下application.properties比application.yml优先级要高
优先级由高到低，高优先级的配置会覆盖低优先级的配置
```

**springboot会从这四个位置全部加载主配置文件；互补配置**

## 整合JDBC

### 1 添加依赖

```xml
<!-- 整合JDBC -->
<dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-jdbc</artifactId>
</dependency>
<dependency>
    <groupId>mysql</groupId>
    <artifactId>mysql-connector-java</artifactId>
    <scope>runtime</scope>
</dependency>
```

## 2 配置yml文件

```yml
spring:
  datasource:
    username: homestead
    password: secret
    driver-class-name: com.mysql.cj.jdbc.Driver
    url: jdbc:mysql://192.168.58.10:3306/mybatis?serverTimezone=UTC&useSSL=false&useUnicode=true&characterEncoding=utf-8
```

## 3 验证

直接在测试类中验证

```java
@SpringBootTest
class SpringbootApplicationTests {
    @Autowired
    DataSource dataSource;

    @Test
    void contextLoads() throws SQLException {
        // com.zaxxer.hikari.HikariDataSource
        System.out.println(dataSource.getClass());
        Connection connection = dataSource.getConnection();
        // HikariProxyConnection@170050776 wrapping com.mysql.cj.jdbc.ConnectionImpl@45cd7bc5
        System.out.println(connection);
        connection.close();
    }
}
```

默认给配置成 com.zaxxer.hikari.HikariDataSource 类

