UPDATE `asterisk`.`admin` SET `value` = 'true' WHERE `admin`.`variable` = 'need_reload';
UPDATE `asterisk`.`sip` SET `data` = 'from-internal' WHERE `data` = 'from-roomx';
UPDATE `asterisk`.`iax` SET `data` = 'from-internal' WHERE `data` = 'from-roomx';
UPDATE `asterisk`.`dahdi` SET `data` = 'from-internal' WHERE `data` = 'from-roomx';