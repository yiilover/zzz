
/**
 * Created by JetBrains PhpStorm.
 * User: black
 * Date: 14-2-11
 * Time: обнГ6:07
 * To change this template use File | Settings | File Templates.
 */
alter table zhi_post add slogan varchar(255) not null default '';
alter table zhi_mall add email varchar(255) not null default '';
alter table zhi_mall add tel varchar(255) not null default '';
alter table zhi_mall add addr varchar(255) not null default '';

CREATE TABLE `zhi_mall_cate_re` (
  `mall_id` int(10) NOT NULL,
  `cate_id` smallint(4) NOT NULL,
  UNIQUE KEY `mall_id` (`mall_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
