-- MySQL dump 10.13  Distrib 5.7.44, for Linux (x86_64)
--
-- Host: localhost    Database: love
-- ------------------------------------------------------
-- Server version	5.7.44-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `love_config`
--

DROP TABLE IF EXISTS `love_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '我们的小世界',
  `subtitle` varchar(255) DEFAULT '把所有的温柔和可爱都存放在这里 ♡',
  `default_date` datetime DEFAULT NULL COMMENT '在一起日期时间',
  `emoji_left` varchar(10) DEFAULT '?',
  `avatar_left` varchar(500) DEFAULT '' COMMENT '左侧头像URL',
  `emoji_right` varchar(10) DEFAULT '?',
  `avatar_right` varchar(500) DEFAULT '' COMMENT '右侧头像URL',
  `footer_text` varchar(255) DEFAULT '你是我遇见最美的意外 ?',
  `show_travel` tinyint(1) DEFAULT '1' COMMENT '显示旅行足迹',
  `show_gallery` tinyint(1) DEFAULT '1' COMMENT '显示甜蜜相册',
  `show_hobbies` tinyint(1) DEFAULT '1' COMMENT '显示爱好',
  `show_together` tinyint(1) DEFAULT '1' COMMENT '显示一起做的事',
  `show_countdown` tinyint(1) DEFAULT '1' COMMENT '显示纪念日倒计时',
  `show_location` tinyint(1) DEFAULT '1' COMMENT '显示定位地图',
  `show_music_player` tinyint(1) DEFAULT '1' COMMENT '显示音乐播放器',
  `music_autoplay` tinyint(1) DEFAULT '1' COMMENT '自动播放音乐',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `music_hide_hours` int(11) DEFAULT '24' COMMENT '关闭播放器后隐藏小时数',
  `page_lock` tinyint(1) DEFAULT '0' COMMENT '页面加密开关',
  `page_password` varchar(100) DEFAULT '' COMMENT '页面访问密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_config`
--

/*!40000 ALTER TABLE `love_config` DISABLE KEYS */;
INSERT INTO `love_config` VALUES (1,'我们的小世界','吃独食','2024-09-21 20:01:00','🧑','https://9255.9255.net/assets/avatars/left_1778573970.jpg','👩','https://9255.9255.net/assets/avatars/right_1778601170.jpg','',1,1,1,1,1,0,1,1,'2026-05-12 16:19:29',1,0,'love');
/*!40000 ALTER TABLE `love_config` ENABLE KEYS */;

--
-- Table structure for table `love_countdown`
--

DROP TABLE IF EXISTS `love_countdown`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_countdown` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '倒计时名称，如结婚日、婚礼日',
  `target_date` date NOT NULL COMMENT '目标日期',
  `description` varchar(255) DEFAULT NULL COMMENT '描述说明',
  `emoji` varchar(10) DEFAULT '?' COMMENT '图标',
  `bg_color` varchar(20) DEFAULT 'pink' COMMENT '卡片颜色',
  `is_active` tinyint(1) DEFAULT '1' COMMENT '是否启用',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_countdown`
--

/*!40000 ALTER TABLE `love_countdown` DISABLE KEYS */;
INSERT INTO `love_countdown` VALUES (1,'结婚日','2026-10-31','','🎉','pink',1,0,'2026-05-12 09:05:15');
/*!40000 ALTER TABLE `love_countdown` ENABLE KEYS */;

--
-- Table structure for table `love_gallery`
--

DROP TABLE IF EXISTS `love_gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `emoji` varchar(10) DEFAULT '?',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_gallery`
--

