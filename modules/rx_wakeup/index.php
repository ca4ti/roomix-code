<?php
 /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.4.0-1                                               |
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
  $Id: index.php,v 1.1 2013-05-07 01:05:40 Franck Danard franck.danard@roomx.fr Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function count_day($start,$end)
{
	$date_start = strtotime($start);
	$date_end   = strtotime($end);

	$timeDiff   = abs($date_start - $date_end);
	$numberDays = $timeDiff / 86400;  // 86400 seconds in one day
	$Days       = intval($numberDays);

	return $numberDays;
}

function moving_wakeup($Temp_File)
{
    $errMsg = '';
    $cmd = "/usr/bin/elastix-helper moving_wakeup ".$Temp_File." 2>&1";
    $output = $ret = NULL;
        exec($cmd, $output, $ret);
		if ($ret != 0) {
			$errMsg = implode('', $output);
            return FALSE;
        }
    return TRUE;
} 

function changing_date($originalDate)
{
	$newDate = date("Y-m-d H:i:s", strtotime($originalDate));
	return $newDate;
}

function arrDate($first, $last, $format) 
{ 
	$dates 		= array();
    $current 	= strtotime($first);
    $last 		= strtotime($last);

    while( $current <= $last ) 
	{ 
    	$dates[] = date($format, $current);
        $current = strtotime('+1 day', $current);
    }
    return $dates;
}

