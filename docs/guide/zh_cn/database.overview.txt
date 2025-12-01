Working with Database(数据库开发工作)
=====================

Yii提供了强大的数据库编程支持。Yii数据访问对象(DAO)建立在PHP的数据对象(PDO)extension上，使得在一个单一的统一的接口可以访问不同的数据库管理系统(DBMS)。使用Yii的DAO开发的应用程序可以很容易地切换使用不同的数据库管理系统，而不需要修改数据访问代码。Yii 的Active Record（ AR ），实现了被广泛采用的对象关系映射(ORM)办法，进一步简化数据库编程。按照约定，一个类代表一个表，一个实例代表一行数据。Yii AR消除了大部分用于处理CRUD（创建，读取，更新和删除）数据操作的sql语句的重复任务。

尽管Yii的DAO和AR能够处理几乎所有数据库相关的任务，您仍然可以在Yii application中使用自己的数据库库。事实上，Yii框架精心设计使得可以与其他第三方库同时使用。

<div class="revision">$Id: database.overview.txt 163 2008-11-05 12:51:48Z weizhuo  译：sharehua$</div>