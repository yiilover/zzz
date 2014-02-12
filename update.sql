
/**
 * Created by JetBrains PhpStorm.
 * User: black
 * Date: 14-2-11
 * Time: ÏÂÎç6:07
 * To change this template use File | Settings | File Templates.
 */
alter table zhi_post add slogan varchar(255) not null default '';

delete from zhi_post_cate where id>68;
