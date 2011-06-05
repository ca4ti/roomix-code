<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-18                                               |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.1 2010-03-28 08:03:30 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoCheckIn.class.php";
    $DocumentRoot = (isset($_SERVER['argv'][1]))?$_SERVER['argv'][1]:"/var/www/html";
    require_once("$DocumentRoot/libs/misc.lib.php");

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['dsn_conn_database']);
    $pDB_Ast = new paloDB("mysql://root:".obtenerClaveConocidaMySQL('root')."@localhost/asterisk");

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = saveNewCheckIn($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Ast, $arrConf, $arrLang);
            break;
        default: // view_form
            $content = viewFormCheckIn($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Ast, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function viewFormCheckIn($smarty, $module_name, $local_templates_dir, &$pDB, &$pDB_Ast, $arrConf, $arrLang)
{
    $pCheckIn = new paloSantoCheckIn($pDB);
    $arrFormCheckIn = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormCheckIn);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    $_DATA['num_guest'] = 1;

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataCheckIn = $pCheckIn->getCheckInById($id);
        if(is_array($dataCheckIn) & count($dataCheckIn)>0)
            $_DATA = $dataCheckIn;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pCheckIn->errMsg);
        }
    }
    $smarty->caching = 0;
    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("BOOKING","<a style='text-decoration: none;' href='./index.php?menu=rx_booking_status'><button type='button'>".$arrLang['Show']."</button></a>");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["Check In"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewCheckIn($smarty, $module_name, $local_templates_dir, &$pDB, &$pDB_Ast, $arrConf, $arrLang)
{
    $pCheckIn = new paloSantoCheckIn($pDB);
    $pCheckIn_Ast = new paloSantoCheckIn($pDB_Ast);
    $arrFormCheckIn = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormCheckIn);
    $_DATA = $_POST;

    $arrCheckBooking	= $pCheckIn->Check_Booking($_DATA['room'],$_DATA['date'],$_DATA['date_co']); 

    if ($arrCheckBooking == 0){

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", $arrLang["Validation Error"]);
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>{$arrLang['The following fields contain errors']}:</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        $content = viewFormCheckIn($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Ast, $arrConf, $arrLang);
    }
    else{
        //Save all Datas into the table guest.

        $value_guest['first_name'] = "'".$_DATA['first_name']."'";
        $value_guest['last_name']  = "'".$_DATA['last_name']."'";
        $value_guest['address']    = "'".$_DATA['address']."'";
        $value_guest['cp']         = "'".$_DATA['cp']."'";
        $value_guest['city']       = "'".$_DATA['city']."'";
        $value_guest['phone']      = "'".$_DATA['phone']."'";
        $value_guest['mobile']     = "'".$_DATA['mobile']."'";
        $value_guest['fax']        = "'".$_DATA['fax']."'";
        $value_guest['mail']       = "'".$_DATA['mail']."'";
	 
	 //Test if the guest is already exist. 
        //---------------------------------------------
        $conditions = "WHERE first_name = '".$_DATA['first_name'].
                      "' and last_name = '".$_DATA['last_name'].
                      "' and address ='".$_DATA['address'].
                      "' and cp = '".$_DATA['cp'].
                      "' and city = '".$_DATA['city'].
                      "' and phone = '".$_DATA['phone'].
                      "' and mobile = '".$_DATA['mobile'].
                      "' and fax = '".$_DATA['fax'].
                      "' and mail = '".$_DATA['mail']."'";
			// Catch the guest_id
			//----------------------

	 		$arrGuestID 	= $pCheckIn->get_ID_Gest($conditions);
        		$GuestID 	= $arrGuestID[0];
	 		$news_guest 	= "Guest already exist, ";

	 if (!isset($GuestID)){
		// New Guest
		//------------------

        	$arrGuest   		= $pCheckIn->insertQuery('guest',$value_guest);
	 	$arrGuestID 		= $pCheckIn->get_ID_Gest($conditions);
        	$GuestID    		= $arrGuestID[0];
		$news_guest 		= $arrLang["New guest"];

	 }

        // Save all Datas into the table register. 
        //---------------------------------------------
        $value_register['room_id']   = "'".$_DATA['room']."'";
        $value_register['guest_id']  = "'".$GuestID['id']."'";
        $value_register['date_ci']   = "'".$_DATA['date']."'";
        $value_register['date_co']   = "'".$_DATA['date_co']."'";
        $value_register['num_guest'] = "'".$_DATA['num_guest']."'";
	 if ($_DATA['booking'] == "off"){
        	$value_register['status']    = "'1'";
        	$arrRegister 		  = $pCheckIn->insertQuery('register',$value_register);
	 	}
	 else
	 	{
		$arrRegister 		  = $pCheckIn->insertQuery('booking',$value_register);
	 	}
	 
        // Update the room status (Free -> Busy)
	 // Put the guest name into the room.
        //---------------------------------------------
	 if ($_DATA['booking'] == "off"){
	 	$guest_name 			= str_replace("'","",$value_guest['first_name']." ".$value_guest['last_name']);
       	$value_rooms['free'] 	= '0'; 
        	$value_rooms['guest_name']  = "'".$guest_name."'";
        	$where 			= "id = '".$_DATA['room']."'";
        	$arrRegister 			= $pCheckIn->updateQuery('rooms',$value_rooms, $where);
	 }

	 // Update status table.
	 //---------------------
	 $free				= $pCheckIn->Free();				// Take all free rooms
	 $busy				= $pCheckIn->Busy();				// Take all busy rooms
	 $booking			= $pCheckIn->getBookingStatus();		// Take all booking of the day. 

        $value_status['free']  	= strval($free);
        $value_status['busy']   	= strval($busy);
        $value_status['booking']   = strval($booking);
	  
	 $arrStatus	 		= $pCheckIn->UpdateStatus($value_status);	// At first, creating the day if not exist
	 $arrStatus	 		= $pCheckIn->UpdateStatus($value_status);	// Next, re-sending request to update free, busy, and booking

	 // Take the rooms extension from id 
        //---------------------------------------------
        $where 			= "WHERE id = '".$_DATA['room']."'";
        $arrRooms 			= $pCheckIn->getCheckIn('rooms',$where);
        $Rooms 			= $arrRooms['0'];

        // Modify the account code extension into Freepbx data
        //---------------------------------------------
	 if ($_DATA['booking'] == "off"){
        	$value_rl['value']  	= "'true'";
        	$where              	= "variable = 'need_reload';";
        	$arrReload          	= $pCheckIn_Ast->updateQuery('admin',$value_rl, $where);

        	$value_ac['data']   	= "'".$GuestID['id']."'";
        	$where              	= "id = '".$Rooms['extension']."' and keyword = 'accountcode';";
        	$arrAccount         	= $pCheckIn_Ast->updateQuery('sip',$value_ac, $where);

        	$cmd			="/var/lib/asterisk/bin/module_admin reload";
        	exec($cmd);
	 }

        // Unlock the extension 
        //---------------------------------------------
	 if ($_DATA['booking'] == "off"){
	 	$cmd 			= "/usr/sbin/asterisk -rx 'database put LOCKED ".$Rooms['extension']." 0'";
		exec($cmd);
	 }

        // Call Between rooms enabled or not.
        //---------------------------------------------
        $strMsg 			= $news_guest." ".$arrLang["Booking Done"];
	 if ($_DATA['booking'] == "off"){
        	$where 		= "";
        	$arrConfig 		= $pCheckIn->getCheckIn('config',$where);
        	$arrAstDB 		= $arrConfig['0'];

        	$cmd			= "/usr/sbin/asterisk -rx 'database put CBR ".$Rooms['extension']." ".$arrAstDB['cbr']."'";
        	exec($cmd);

        	$strMsg 		= $news_guest." ".$arrLang["Checkin Done"];
	 }
	 
        $smarty->assign("mb_message", $strMsg);
    }
    }
    else
    {
    	$smarty->assign("mb_message", $arrLang["Already Booked"]);
    }
    $content 			= viewFormCheckIn($smarty, $module_name, $local_templates_dir, $pDB,$dDP_Ast, $arrConf, $arrLang);
    return $content;
}

