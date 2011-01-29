<?php
$DocumentRoot = "/var/www/html";

require_once("$DocumentRoot/libs/paloSantoInstaller.class.php");
require_once("$DocumentRoot/libs/paloSantoDB.class.php");

$tmpDir = '/tmp/new_module/roomx';  # in this folder the load module extract the package content

$return=1;
$path_script_db="$tmpDir/setup/roomx.sql";
$datos_conexion['user']     = "root";
$datos_conexion['password'] = "eLaStIx.2oo7";
$datos_conexion['locate']   = "";
$oInstaller 				= new Installer();

if(file_exists($path_script_db)){
    //Create database
    $return = $oInstaller->createNewDatabaseMySQL($path_script_db,"roomx",$datos_conexion);
	
    // Add an iclude extension_roomx.conf in the extension_custom.conf at the first line. 
	exec("sed -i '1i#include extension_roomx.conf\n' /etc/asterisk/extensions_custom.conf",$arrConsole,$flagStatus);
	exec("sudo -u root mkdir /var/www/html/roomx_billing",$arrConsole,$flagStatus);
	exec("sudo -u root chown asterisk:asterisk /var/www/html/roomx_billing",$arrConsole,$flagStatus);
	exec("echo '0 0 * * * root mysql -D roomx -u root -peLaStIx.2oo7 < /var/www/html/modules/rx_general/libs/clean.sql' >> /etc/crontab",$arrConsole,$flagStatus);
    // exec("sudo -u root chmod 777 /opt/",$arrConsole,$flagStatus);
    //exec("mkdir -p /opt/elastix/dialer/",$arrConsole,$flagStatus);
    //exec("mv -f $tmpDir/dialer_process/dialer/* /opt/elastix/dialer/",$arrConsole,$flagStatus);

    //$return = ($flagStatus)?2:0;
}
exit($return);
?>
