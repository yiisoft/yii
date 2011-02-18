drop table if exists authassignment cascade;
drop table if exists authitemchild cascade;
drop table if exists authitem cascade;

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
