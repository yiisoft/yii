/**
 * Database schema required by CDbAuthManager.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 1.0
 */

drop table if exists authassignment;
drop table if exists authitemchild;
drop table if exists authitem;

create table authitem
(
   name                 varchar(64) not null,
   type                 integer not null,
   description          text,
   bizrule              text,
   data                 text,
   primary key (name)
);

create table authitemchild
(
   parent               varchar(64) not null,
   child                varchar(64) not null,
   primary key (parent,child),
   foreign key (parent) references authitem (name) on delete cascade on update cascade,
   foreign key (child) references authitem (name) on delete cascade on update cascade
);

create table authassignment
(
   itemname             varchar(64) not null,
   userid               varchar(64) not null,
   bizrule              text,
   data                 text,
   primary key (itemname,userid),
   foreign key (itemname) references authitem (name) on delete cascade on update cascade
);
