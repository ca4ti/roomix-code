UPDATE `roomx`.`config` SET `version` = '0.12.20-96' WHERE CONVERT( `config`.`o_m` USING utf8 ) = 'Hotel';
ALTER TABLE `register` ADD `remote_folio` varchar(255) NOT NULL ;
