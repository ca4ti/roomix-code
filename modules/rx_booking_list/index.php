<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.4-21                                               |
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
  $Id: index.php,v 1.1 2011-05-26 06:05:07 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoBookingList.class.php";
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

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB     = new paloDB($arrConf['dsn_conn_database']);
    $pDB_Ast = new paloDB("mysql://root:".obtenerClaveConocidaMySQL('root')."@localhost/asterisk");

    //actions
    $action  = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = ActionBookingList($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Ast, $arrConf);
            break;
        default:
            $content = reportBookingList($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function reportBookingList($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pBookingList = new paloSantoBookingList($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("Booking List"));
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export(_tr("Booking List"));

    $url = array(
        "menu"         =>  $module_name,
        "filter_field" =>  $filter_field,
        "filter_value" =>  $filter_value);
    $oGrid->setURL($url);

    $arrColumns = array(_tr("Checkin"),_tr("Canceled"),_tr("Rooms"),_tr("First Name"),_tr("Last Name"),_tr("Number Guest"),_tr("Date Checkin"),_tr("Date Checkout"),);
    $oGrid->setColumns($arrColumns);

    $total   = $pBookingList->getNumBookingList($filter_field, $filter_value);
    $arrData = null;
    if($oGrid->isExportAction()){
        $limit  = $total; // max number of rows.
        $offset = 0;      // since the start.
    }
    else{
        $limit  = 20;
        $oGrid->setLimit($limit);
        $oGrid->setTotal($total);
        $offset = $oGrid->calculateOffset();
    }

    $arrResult = $pBookingList->getBookingList($limit, $offset, $filter_field, $filter_value);
    
    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
	    $arrTmp[0] = "<input type='checkbox' name='checkin[".$key."]' value='".$value['id']."'>";
	    $arrTmp[1] = "<input type='checkbox' name='canceled[".$key."]' value='".$value['id']."'>";
	    $arrTmp[2] = $value['room_name'];
	    $arrTmp[3] = $value['first_name'];
	    $arrTmp[4] = $value['last_name'];
	    $arrTmp[5] = $value['num_guest'];	
	    $arrTmp[6] = $value['date_ci'];
	    $arrTmp[7] = $value['date_co'];
           $arrData[] = $arrTmp;
        }
    }

    $oGrid->setData($arrData);

    //begin section filter
    $oFilterForm = new paloForm($smarty, createFieldFilter());
    $smarty->assign("SHOW", _tr("Show"));
    $htmlFilter  = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid();
    //end grid parameters

    return $content;
}