/*!40000 ALTER TABLE `love_gallery` DISABLE KEYS */;
INSERT INTO `love_gallery` VALUES (1,'第一次约会','','☕',1,'2026-05-12 07:28:10'),(2,'生日快乐',NULL,'🎂',2,'2026-05-12 07:28:10'),(3,'圣诞温馨',NULL,'🎄',3,'2026-05-12 07:28:10'),(4,'跨年倒数',NULL,'🎆',4,'2026-05-12 07:28:10'),(5,'春日散步',NULL,'🌸',5,'2026-05-12 07:28:10'),(6,'夜景漫步',NULL,'🌙',6,'2026-05-12 07:28:10'),(7,'探店美食',NULL,'🍰',7,'2026-05-12 07:28:10'),(8,'撸猫日常',NULL,'🐾',8,'2026-05-12 07:28:10'),(10,'测试','','🛴',9,'2026-05-12 15:08:52'),(11,'测试2','','🏦',11,'2026-05-12 15:09:05');
/*!40000 ALTER TABLE `love_gallery` ENABLE KEYS */;

--
-- Table structure for table `love_hobbies`
--

DROP TABLE IF EXISTS `love_hobbies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_hobbies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('left','right','shared') DEFAULT 'shared',
  `content` varchar(100) NOT NULL,
  `color` varchar(20) DEFAULT 'pink',
  `sort_order` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_hobbies`
--

/*!40000 ALTER TABLE `love_hobbies` DISABLE KEYS */;
INSERT INTO `love_hobbies` VALUES (1,'left','🎮 游戏','blue',1),(2,'left','🏀 运动','green',2),(3,'left','📖 看书','purple',3),(4,'left','🎵 听歌','pink',4),(5,'left','🚗 驾驶','gold',5),(6,'right','👗 拍照','pink',1),(7,'right','🎨 画画','purple',2),(8,'right','🌱 植物','green',3),(9,'right','🧁 烘焙','gold',4),(10,'right','🎬 追剧','blue',5),(11,'shared','🍜 美食探店','pink',1),(12,'shared','🎬 一起看电影','purple',2),(13,'shared','✈️ 旅行','green',3),(14,'shared','🐾 撸猫','gold',4),(15,'shared','🎮 一起玩游戏','blue',5),(16,'shared','🎵 一起听歌','pink',6);
/*!40000 ALTER TABLE `love_hobbies` ENABLE KEYS */;

--
-- Table structure for table `love_location`
--

DROP TABLE IF EXISTS `love_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '地点名称',
  `address` varchar(255) DEFAULT NULL COMMENT '详细地址',
  `lat` decimal(10,6) DEFAULT NULL COMMENT '纬度',
  `lng` decimal(10,6) DEFAULT NULL COMMENT '经度',
  `map_type` enum('baidu','gaode') DEFAULT 'baidu' COMMENT '地图类型',
  `is_show` tinyint(1) DEFAULT '1' COMMENT '是否显示地图',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_location`
--

/*!40000 ALTER TABLE `love_location` DISABLE KEYS */;
/*!40000 ALTER TABLE `love_location` ENABLE KEYS */;

--
-- Table structure for table `love_music`
--

DROP TABLE IF EXISTS `love_music`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_music` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '歌曲名称',
  `artist` varchar(100) DEFAULT '' COMMENT '歌手',
  `audio_url` varchar(500) NOT NULL COMMENT '音频URL',
  `cover_url` varchar(500) DEFAULT '' COMMENT '封面图片',
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_music`
--

/*!40000 ALTER TABLE `love_music` DISABLE KEYS */;
INSERT INTO `love_music` VALUES (1,'存在','汪峰','https://2024love.cn/123.mp3','',10,1,'2026-05-12 14:06:48'),(2,'摆脱地心引力','时代少年团','https://2024love.cn/12.mp3','',0,1,'2026-05-12 15:12:22');
/*!40000 ALTER TABLE `love_music` ENABLE KEYS */;

--
-- Table structure for table `love_notes`
--

DROP TABLE IF EXISTS `love_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_notes`
--

/*!40000 ALTER TABLE `love_notes` DISABLE KEYS */;
INSERT INTO `love_notes` VALUES (1,'测试01','2026-05-12 15:03:37');
/*!40000 ALTER TABLE `love_notes` ENABLE KEYS */;

