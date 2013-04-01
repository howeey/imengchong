-- DROP TABLE IF EXISTS `mc_user`;
-- CREATE TABLE `qt_user` (
--   `user_id` bigint(20) NOT NULL DEFAULT '0',
--   `renren_id` bigint(20) NOT NULL DEFAULT '0',
--   `user_name` varchar(255) NOT NULL DEFAULT '', 
--   `user_pinyin` varchar(255) NOT NULL DEFAULT '', 
--   `signature` varchar(255) NOT NULL DEFAULT '', 
--   `status` bigint(20) NOT NULL DEFAULT '0',
--   `portrait_id` bigint(20) NOT NULL DEFAULT '0',
--   `create_time` bigint(20) NOT NULL DEFAULT '0',
--   PRIMARY KEY (`user_id`),
--   UNIQUE KEY `renren_id` (`renren_id`),
--   KEY `idx_renrenid` (`renren_id`),
--   KEY `idx_uname` (`user_name`),
--   KEY `idx_porid` (`portrait_id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- /*!40101 SET character_set_client = @saved_cs_client */;

-- --  mc_alloc_user_id
-- DROP TABLE IF EXISTS `mc_alloc_user_id`;
-- CREATE TABLE `mc_alloc_user_id` (
--         `id` bigint(20) NOT NULL DEFAULT '0',
--         PRIMARY KEY (`id`)
--         ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- INSERT INTO `mc_alloc_user_id` VALUES(0);
-- 
-- --  mc_alloc_photo_id
-- DROP TABLE IF EXISTS `mc_alloc_photo_id`;
-- CREATE TABLE `mc_alloc_photo_id` (
--         `id` bigint(20) NOT NULL DEFAULT '0',
--         PRIMARY KEY (`id`)
--         ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- INSERT INTO `mc_alloc_photo_id` VALUES(0);
-- 
-- --  mc_alloc_animal_id
-- DROP TABLE IF EXISTS `mc_alloc_animal_id`;
-- CREATE TABLE `mc_alloc_animal_id` (
--         `id` bigint(20) NOT NULL DEFAULT '0',
--         PRIMARY KEY (`id`)
--         ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
-- INSERT INTO `mc_alloc_animal_id` VALUES(0);
-- 



--  用户
DROP TABLE IF EXISTS `mc_user`;
CREATE TABLE `mc_user` (
        `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `sina_id` bigint(20) NOT NULL DEFAULT '0',
        `user_name` varchar(255) NOT NULL DEFAULT '', 
        `user_alias` varchar(255) NOT NULL DEFAULT '', 
        `user_email` varchar(255) NOT NULL DEFAULT '', 
        `status` bigint(20) NOT NULL DEFAULT '0',
        `portrait_id` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`user_id`),
        UNIQUE sina_id (`sina_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_user_confirm`;
CREATE TABLE `mc_user_confirm` (
        `user_id` bigint(20) NOT NULL DEFAULT '0',
        `md5_checksum` varchar(255) NOT NULL DEFAULT '',
        `is_confirm` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        
DROP TABLE IF EXISTS `mc_user_status`;
CREATE TABLE `mc_user_status` (
        `user_id` bigint(20) NOT NULL DEFAULT '0',
        `focus_count` bigint(20) NOT NULL DEFAULT '0',
        `focused_count` bigint(20) NOT NULL DEFAULT '0',
        `like_animal_count` bigint(20) NOT NULL DEFAULT '0',
        `source_count` text NOT NULL DEFAULT '',
        `user_session` text NOT NULL DEFAULT '',
        `user_session_create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--  用户关注
DROP TABLE IF EXISTS `mc_user_focus`;
CREATE TABLE `mc_user_focus` (
        `focus_user_id` bigint(20) NOT NULL DEFAULT '0',
        `focused_user_id` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`focus_user_id`, `focused_user_id`),
        INDEX query_create_time(`focus_user_id`, `focused_user_id`, `create_time`)
        INDEX query_list_create_time(`focus_user_id`, `create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 宠物
DROP TABLE IF EXISTS `mc_animal`;
CREATE TABLE `mc_animal` (
        `animal_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `user_id` bigint(20) NOT NULL DEFAULT '0',
        `class_id` bigint(20) NOT NULL DEFAULT '0',
        `animal_name` varchar(255) NOT NULL DEFAULT '',
        `sign` varchar(255) NOT NULL DEFAULT '',
        `animal_avatar_id` bigint(20) NOT NULL DEFAULT '0',
        `type` bigint(20) NOT NULL DEFAULT '0',
        `sex` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        `base_info` text NOT NULL,
        `interest_info` text NOT NULL,
        PRIMARY KEY (`animal_id`),
        INDEX query_by_user(`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
DROP TABLE IF EXISTS `mc_animal_avatar_store`;
CREATE TABLE `mc_animal_avatar_store` (
        `animal_avatar_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `avatar_data` text NOT NULL,
        `avatar_resize_l` mediumblob NOT NULL,
        `avatar_resize_m` mediumblob NOT NULL,
        PRIMARY KEY (`animal_avatar_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `mc_animal_status`;
CREATE TABLE `mc_animal_status` (
        `animal_id` bigint(20) NOT NULL DEFAULT '0',
        `liked_count` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`animal_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_animal_liked`;
CREATE TABLE `mc_animal_liked` (
        `animal_id` bigint(20) NOT NULL DEFAULT '0',
        `user_id` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`animal_id`, `user_id`),
        INDEX query_create_time (`animal_id`, `user_id`, `create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--  宠物分类
DROP TABLE IF EXISTS `mc_animal_class`;
CREATE TABLE `mc_animal_class` (
        `type_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `father_type_id` bigint(20) NOT NULL DEFAULT '0',
        `class_name` varchar(255) NOT NULL DEFAULT '',
        `animal_count` varchar(255) NOT NULL DEFAULT '',
        `last_update_time` varchar(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`type_id`),
        INDEX father_type_id(`father_type_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 图片
DROP TABLE IF EXISTS `mc_photo`;
CREATE TABLE `mc_photo` (
        `photo_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `animal_id` bigint(20) NOT NULL DEFAULT '0',
        `user_id` bigint(20) NOT NULL DEFAULT '0',
        `store_ids` varchar(255) NOT NULL DEFAULT '',
        `tag_ids` varchar(255) NOT NULL DEFAULT '',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        `description` text NOT NULL,
        `photo_num` bigint(20) NOT NULL DEFAULT '0',
        `source` bigint(20) NOT NULL DEFAULT '0',
        `type` bigint(20) NOT NULL DEFAULT '0',
        `class` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`photo_id`)
        INDEX query_one_user(`user_id`,`create_time`),
        INDEX query_one_animal_ex(`animal_id`,`create_time`),
        INDEX query_one_animal(`animal_id`, `user_id`, `source`, `create_time`),
        INDEX query_all_animal(`user_id`, `source`, `create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_user_avatar_store`;
CREATE TABLE `mc_user_avatar_store` (
        `portrait_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `avatar_resize_l` mediumblob NOT NULL,
        `avatar_resize_m` mediumblob NOT NULL,
        `avatar_resize_s` mediumblob NOT NULL,
        PRIMARY KEY (`portrait_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `mc_photo_store`;
CREATE TABLE `mc_photo_store` (
        `store_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `photo_data` text NOT NULL,
        `photo_resize_l` mediumblob NOT NULL,
        `photo_resize_m` mediumblob NOT NULL,
        `photo_resize` mediumblob NOT NULL,
        PRIMARY KEY (`store_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_photo_exif`;
CREATE TABLE `mc_photo_exif` (
        `store_id` bigint(20) NOT NULL DEFAULT '0',
        `exif` text NOT NULL,
        PRIMARY KEY (`store_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_photo_status`;
CREATE TABLE `mc_photo_status` (
        `photo_id` bigint(20) NOT NULL DEFAULT '0',
        `meng_count` bigint(20) NOT NULL DEFAULT '0',
        `share_record` text NOT NULL,
        PRIMARY KEY (`photo_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_photo_meng`;
CREATE TABLE `mc_photo_meng` (
        `photo_id` bigint(20) NOT NULL DEFAULT '0',
        `user_id` bigint(20) NOT NULL DEFAULT '0',
        `source` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`photo_id`, `user_id`),
        INDEX query (`user_id`, `create_time`),
        INDEX query_source (`user_id`, `source`, `create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--   图片tag
DROP TABLE IF EXISTS `mc_photo_tag`;
CREATE TABLE `mc_photo_tag` (
        `tag_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `tag_name` varchar(255) NOT NULL DEFAULT '',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`tag_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--  萌宠周边
DROP TABLE IF EXISTS `mc_thing_class`;
CREATE TABLE `mc_thing_class` (
        `type_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `farther_type_id` bigint(20) NOT NULL DEFAULT '0',
        `class_name` varchar(255) NOT NULL DEFAULT '',
        `thing_count` bigint(20) NOT NULL DEFAULT '0',
        `last_update_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`type_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_thing`;
CREATE TABLE `mc_thing` (
        `thing_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `editor_id` bigint(20) NOT NULL DEFAULT '0',
        `thing_class_id` bigint(20) NOT NULL DEFAULT '0',
        `store_ids` varchar(255) NOT NULL DEFAULT '',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        `title` varchar(255) NOT NULL DEFAULT '',
        `detail` text NOT NULL,
        `photo_num` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`thing_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_thing_status`;
CREATE TABLE `mc_thing_status` (
        `thing_id` bigint(20) NOT NULL DEFAULT '0',
        `meng_count` bigint(20) NOT NULL DEFAULT '0',
        `share_record` text NOT NULL,
        PRIMARY KEY (`thing_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_thing_buy`;
CREATE TABLE `mc_thing_buy` (
        `buy_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `thing_id` bigint(20) NOT NULL DEFAULT '0',
        `seller_name` varchar(255) NOT NULL DEFAULT '',
        `seller_link` varchar(255) NOT NULL DEFAULT '',
        `seller_price` varchar(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`buy_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_thing_meng`;
CREATE TABLE `mc_thing_meng` (
        `thing_id` bigint(20) NOT NULL DEFAULT '0',
        `user_id` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`thing_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--  评论(thing and photo)
DROP TABLE IF EXISTS `mc_comment_reffer`;
CREATE TABLE `mc_comment_reffer` (
        `reffer_id` bigint(20) NOT NULL DEFAULT '0',
        `comment_count` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`reffer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_comment`;
CREATE TABLE `mc_comment` (
        `comment_id` bigint(20) NOT NULL AUTO_INCREMENT,
        `reffer_id` bigint(20) NOT NULL DEFAULT '0',
        `user_id` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        `content` text NOT NULL,
        `flag` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`comment_id`),
        index comment_list_desc(`comment_id`, `flag`, `create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--  索引表、关系表
DROP TABLE IF EXISTS `mc_tag_news_link`;
CREATE TABLE `mc_tag_news_link` (
        `tag_id` bigint(20) NOT NULL DEFAULT '0',
        `reffer_id` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`tag_id`,`reffer_id`),
        INDEX tag_time(`tag_id`,`create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_tag_meng_link`;
CREATE TABLE `mc_tag_meng_link` (
        `tag_id` bigint(20) NOT NULL DEFAULT '0',
        `reffer_id` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`tag_id`,`reffer_id`),
        INDEX tag(`tag_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_class_news_link`;
CREATE TABLE `mc_class_news_link` (
        `class_id` bigint(20) NOT NULL DEFAULT '0',
        `reffer_id` bigint(20) NOT NULL DEFAULT '0',
        `create_time` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`class_id`,`reffer_id`),
        INDEX class_time(`class_id`,`create_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `mc_class_meng_link`;
CREATE TABLE `mc_class_meng_link` (
        `class_id` bigint(20) NOT NULL DEFAULT '0',
        `reffer_id` bigint(20) NOT NULL DEFAULT '0',
        PRIMARY KEY (`class_id`,`reffer_id`),
        INDEX class(`class_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;


