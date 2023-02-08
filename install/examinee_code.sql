DROP TABLE IF EXISTS `examinee_code`;

CREATE TABLE `examinee_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `examinee_code` VARCHAR(16) NOT NULL,
  `exam_code` varchar(100),
  `status_code` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- status_code
-- 0 - not logged in
-- 1 - logged in
-- 2 - exam started