--
-- Table structure for table `love_together`
--

DROP TABLE IF EXISTS `love_together`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_together` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `emoji` varchar(10) DEFAULT '?',
  `count_label` varchar(50) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_together`
--

/*!40000 ALTER TABLE `love_together` DISABLE KEYS */;
INSERT INTO `love_together` VALUES (1,'一起看日出','早起只为看到第一缕阳光','🌅','1次',1,'2026-05-12 07:28:10'),(2,'一起做饭','厨房里的欢声笑语','🍳','10+次',2,'2026-05-12 07:28:10'),(3,'一起看电影','窝在沙发上的温馨时光','🎬','30+部',3,'2026-05-12 07:28:10'),(4,'一起散步','手牵手走过每个夜晚','🌙','无数次',4,'2026-05-12 07:28:10'),(5,'一起打游戏','赢了抱一下，输了再来','🎮','50+局',5,'2026-05-12 07:28:10'),(6,'一起过生日','每一年都要一起过','🎂','2次',6,'2026-05-12 07:28:10'),(7,'视频通话','再远也要看到你的脸','📱','100+次',7,'2026-05-12 07:28:10'),(8,'一起K歌','跑调也没关系呀','🎵','10+次',8,'2026-05-12 07:28:10'),(9,'一起逛超市','推着购物车挑零食','🛒','15+次',9,'2026-05-12 07:28:10'),(10,'一起许愿','流星划过时许下同一个愿望','⭐','2次',10,'2026-05-12 07:28:10'),(11,'一起淋雨','因为有你，雨也变得浪漫','🌧️','2次',11,'2026-05-12 07:28:10'),(12,'一起跨年','倒数3 2 1 新年快乐！','🎊','2次',12,'2026-05-12 07:28:10');
/*!40000 ALTER TABLE `love_together` ENABLE KEYS */;

--
-- Table structure for table `love_travel`
--

DROP TABLE IF EXISTS `love_travel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_travel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `travel_date` varchar(50) NOT NULL,
  `place` varchar(100) NOT NULL,
  `description` text,
  `emoji` varchar(10) DEFAULT '?',
  `sort_order` int(11) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_travel`
--

/*!40000 ALTER TABLE `love_travel` DISABLE KEYS */;
INSERT INTO `love_travel` VALUES (1,'2025年3月','三亚 · 海南','第一次一起看海，踩在沙滩上，追着日落跑 🌅','🌊',1,'2026-05-12 07:28:10'),(2,'2025年5月','江苏 · 徐州','吃了把子肉，看了龟山汉墓，逛了云龙湖','🚄',2,'2026-05-12 07:28:10'),(3,'2026年5月','广东 · 深圳','去看时代少年团演唱会 📸，那天还下大雨','✈️',3,'2026-05-12 07:28:10'),(4,'2024年11月','上海 · 上海','迪士尼之旅！坐了旋转木马，看了烟花秀 🎆','🏙️',0,'2026-05-12 07:28:10'),(5,'2024年12月','安徽马鞍山','第一次一起去洗澡泡澡','🤩',0,'2026-05-12 08:29:29'),(6,'2026年12月','黑龙江 哈尔滨','计划中（未完成）','📆',5,'2026-05-12 15:21:51');
/*!40000 ALTER TABLE `love_travel` ENABLE KEYS */;

--
-- Table structure for table `love_users`
--

DROP TABLE IF EXISTS `love_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `love_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admin_dir` varchar(50) NOT NULL DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `love_users`
--

/*!40000 ALTER TABLE `love_users` DISABLE KEYS */;
INSERT INTO `love_users` VALUES (1,'admin','$2y$12$AVrfUIERja33xx8wPS9VqORuSz/7NiSvdx43qTY7.yOE3AyKabwfa','admin','2026-05-11 17:23:49');
/*!40000 ALTER TABLE `love_users` ENABLE KEYS */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-13  0:27:59
