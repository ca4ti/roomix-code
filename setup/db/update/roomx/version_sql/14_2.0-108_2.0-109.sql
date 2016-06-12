UPDATE `roomx`.`config` SET `vat_1` = '20', `vat_2` = '7', `version` = '2.0-109' WHERE CONVERT( `config`.`o_m` USING utf8 ) = 'Hotel' LIMIT 1 ;
ALTER TABLE `booking` ADD `booking_number` varchar(15) NOT NULL;
ALTER TABLE `register` ADD `booking_number` varchar(15) NOT NULL;