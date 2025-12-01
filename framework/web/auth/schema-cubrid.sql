/**
 * Database schema required by CDbAuthManager.
 *
 * @author Esen Sagynov <kadishmal@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 * @since 1.1.14
 */

drop table if exists `AuthAssignment`;
drop table if exists `AuthItemChild`;
drop table if exists `AuthItem`;

create table `AuthItem`
(
   `name`                 varchar(64) not null,
   `type`                 integer not null,
   `description`          varchar(65535),
   `bizrule`              varchar(65535),
   `data`                 string,
   primary key (`name`)
);

create table `AuthItemChild`
(
   `parent`               varchar(64) not null,
   `child`                varchar(64) not null,
   primary key (`parent`,`child`),
   foreign key (`parent`) references `AuthItem` (`name`) on delete cascade on update restrict,
   foreign key (`child`) references `AuthItem` (`name`) on delete cascade on update restrict
);

create table `AuthAssignment`
(
   `itemname`             varchar(64) not null,
   `userid`               varchar(64) not null,
   `bizrule`              varchar(65535),
   `data`                 string,
   primary key (`itemname`,`userid`),
   foreign key (`itemname`) references `AuthItem` (`name`) on delete cascade on update restrict
);
