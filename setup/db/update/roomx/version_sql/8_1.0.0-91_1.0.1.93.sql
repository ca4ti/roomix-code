UPDATE `roomx`.`config` SET `version` = '1.0.1-93' WHERE CONVERT( `config`.`o_m` USING utf8 ) = 'Hotel';
ALTER TABLE `register` ADD `pay_mode` VARCHAR( 30 ) NOT NULL AFTER `paid` ;
ALTER TABLE `guest` ADD `NIF` VARCHAR( 30 ) NOT NULL;
ALTER TABLE `guest` ADD `Off_Doc` VARCHAR( 30 ) NOT NULL ;
ALTER TABLE `booking` ADD `payment_mode` VARCHAR( 30 ) NOT NULL , ADD `money_advance` VARCHAR( 15 ) NOT NULL ;
ALTER TABLE `regsiter` ADD `payment_mode_b` VARCHAR( 30 ) NOT NULL , ADD `money_advance` VARCHAR( 15 ) NOT NULL , ADD `payment_mode` VARCHAR( 30 ) NOT NULL, ;
