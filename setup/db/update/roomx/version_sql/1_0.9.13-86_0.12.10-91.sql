UPDATE `roomx`.`config` SET `version` = '0.12.10-91' WHERE CONVERT( `config`.`o_m` USING utf8 ) = 'Hotel';
ALTER TABLE `models` CHANGE `room_price` `room_price` DECIMAL( 12, 2 ) NOT NULL DEFAULT '0.00';
ALTER TABLE `models` CHANGE `room_guest` `room_guest` DECIMAL( 12, 2 ) NOT NULL DEFAULT '0.00';
ALTER TABLE `minibar` CHANGE `price` `price` DECIMAL( 10, 2 ) NOT NULL;
ALTER TABLE `register` CHANGE `total_room` `total_room` DECIMAL( 12, 2 ) NOT NULL; 
ALTER TABLE `register` CHANGE `total_bar` `total_bar` DECIMAL( 12, 2 ) NOT NULL;
ALTER TABLE `register` CHANGE `total_call` `total_call` DECIMAL( 12, 2 ) NOT NULL; 
ALTER TABLE `register` CHANGE `total_billing` `total_billing` DECIMAL( 12, 2 ) NOT NULL; 
ALTER TABLE `config` ADD `rounded` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `vat_2`;