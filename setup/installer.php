<?php

$DocumentRoot = (isset($_SERVER['argv'][1]))?$_SERVER['argv'][1]:"/var/www/html";
require_once("$DocumentRoot/libs/misc.lib.php");
require_once("$DocumentRoot/libs/paloSantoInstaller.class.php");
require_once("$DocumentRoot/libs/paloSantoDB.class.php");

$tmpDir = '/tmp/new_module/roomx';  # in this folder the load module extract the package content

$return=1;
$path_script_db		= "$tmpDir/setup/install.sql";
$path_script_db_update	= "$tmpDir/setup/update.sql";
$datos_conexion['user']     = "root";
$datos_conexion['password'] =  obtenerClaveConocidaMySQL('root',"$DocumentRoot"."/");
$datos_conexion['locate']   = "";
$oInstaller                 = new Installer(); 

if(file_exists($path_script_db)){
    //Create database
    $return = $oInstaller->createNewDatabaseMySQL($path_script_db,"roomx",$datos_conexion);
	
    // Add an include extensions_roomx.conf in the extension_custom.conf at the first line. 
    if (!exec("more /etc/asterisk/extensions_custom.conf |grep room")) {
	exec("sed -i '1i#include extensions_roomx.conf\n' /etc/asterisk/extensions_custom.conf",$arrConsole,$flagStatus);
	//  Create roomx_billing folder
	//----------------------------------
	exec("sudo -u root chmod 777 /var/www/html/",$arrConsole,$flagStatus);
	mkdir("/var/www/html/roomx_billing",0755);
	exec("sudo -u root touch /var/www/html/roomx_billing/index.html",$arrConsole,$flagStatus);
	exec("sudo -u root chown asterisk:asterisk /var/www/html/roomx_billing",$arrConsole,$flagStatus);
	exec("sudo -u root chmod 755 /var/www/html/",$arrConsole,$flagStatus);

	// Add a line into the contrab file.
	// This line is used to put "room not clean" at midnight. 
	//--------------------------------------------------------
	exec("sudo -u root chmod 777 /etc/",$arrConsole,$flagStatus);
	exec("sudo -u root chmod 777 /etc/crontab",$arrConsole,$flagStatus);
       $cmd = "echo '0 0 * * * root mysql -D roomx -u root -p".obtenerClaveConocidaMySQL('root','/var/www/html/')." < /var/www/html/modules/rx_general/libs/clean.sql' >> /etc/crontab";
	exec($cmd,$arrConsole,$flagStatus);
	exec("sudo -u root chmod 644 /etc/crontab",$arrConsole,$flagStatus);
	exec("sudo -u root chmod 755 /etc/",$arrConsole,$flagStatus);
    }
    else
    {
	$return = $oInstaller->createNewDatabaseMySQL($path_script_db_update,"roomx",$datos_conexion);
    }
}
exit($return);
?>
