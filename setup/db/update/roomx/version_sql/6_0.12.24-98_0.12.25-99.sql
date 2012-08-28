UPDATE `roomx`.`config` SET `version` = '0.12.25-99' WHERE CONVERT( `config`.`o_m` USING utf8 ) = 'Hotel';
ALTER TABLE `rooms` ADD `RACI` varchar(255) NOT NULL; 
ALTER TABLE `rooms` ADD `RACO` varchar(255) NOT NULL;
ALTER TABLE `register` DROP `RACI`,DROP `RACO`;