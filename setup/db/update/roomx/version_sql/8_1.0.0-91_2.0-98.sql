UPDATE `roomx`.`config` SET `version` = '2.0-98' WHERE CONVERT( `config`.`o_m` USING utf8 ) = 'Hotel';
ALTER TABLE `guest` ADD `NIF` VARCHAR( 30 ) NOT NULL, ADD `Off_Doc` VARCHAR( 30 ) NOT NULL ;
ALTER TABLE `booking` ADD `payment_mode` VARCHAR( 30 ) NOT NULL , ADD `money_advance` VARCHAR( 15 ) NOT NULL ;
ALTER TABLE `regsiter` ADD `payment_mode_b` VARCHAR( 30 ) NOT NULL , ADD `money_advance` VARCHAR( 15 ) NOT NULL , ADD `payment_mode` VARCHAR( 30 ) NOT NULL, ;

