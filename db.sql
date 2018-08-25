CREATE TABLE `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) DEFAULT NULL,
  `data` text,
  `slug` varchar(500) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `bookmark` varchar(400) DEFAULT NULL,
  `visibility` varchar(50) DEFAULT NULL,
  `password` varchar(400) DEFAULT NULL,
  `parentid` int(11) DEFAULT NULL,
  `createdon` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedon` timestamp NULL DEFAULT NULL,
  `order_index` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8