function ActionBookingList($smarty, $module_name, $local_templates_dir, &$pDB, &$pDB_Ast, $arrConf)
{
    $pBookingList     = new paloSantoBookingList($pDB);
    $pBookingList_Ast = new paloSantoBookingList($pDB_Ast); 
    
    $_DATA = $_POST;

    // There's some checkin to do?
    //-------------------------------
    if(array_key_exists('checkin',$_DATA))
    {
    	foreach($_DATA['checkin'] as $key => $value)
    	{
      		// Making CheckIn Room.
		//---------------------

		$arrBooking 			= $pBookingList->getCheckIn('booking',"WHERE id = $value");

              // Save all Datas into the table register. 
        	//---------------------------------------------
        	$value_register['room_id']  = "'".$arrBooking['0']['room_id']."'";
        	$value_register['guest_id'] = "'".$arrBooking['0']['guest_id']."'";
        	$value_register['date_ci']  = "'".$arrBooking['0']['date_ci']."'";
        	$value_register['date_co']  = "'".$arrBooking['0']['date_co']."'";
        	$value_register['num_guest']= "'".$arrBooking['0']['num_guest']."'";
        	$value_register['status']   = "'1'";
        	$arrRegister 		  	= $pBookingList->insertQuery('register',$value_register);

        	// Update the room status (Free -> Busy)
	 	// Put the guest name into the room.
        	//---------------------------------------------

		$guest_id			= $value_register['guest_id'];
		$arrGuest	 		= $pBookingList->getCheckIn("guest","WHERE id = $guest_id");
	 	$guest_name 			= str_replace("'","",$arrGuest['0']['first_name']." ".$arrGuest['0']['last_name']);
       	$value_rooms['free'] 	= '0'; 
        	$value_rooms['guest_name']  = "'".$guest_name."'";
        	$where 			= "id = ".$value_register['room_id'];
        	$arrRegister 			= $pBookingList->updateQuery('rooms',$value_rooms, $where);

	 	// Update status table.
	 	//---------------------
	 	$free				= $pBookingList->Free();				// Take all free rooms
	 	$busy				= $pBookingList->Busy();				// Take all busy rooms
	 	$booking			= $pBookingList->getBookingStatus();		// Take all booking of the day. 

        	$value_status['free']  	= strval($free);
        	$value_status['busy']   	= strval($busy);
        	$value_status['booking']    = strval($booking);
	  
	 	$arrStatus	 		= $pBookingList->UpdateStatus($value_status);	// At first, creating the day if not exist
	 	$arrStatus	 		= $pBookingList->UpdateStatus($value_status);	// Next, re-sending request to update free, busy, and booking

	 	// Take the rooms extension from id 
        	//---------------------------------------------
        	$where 			= "WHERE id = ".$value_register['room_id'];
        	$arrRooms 			= $pBookingList->getCheckIn('rooms',$where);
        	$Rooms 			= $arrRooms['0'];

        	// Modify the account code extension into Freepbx data
        	//---------------------------------------------
        	$value_rl['value']  		= "'true'";
        	$where              		= "variable = 'need_reload';";
        	$arrReload          		= $pBookingList_Ast->updateQuery('admin',$value_rl, $where);

        	$value_ac['data']   		= "'".$value_register['guest_id']."'";
        	$where              		= "id = '".$Rooms['extension']."' and keyword = 'accountcode';";
        	$arrAccount         		= $pBookingList_Ast->updateQuery('sip',$value_ac, $where);

        	$cmd				= "/var/lib/asterisk/bin/module_admin reload";
        	exec($cmd);

        	// Unlock the extension 
        	//---------------------------------------------
	 	$cmd 				= "/usr/sbin/asterisk -rx 'database put LOCKED ".$Rooms['extension']." 0'";
		exec($cmd);

        	// Call Between rooms enabled or not.
        	//---------------------------------------------
        	$arrConfig 			= $pBookingList->getCheckIn('config',"");
        	$arrAstDB 			= $arrConfig['0'];

        	$cmd				= "/usr/sbin/asterisk -rx 'database put CBR ".$Rooms['extension']." ".$arrAstDB['cbr']."'";
        	exec($cmd);

      		// Deleting booked Room.
		//----------------------
		$Result = $pBookingList->Delete($value);  
	}
    }
    
    // There's some booking canceled?
    //-------------------------------
    if(array_key_exists('canceled',$_DATA)){
    	foreach($_DATA['canceled'] as $key => $value)
    	{
      		// Deleting booked Room.
		//----------------------
		$Result = $pBookingList->Delete($value);         
    	}
    }

    $pBookingList = new paloSantoBookingList($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $oGrid->setTitle(_tr("Booking List"));
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->enableExport();   // enable export.
    $oGrid->setNameFile_Export(_tr("Booking List"));

    $url = array(
        "menu"         =>  $module_name,
        "filter_field" =>  $filter_field,
        "filter_value" =>  $filter_value);
    $oGrid->setURL($url);

    $arrColumns = array(_tr("Checkin"),_tr("Canceled"),_tr("Rooms"),_tr("First Name"),_tr("Last Name"),_tr("Number Guest"),_tr("Date Checkin"),_tr("Date Checkout"),);
    $oGrid->setColumns($arrColumns);

    $total   = $pBookingList->getNumBookingList($filter_field, $filter_value);
    $arrData = null;
    if($oGrid->isExportAction()){
        $limit  = $total; // max number of rows.
        $offset = 0;      // since the start.
    }
    else{
        $limit  = 20;
        $oGrid->setLimit($limit);
        $oGrid->setTotal($total);
        $offset = $oGrid->calculateOffset();
    }

    $arrResult = $pBookingList->getBookingList($limit, $offset, $filter_field, $filter_value);
    
    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 
	    $arrTmp[0] = "<input type='checkbox' name='checkin[".$key."]' value='".$value['id']."'>";
	    $arrTmp[1] = "<input type='checkbox' name='canceled[".$key."]' value='".$value['id']."'>";
	    $arrTmp[2] = $value['room_name'];
	    $arrTmp[3] = $value['first_name'];
	    $arrTmp[4] = $value['last_name'];
	    $arrTmp[5] = $value['num_guest'];	
	    $arrTmp[6] = $value['date_ci'];
	    $arrTmp[7] = $value['date_co'];
           $arrData[] = $arrTmp;
        }
    }
    $oGrid->setData($arrData);

    //begin section filter
    $oFilterForm = new paloForm($smarty, createFieldFilter());
    $smarty->assign("SHOW", _tr("Show"));
    $htmlFilter  = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
    //end section filter

    $oGrid->showFilter(trim($htmlFilter));
    $content = $oGrid->fetchGrid();
    //end grid parameters

    return $content;
}


function createFieldFilter(){
    $arrFilter = array(
	    "room_name"    => _tr("Rooms"),
	    "first_name"   => _tr("First Name"),
	    "last_name"    => _tr("Last Name"),
	    "num_guest"    => _tr("Number Guest"),
	    "date_ci"      => _tr("Date Checkin"),
	    "date_co"      => _tr("Date Checkout"),
                    );

    $arrFormElements = array(
            "filter_field" => array("LABEL"                  => _tr("Search"),
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "SELECT",
                                    "INPUT_EXTRA_PARAM"      => $arrFilter,
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
            "filter_value" => array("LABEL"                  => "",
                                    "REQUIRED"               => "no",
                                    "INPUT_TYPE"             => "TEXT",
                                    "INPUT_EXTRA_PARAM"      => "",
                                    "VALIDATION_TYPE"        => "text",
                                    "VALIDATION_EXTRA_PARAM" => ""),
                    );
    return $arrFormElements;
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