function createFieldForm($arrLang, &$pDB)
{
    $pRoom= new paloSantoCheckIn($pDB);

    // Test if the room must be clean before CheckIn
    //-----------------------------------------------
    $arrConf=$pRoom->getCheckIn('config',"");
    $arrRmbc=$arrConf[0];
    $rmbc=$arrRmbc['rmbc'];

    // Displaying Rooms
    //------------------
    $where = "WHERE free = '1' ORDER BY `extension` ASC";
    if ($rmbc == "1")
    	$where = "WHERE free = '1' and clean = '1' ORDER BY `extension` ASC";

    $arrRoom=$pRoom->getCheckIn('rooms',$where);

    foreach($arrRoom as $k => $value)
    	$arrOptions[$value['id']] = $value['room_name'];

    if (!isset($value['room_name']))
    	$arrOptions = array( '1' => $arrLang['No Room!'] );

    $arrFields = array(
            "room"   => array(      "LABEL"                  => $arrLang["Room"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptions,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "date"   => array(      "LABEL"                  => $arrLang["Date"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%Y-%m-%d %H:%M:%S","TIMEFORMAT" => "24"),
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "date_co" => array(      "LABEL"                  => $arrLang["Date Checkout"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%Y-%m-%d %H:%M:%S","TIMEFORMAT" => "24"),
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "num_guest"=> array(     "LABEL"                  => $arrLang["Number of personne"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "booking"   => array(    "LABEL"         => $arrLang["Booking"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "first_name"   => array(      "LABEL"                  => $arrLang["First Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "last_name"   => array(      "LABEL"                  => $arrLang["Last Name"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "address"   => array(      "LABEL"                  => $arrLang["Address"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXTAREA",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "COLS"                   => "50",
                                            "ROWS"                   => "4",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "cp"   => array(      "LABEL"                  => $arrLang["CP"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "city"   => array(      "LABEL"                  => $arrLang["City"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "phone"   => array(      "LABEL"                  => $arrLang["Phone"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "mobile"   => array(      "LABEL"                  => $arrLang["Mobile"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "mail"   => array(      "LABEL"                  => $arrLang["Mail"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "fax"   => array(      "LABEL"                  => $arrLang["Fax"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            );
    return $arrFields;
}

function getAction()
{
    if(getParameter("save_new")) //Get parameter by POST (submit)
        return "save_new";
    else if(getParameter("save_edit"))
        return "save_edit";
    else if(getParameter("delete")) 
        return "delete";
    else if(getParameter("new_open")) 
        return "view_form";
    else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
        return "view_form";
    else if(getParameter("action")=="view_edit")
        return "view_form";
    else
        return "report"; //cancel
}
?>