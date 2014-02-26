
/**
 * Created by JetBrains PhpStorm.
 * User: black
 * Date: 14-2-11
 * Time: ����6:07
 * To change this template use File | Settings | File Templates.
 */
alter table zhi_post add slogan varchar(255) not null default '';
alter table zhi_mall add email varchar(255)  default '';
alter table zhi_mall add tel varchar(255)  default '';
alter table zhi_mall add addr varchar(255)  default '';

CREATE TABLE `zhi_mall_cate_re` (
  `mall_id` int(10) NOT NULL,
  `cate_id` smallint(4) NOT NULL,
  UNIQUE KEY `mall_id` (`mall_id`,`cate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8

CREATE TABLE `zhi_mall` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` varchar(50) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `domain` varchar(255) NOT NULL,
  `abst` varchar(255) NOT NULL,
  `info` text,
  `url_dm` varchar(255) NOT NULL,
  `url_yqf` varchar(255) NOT NULL,
  `url_other` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `cps` varchar(255) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `cid` int(11) DEFAULT '0',
  `rebates` varchar(20) NOT NULL,
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `status` int(11) DEFAULT '1',
  `ordid` int(11) DEFAULT '0',
  `add_time` int(11) DEFAULT '0',
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_keys` varchar(255) DEFAULT NULL,
  `seo_desc` text,
  `index` varchar(2) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `addr` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8
