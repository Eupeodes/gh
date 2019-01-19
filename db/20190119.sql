CREATE TABLE `dow_holidays` (
		  `date` date NOT NULL,
		  `holiday` text NOT NULL,
		  `year` int(11) NOT NULL,
		  `recuring` int(1) NOT NULL DEFAULT '0',
		  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
