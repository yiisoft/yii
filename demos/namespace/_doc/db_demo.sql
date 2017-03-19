-- ----------------------------
-- Database
-- ----------------------------
CREATE DATABASE IF NOT EXISTS demo;

-- ----------------------------
-- Table structure for tb_demo
-- ----------------------------
DROP TABLE IF EXISTS `tb_demo`;
CREATE TABLE `tb_demo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `num_a` int(11) DEFAULT '0',
  `num_b` int(11) DEFAULT '0',
  `num_c` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of tb_demo
-- ----------------------------
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
insert into tb_demo(num_a, num_b, num_c) values(floor(rand()*100),floor(rand()*100),floor(rand()*100));