function touch_wakeup($at, $Temp_File )
    {
        $errMsg = '';
        $cmd = "/usr/bin/elastix-helper touch_wakeup ".$at." ".$Temp_File." 2>&1";
        $output = $ret = NULL;
        exec($cmd, $output, $ret);
        if ($ret != 0) {
            $errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    } 


function write_wakeup($extension, $date_time)
{
	// Looking for the language used by the room
	//-------------------------------------------------------
    $cmd="/usr/sbin/asterisk -x 'sip show peer ${extension}' | grep Language";
    exec($cmd,$lang);

    // Formating Date_Time 
    //-----------------------------
	

    // Preparing the content of wakeup file
    //------------------------------------------------
    $autodialCmd = 	"Channel: SIP/$extension\n".
			"CallerID: 'WakeUp' <$extension>\n".
			"MaxRetries: 3\n".
			"RetryTime: 60\n".
			"WaitTime: 30\n".
			"Context: from-internal\n".
			"Application: Playback\n".
			"Data: roomx/wakeup\n".
			"Extension: $extension\n".
			"Priority: 1\n".
			"AlwaysDelete: Yes\n".
			"Archive: Yes";

    // Create a wakeup file for a room
    //-------------------------------------------
    $filter = array(" ", ":");
    $file_name="/tmp/roomx_wakeup_".$extension."_".str_replace($filter,"_",$date_time).".call";
    $file = fopen($file_name,"a");
    fwrite($file,$autodialCmd);
    fclose($file);

    // Change the real time of wakeup file
    //-----------------------------
    $From = "'".$date_time."'";
    touch_wakeup($From, $file_name);

    // Move the temp file to this folder: /var/spool/asterisk/outgoing
    //-----------------------------
    moving_wakeup($file_name);

}

function delete_wakeup($extension, $date)
{
	// Delete wakeup files in the folder: /var/spool/asterisk/outgoing
	//----------------------------------------------------------------------------------
    $errMsg = '';
    $cmd = "/usr/bin/elastix-helper rm_wakeup {$extension} {$date} 2>&1";
    $output = $ret = NULL;
        exec($cmd, $output, $ret);
		if ($ret != 0) {
			$errMsg = implode('', $output);
            return FALSE;
        }
    return TRUE;	
}

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantorx_wakeup.class.php";

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
    
    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = saveNewrx_wakeup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case "delete":
            $content = saveDelrx_wakeup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default: // view_form
            $content = viewFormrx_wakeup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function viewFormrx_wakeup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $prx_wakeup = new paloSantorx_wakeup($pDB);
    $arrFormrx_wakeup = createFieldForm($pDB);
    $oForm = new paloForm($smarty,$arrFormrx_wakeup);

    //begin, Form data persistence to errors and other events.
    $_DATA  = $_POST;
    $action = getParameter("action");
    $id     = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $datarx_wakeup = $prx_wakeup->getrx_wakeupById($id);
        if(is_array($datarx_wakeup) & count($datarx_wakeup)>0)
            $_DATA = $datarx_wakeup;
        else{
            $smarty->assign("mb_title", _tr("Error get Data"));
            $smarty->assign("mb_message", $prx_wakeup->errMsg);
        }
    }

    $smarty->assign("SAVE", _tr("Save"));
	$smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "images/list.png");
    $smarty->assign("wuPic", "<img src='modules/$module_name/images/wakeup.png' width='130' height='190'>");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("rx_wakeup"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewrx_wakeup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $_DATA  		= $_POST;
    $prx_wakeup 	= new paloSantorx_wakeup($pDB);
    $Ext_Id 		= $prx_wakeup->getrx_wakeupById($_DATA['room']);
    $Group			= array(0 =>$Ext_Id);
    if($_DATA['spread'] == "on")
    	$Group	  	= $prx_wakeup->get_rooms("WHERE groupe = '{$Ext_Id['groupe']}' and free = 0;");

    $arrFormrx_wakeup = createFieldForm($pDB);
    $oForm = new paloForm($smarty,$arrFormrx_wakeup);

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        $content = viewFormrx_wakeup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    else{
        //NO ERROR, HERE IMPLEMENTATION OF SAVE

    $smarty->assign("SAVE", _tr("Save"));
	$smarty->assign("DELETE", _tr("Delete"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("icon", "images/list.png");
    $smarty->assign("wuPic", "<img src='modules/$module_name/images/wakeup.png' width='50%' height='50%'>");

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("rx_wakeup"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    $date_r=arrDate($_DATA['from'],$_DATA['to'],"j M Y H:i");

    for ($grp = 0; $grp < count($Group); $grp++)
    {
       $Extension = $Group[$grp]['extension'];

    	if(count($date_r) != 0){
    		for ($i = 0; $i < count($date_r); $i++) 
    		{
    			write_wakeup($Extension, $date_r[$i]);
    		}
    	}
    	else
    		{
    			write_wakeup($Extension, $_DATA['from']);
    		}
    	}
    }
    return $content;
}

function saveDelrx_wakeup($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $_DATA  		= $_POST;
    $prx_wakeup 	= new paloSantorx_wakeup($pDB);
    $Ext_Id 		= $prx_wakeup->getrx_wakeupById($_DATA['room']);
    $Group			= array(0 =>$Ext_Id);
    if($_DATA['spread'] == "on")
    	$Group	  	= $prx_wakeup->get_rooms("WHERE groupe = '{$Ext_Id['groupe']}' and free = 0;");

    $arrFormrx_wakeup = createFieldForm($pDB);
    $oForm = new paloForm($smarty,$arrFormrx_wakeup);

    if(!$oForm->validateForm($_POST)){
        // Validation basic, not empty and VALIDATION_TYPE 
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores = $oForm->arrErroresValidacion;
        $strErrorMsg = "<b>"._tr("The following fields contain errors").":</b><br/>";
        if(is_array($arrErrores) && count($arrErrores) > 0){
            foreach($arrErrores as $k=>$v)
                $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);
        $content = viewFormrx_wakeup($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    else
	{
		//NO ERROR, HERE IMPLEMENTATION OF SAVE

		$smarty->assign("SAVE", _tr("Save"));
		$smarty->assign("DELETE", _tr("Delete"));
		$smarty->assign("EDIT", _tr("Edit"));
		$smarty->assign("CANCEL", _tr("Cancel"));
		$smarty->assign("REQUIRED_FIELD", _tr("Required field"));
		$smarty->assign("icon", "images/list.png");
		$smarty->assign("wuPic", "<img src='modules/$module_name/images/wakeup.png' width='50%' height='50%'>");

		$htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("rx_wakeup"), $_DATA);
		$content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

		$date_r=arrDate($_DATA['from'],$_DATA['to'],"j M Y H:i");

	    for ($grp = 0; $grp < count($Group); $grp++)
		{
		$Extension = $Group[$grp]['extension'];

    	if(count($date_r) != 0){
    		for ($i = 0; $i < count($date_r); $i++) 
    		{
    			delete_wakeup($Extension, $date_r[$i]);
    		}
    	}
    	else
    		{
    			delete_wakeup($Extension, $_DATA['from']);
    		}
    	}
	}
    return $content;
}

function createFieldForm(&$pDB)
{
    $prx_wakeup = new paloSantorx_wakeup($pDB);
    $Room_List  = $prx_wakeup->get_rooms("WHERE free = 0 ORDER BY room_name;");
    $arrOptions = Null;

    foreach($Room_List as $k => $value)
    	$arrOptions[$value['id']] = $value['room_name'];
    
    if(count($arrOptions)==0)
	$arrOptions=array('1' => _tr("None"));

    $arrFields = array(
            "room"   => array(              "LABEL"                  => _tr("Room"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptions,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "from"   => array(      	  "LABEL"                  => _tr("From"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M","TIMEFORMAT" => "24"),
                                            "VALIDATION_TYPE"        => "",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_TYPE"        => "",
                                            "VALIDATION_EXTRA_PARAM" => ""
						  ),                                            
            "to"   => array(     		  "LABEL"                  => _tr("To"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%d %b %Y %H:%M","TIMEFORMAT" => "24"),
                                            "VALIDATION_TYPE"        => "",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "spread"   => array(      	  "LABEL"                  => _tr("Spread"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
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