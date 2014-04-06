UPDATE `roomx`.`config` SET `version` = '0.12.22-97' WHERE CONVERT( `config`.`o_m` USING utf8 ) = 'Hotel';
ALTER TABLE `register` ADD `RACI` varchar(255) NOT NULL;  
ALTER TABLE `register` ADD `RACO` varchar(255) NOT NULL; 
  