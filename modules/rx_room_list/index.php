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
  $Id: index.php,v 1.1 2010-04-18 07:04:20 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoJSON.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoRoomList.class.php";

    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    $image_dir="modules/$module_name/images/";
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
    $pDB     = new paloDB($arrConf['dsn_conn_database']);
    $pDB_Trk = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/trunk.db");
    $pDB_CDR = new paloDB("mysql://root:".obtenerClaveConocidaMySQL('root')."@localhost/asteriskcdrdb");
	$pDB_Ast = new paloDB("mysql://root:".obtenerClaveConocidaMySQL('root')."@localhost/asterisk");

    //actions
    $action   	= getAction();
    $Exten		= getParameter("Who");
    $Wakeup_File	= getParameter("Wakeup_File");
    $DND_Status 	= getParameter("DND_St");
    $CLEAN_Status 	= getParameter("CLEAN_St");
    $Ack_Wakeup 	= getParameter("Ack_Wakeup");
    $from			= getParameter("from");
	$to				= getParameter("to");
	$register_id	= getParameter("register_id");
    $content = "";

    switch($action){
		case "dnd_status":
            $content = SendDNDStatus($Exten, $DND_Status);
			break;	
		case "clean_status":
            $content = SendCleanStatus($pDB, $Exten, $CLEAN_Status);
			break; 
		case "Ack_Wakeup":
            $content = Send_Ack_Wakeup($pDB, $Wakeup_File);
            break;
		case "transfer":
		    Launching_Transfer($register_id,$from, $to, $pDB,$pDB_Ast);
            $content = reportRoomList($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Trk, $pDB_CDR, $arrConf, $arrLang);
            break;
		default:
            $content = reportRoomList($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Trk, $pDB_CDR, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function SendDNDStatus($Exten, $DND_Status)
{
    $jsonObject      = new PaloSantoJSON();
    $msgResponse     = "ERROR";

    $cmd1 = "asterisk -rx 'database put DND ".$Exten." YES'";
    $cmd2 = "asterisk -rx 'devstate change Custom:DND".$Exten." INUSE'";
    if ( $DND_Status == "NO"){
    	$cmd1 = "asterisk -rx 'database del DND ".$Exten."'";
	$cmd2 = "asterisk -rx 'devstate change Custom:DND".$Exten." NOT_INUSE'";
    }
    if(isset($Exten) && isset($DND_Status)){
    	exec($cmd1);
    	exec($cmd2);
	$msgResponse	= "OK";
    }

    $jsonObject->set_message($msgResponse);
    return $jsonObject->createJSON();
}

function remoteActionControl($url)
{
	if($url != ""){
		$rac 		= fopen($url, "r");
		$getRac 	= fread($rac,1024);
		fclose($rac);
	}
}

function vm_clean($Ext)
{
    $errMsg = '';
    $cmd = "/usr/bin/elastix-helper vm_clean ".$Ext." 2>&1";
    $output = $ret = NULL;
    exec($cmd, $output, $ret);
    if ($ret != 0) {
        $errMsg = implode('', $output);
        return FALSE;
    }
    return TRUE;
} 

function rm_wakeup($extension)
{
	$errMsg = '';
       $cmd = "/usr/bin/elastix-helper rm_wakeup {$extension} 2>&1";
       $output = $ret = NULL;
       exec($cmd, $output, $ret);
       if ($ret != 0) {
            $errMsg = implode('', $output);
            return FALSE;
       }
       return TRUE;
} 


function Launching_Transfer($register_id,$from, $to,$pDB, $pDB_Ast)
{
    $pRoom 		= new paloSantoRoomList($pDB);
	$pRoom_Ast  = new paloSantoRoomList($pDB_Ast);
    $arrConfig 	= $pRoom->getConfig();
    $arrLock 	= $arrConfig;
	
	$from_r		= $pRoom->getRoomList(1,0,'extension', $from);
	$to_r		= $pRoom->getRoomList(1,0,'room_name', $to);
	$Account_C	= $pRoom_Ast->getAccountCode("WHERE id = {$from} and keyword = 'accountcode'");
	$from_room	=$from_r[0];
	$to_room	=$to_r[0];

	// Updatng the current room (from) : The room was busy and now it's free and not clean.
	// In same time, deleting the guest name and group.
    //--------------------------------------------------------------------------------------------------------------
    $value['free'] 		= "'1'";
    $value['clean'] 	= "'0'";
	$value['groupe'] 	= null;
	$value['guest_name']= null;
    $value['mini_bar'] 	= null;
    $where 				= "extension = '{$from_room['extension']}'";
    $arrUpdateRoom 		= $pRoom->updateQuery('rooms', $value, $where);
	
	// Update room destination (to) : The room was busy and now it's busy and clean.
	// Next, updating the guest name and group.
    //---------------------------------------------------------------------------------------------------
    $value['free'] 		= "'0'";
    $value['clean'] 	= "'1'";
	$value['groupe'] 	= "'{$from_room['groupe']}'";
	$value['guest_name']= "'{$from_room['guest_name']}'";
    $value['mini_bar'] 	= null;
    $where 				= "extension = '{$to_room['extension']}'";
    $arrUpdateRoom 		= $pRoom->updateQuery('rooms', $value, $where);
	
    // Lock the extension after transfer
    //---------------------------------------------
    $cmd 			= "/usr/sbin/asterisk -rx 'database put LOCKED {$from_room['extension']} 0'";
	 if ( $arrLock['locked'] == "1")
        	$cmd 	= "/usr/sbin/asterisk -rx 'database put LOCKED {$from_room['extension']} 1'";
	exec($cmd);

	// Unlock the extension  destination after transfer
    //--------------------------------------------------------------
    $cmd 			= "/usr/sbin/asterisk -rx 'database put LOCKED {$to_room['extension']} 0'";
	exec($cmd);

	// Delete voicemail of the previous room
	//-------------------------------------------------
	vm_clean($from_room['extension']);

    // Remove all Wakeup files of the previous room.
    //-------------------------------------------------------------
	rm_wakeup($from_room['extension']);
	
	// Disable DND of the previous room.
	//----------------------------------------------
    $cmd 		 		= "/usr/sbin/asterisk -rx 'database del DND {$from_room['extension']}'";
	exec($cmd);
	
	// Replace the guest name.by the room name 
	//------------------------------------------------------
	$cmd 			= "asterisk -rx 'database put AMPUSER/{$from_room['extension']} cidname \"".$from_room['room_name']."\"'";
	exec($cmd);
	$cmd 			= "asterisk -rx 'database put AMPUSER/{$to_room['extension']} cidname \"".$from_room['guest_name']."\"'";
	exec($cmd);

	// Sending url to R.A.C.
	//---------------------------
	$rac_url	= $from_room['RACO'];
	remoteActionControl($rac_url);
	$rac_url	= $to_room['RACI'];
	remoteActionControl($rac_url);

	// Delete account code extension into Freepbx data
    //----------------------------------------------------------------
    $value_rl['value']  = "'true'";
    $where              = "variable = 'need_reload';";
    $arrReload          = $pRoom_Ast->updateQuery('admin',$value_rl, $where);
	
	// From
    $value_ac['data']   = "''";
    $where              = "id = '{$from_room["extension"]}' and keyword = 'accountcode';";
    $arrAccount         = $pRoom_Ast->updateQuery('sip',$value_ac, $where);
	
	// To
	$value_ac['data']   = "'{$Account_C["data"]}'";
    $where              = "id = '{$to_room["extension"]}' and keyword = 'accountcode';";
    $arrAccount         = $pRoom_Ast->updateQuery('sip',$value_ac, $where);

    $cmd="/var/lib/asterisk/bin/module_admin reload";
    exec($cmd);
	
    //  Updating register table into the database. 
    //------------------------------------------------------
	$value_reg['room_id']	= "'{$to_room["id"]}'";
	$where              	= "id='{$register_id}';";
	$arrRegister         	= $pRoom->updateQuery('register',$value_reg, $where);
}

function Delete_Flag()
    {
        $errMsg = '';
        $cmd = "/usr/bin/elastix-helper Delete_Flag 2>&1";
        $output = $ret = NULL;
        exec($cmd, $output, $ret);
        if ($ret != 0) {
            $errMsg = implode('', $output);
            return FALSE;
        }
        return TRUE;
    } 

function Send_Ack_Wakeup($pDB, $file){
    $jsonObject      = new PaloSantoJSON();
    $msgResponse     = "ERROR";

    $text		= fopen($file,'r');
    $content		= file_get_contents($file);
    $Words		= array("Expired","Failed");
    $contentMod	= str_replace($Words, "Acknowledged", $content);

    fclose($text);

    $text_mod	= fopen($file,'w+');
    fwrite($text_mod,$contentMod);
    fclose($text_mod); 
    $msgResponse	= "OK";

    // Deleting the alert file.
    //-------------------------

    Delete_Flag();

    $jsonObject->set_message($msgResponse);
    return $jsonObject->createJSON();
}

function wakeup($WakeUp_Ext, $module_name)
{
    //Get wakeup files
    $Current_wakeup_path = "/var/spool/asterisk/outgoing/";
    $Done_wakeup_path    = "/var/spool/asterisk/outgoing_done/";
    $Current_wakeup	    = scandir($Current_wakeup_path);
    $Done_wakeup         = scandir($Done_wakeup_path);

    $File_filter 	= "/\b".$WakeUp_Ext."\b/i";
    $Wakup_status 	= "";

    //Find a current wakeup.
    foreach($Current_wakeup as $key => $wakeup_file){
          if ($key > 1 && preg_match($File_filter,str_replace("_"," ",$wakeup_file)))
              	$Wakup_status = "<img src='modules/".$module_name."/images/wakeup_on.png'>";
    }

    //Find an expired or failed wakeup
    foreach($Done_wakeup as $key => $wakeup_file){
          if ($key > 1){	// Avoid to display the top of directory /. and /..
              $Content_file = file_get_contents($Done_wakeup_path.$wakeup_file);

              if (preg_match($File_filter, $Content_file)){
			if (preg_match("/\bExpired\b/i", $Content_file) OR preg_match("/\bFailed\b/i", $Content_file)){
              		//$Wakup_status = "<img src='modules/".$module_name."/images/wakeup_down.png'>";
				$Wakup_status = "<div id='Ack".$WakeUp_Ext."'>
						   <img src='modules/".$module_name."/images/wakeup_down.png' onclick='Ack_wakeup(\"".$WakeUp_Ext."\",\"".$Done_wakeup_path.$wakeup_file."\")'>
						   </div>";
			}
		}
          }
    }
    return $Wakup_status;
}

function Wakeup_request($WakeUp_Ext, $arrLang)
{
    //Get wakeup files
    $Current_wakeup_path = "/var/spool/asterisk/outgoing/";
    $Done_wakeup_path    = "/var/spool/asterisk/outgoing_done/";
    $Current_wakeup	    = scandir($Current_wakeup_path);
    $Done_wakeup         = scandir($Done_wakeup_path);

    $File_filter 	= "/\b".$WakeUp_Ext."\b/i";
    $Wakup_status 	= "";

    //Find any current wakeup.
    $Current_Wakup_status="";
    foreach($Current_wakeup as $key => $wakeup_file){
          if ($key > 1 && preg_match($File_filter,str_replace("_"," ",$wakeup_file))){
              	list($balise, $wak, $ext_num, $J, $M, $A, $H, $mn) = explode("_", $wakeup_file);
			$Current_Wakup_status = $Current_Wakup_status.$arrLang["Current wakeup"]." : ".$J." ".$M." ".$A." ".$H."h".str_replace(".call","",$mn)."mn<br>";
	   }
    }

    //Find any expired or failed wakeup
    $Done_Wakeup_status="";
    foreach($Done_wakeup as $key => $wakeup_file){
          if ($key > 1){	// Avoid to display the top of directory /. and /..
              $Content_file = file_get_contents($Done_wakeup_path.$wakeup_file);

              if (preg_match($File_filter, $Content_file)){
			if (preg_match("/\bExpired\b/i", $Content_file) OR preg_match("/\bFailed\b/i", $Content_file)) {
              		list($balise, $wak, $ext_num, $J, $M, $A, $H, $mn) = explode("_", $wakeup_file);
				$Done_Wakeup_status = $Done_Wakeup_status.$arrLang["Expired or Failed wakeup"]." : ".$J." ".$M." ".$A." ".$H."h".str_replace(".call","",$mn)."mn<br>";
			}
		}
          }
    }
    $Wakup_status = $Current_Wakup_status.$Done_Wakeup_status;
    return $Wakup_status;
}

function SendCleanStatus($pDB, $extension, $Clean_Status)
{
    $jsonObject         = new PaloSantoJSON();
    $pRoomList 	    	= new paloSantoRoomList($pDB);
    $msgResponse	    = "ERROR";
    $arrValores['clean']= "'0'";
    if($Clean_Status == "YES")
    	$arrValores['clean'] = "'1'";

    $Ext_Clean	    	= $pRoomList->updateQuery("rooms", $arrValores, "extension = '".$extension."'");

    if($Ext_Clean == true)
	$msgResponse	    = "OK";
    $jsonObject->set_message($msgResponse);
    return $jsonObject->createJSON();
}

function findCalls($extension, $date_ci, $guest_id, $pDB, $pDB_Trk, $pDB_CDR, $arrConf)
{
    $pRoomList 	= new paloSantoRoomList($pDB);
    $pTrunk  	= new paloSantoRoomList($pDB_Trk);
    $pRoomCDR 	= new paloSantoRoomList($pDB_CDR);
    $arrTrk  	= $pTrunk->loadTrunk();

    // Find any room calls
    //--------------------------
        
    $dst_info 	= $dahdi_t = $misdn_t = $capi_t = "";
 	foreach($arrTrk as $key_trk => $value_trk){
		$trunk 	= $value_trk['trunk'];
		if(substr($trunk,0,strlen('DAHDI')) == 'DAHDI')
			$dahdi_t = "dstchannel LIKE '%DAHDI%' OR ";
		if(substr($trunk,0,strlen('mISDN')) == 'mISDN')
			$misdn_t = "dstchannel LIKE '%mISDN%' OR ";
		if(substr($trunk,0,strlen('CAPI')) == 'CAPI')
			$capi_t  = "dstchannel LIKE '%CAPI%' OR ";
		$condition = "lastdata LIKE '%".$trunk."%'";
		if( $key_trk < (count($arrTrk)-1)){
			$condition = "lastdata LIKE '%".$trunk."%' OR ";
		}
		$dst_info = $dst_info.$condition;
	}
	$date_co	= date('Y-m-d H:i:s');
    // and calldate < '".$date_co."' 
	$where		="WHERE billsec > '0' and calldate > '".$date_ci."'".
 			    " and disposition = 'ANSWERED' and accountcode ='".$guest_id."'".
			    " and ( ".$dahdi_t.$misdn_t.$capi_t.$dst_info.");";
    $arrCDR 	= $pRoomCDR->getCDR($where);

    return count($arrCDR);
}


function reportRoomList($smarty, $module_name, $local_templates_dir, &$pDB, $pDB_Trk, $pDB_CDR, $arrConf, $arrLang)
{
    $pRoomList = new paloSantoRoomList($pDB);
    $filter_field = getParameter("filter_field");
    $filter_value = getParameter("filter_value");

    $action = getParameter("nav");
    $start  = getParameter("start");
    $as_csv = getParameter("exportcsv");
    $Wakeup_File = getParameter("wakeup_file");

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalRoomList  = $pRoomList->getNumRoomList($filter_field, $filter_value);
	$room_free		= $pRoomList->getRoomFree();

    $limit  = 20;
    $total  = $totalRoomList;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($total);
    $oGrid->enableExport();   // enable csv export.
    $oGrid->pagingShow(true); // show paging section.

    $oGrid->calculatePagination($action,$start);
    $offset = $oGrid->getOffsetValue();
    $end    = $oGrid->getEnd();
    $url    = "?menu=$module_name&filter_field=$filter_field&filter_value=$filter_value";

    $arrData = null;

    $arrResult =$pRoomList->getRoomList($limit, $offset, $filter_field, $filter_value);
    $enable  = "/images/1.png";
    $disable = "/images/0.png";

    $ok  = array("0" => $disable, "1" => $enable);

    // Check if delete wakeup-file is sent
    if ($Wakeup_File != "")
	    unlink($Wakeup_File);

    if(is_array($arrResult) && $total>0){
        foreach($arrResult as $key => $value){ 

	    // Check MiniBar 
	    //--------------
    	$minibar = " ";	
        $warning = " ";			
    	if ( strlen($value['mini_bar']) != 0)
	    	$minibar= "<img src='modules/".$module_name."/images/m.png'>";

	    // The phone is reachable ?
	    //-------------------------
        $cmd	= "asterisk -rx 'sip show peer ".$value['extension']."' | grep Status | grep OK";
	    if (!exec($cmd))
	    	$warning = "<img src='modules/".$module_name."/images/warning.png' border='0'>";

	    //Wakeup Info
	    $Wakup_Info = Wakeup_request($value['extension'], $arrLang);
           
    	// DND is YES ?
    	//-------------
	    $details= $value['room_name'];
	    $ext	= $value['extension'];
    	$cmd 	= "asterisk -rx 'database show DND ".$value['extension']."' | grep YES ";

    	$dnd 	= "<div id='dnd".$value['extension']."'>
		     	   <img src='modules/".$module_name."/images/dnd.png' onclick='dnd_status(\"".$value['extension']."\",\"NO\")'>
		     	   </div>";
    	if (!exec($cmd))
    		$dnd = "<div id='dnd".$value['extension']."'>
					<img src='modules/".$module_name."/images/d.png' onclick='dnd_status(\"".$value['extension']."\",\"YES\")'>
					</div>";

        $clean 	= "<div id='clean".$value['extension']."'>
			 	  <img src='modules/".$module_name.$ok[$value['clean']]."' onclick='clean_status(\"".$value['extension']."\",\"NO\")'>
		          </div>";
	    if ($value['clean'] == 0){
           	$clean 	= "<div id='clean".$value['extension']."'>
					   <img src='modules/".$module_name.$ok[$value['clean']]."' onclick='clean_status(\"".$value['extension']."\",\"YES\")'>
		               </div>";
	    }
				
    	$id_room	 	= $pRoomList->getRoomListByName($details);
    	$Register_Det	= $pRoomList->getRegisterByRoomId($id_room['id']);
        $Add_Guest		= $Register_Det['num_guest'];
	    $nb_calls		= findCalls($id_room['extension'], $Register_Det['date_ci'], $Register_Det['guest_id'],$pDB, $pDB_Trk, $pDB_CDR, $arrConf);
		
		$Transfer_options ="";
		foreach($room_free as $id => $name) {
		        $id_room = $name['id'];
				$room_name = $name['room_name'];
				$Transfer_options .= "<option>{$room_name}</option>";
			}
	    $ExtenTrans = $value['extension'];
		
		$Content_Transfer = "<p><b>{$value['guest_name']}</b> {$arrLang["is currently here"]} : {$value['room_name']}.</p><p> {$arrLang["Please, select the room destination for this guest"]} : <select name='select' id='select{$ExtenTrans}'>{$Transfer_options}</select></p><br>";

        $Transfer = "<img src='modules/".$module_name."/images/transfer.png' onclick='Transfer(\"{$Register_Det["id"]}\",\"{$ExtenTrans}\",\"{$arrLang["Transfer"]}\",\"{$arrLang["Cancel"]}\",\"{$arrLang["Valid"]}\")'><div style='visibility: hidden; display: none;' id='DialTrans".$ExtenTrans."'>\n".$Content_Transfer."</div>\n";
		
		
		// Detail info dialog
		//------------------------
	    $Call_info	= "";
	    if($nb_calls > 0)
	    	$Call_info		= $arrLang["The guest has used the phone with "].$nb_calls.$arrLang[" calls"]."<br>";
	    $msg_add_guest		= $arrLang["Additional guest"]."<br>";
	    if($Add_Guest == 0)
			$msg_add_guest= "";
		$msgResponse 		= $arrLang["Guest present from"].$Register_Det['date_ci'].$arrLang[" To "].$Register_Det['date_co']."<br>".$msg_add_guest.$Call_info.$Wakup_Info;
	    if(!isset($Register_Det['date_ci']))
           	$msgResponse 	= $arrLang["None"];
	    $arrTmp[0] 	= "<img src='modules/".$module_name."/images/free.png'>";;
	    $arrTmp[1] 	= "<b>".$arrLang['free']."</b>";
	    $arrTmp[2] 	= $value['room_name'];	
	    $arrTmp[3] 	= $value['extension']." ".$warning;
	    $arrTmp[4] 	= $value['model'];
	    $arrTmp[5] 	= $value['groupe'];
		$arrTmp[6] 	= " ";
	    $arrTmp[7] 	= "<img src='modules/".$module_name.$ok[$value['free']]."'>";
	    $arrTmp[8] 	= $clean;
	    $arrTmp[9] 	= $minibar;
	    $arrTmp[10] = " ";
	    $arrTmp[11] = wakeup($value['extension'],$module_name); 

 	    if ($value['guest_name'] != ""){
	    	$arrTmp[0] 	= "<img src='modules/".$module_name."/images/guest_info.png' border='0' onclick='FindDetails(\"$msgResponse\")'>\n";
	    	$arrTmp[1] 	= $value['guest_name'];
			$arrTmp[6] 	= $Transfer;
	        $arrTmp[10] = $dnd;
	        }
        $arrData[] 	= $arrTmp;
        }
		
    }


    $arrGrid = array("title"       => $arrLang["Room List"],
                        "icon"     => "/modules/$module_name/images/icone.png",
                        "width"    => "99%",
                        "start"    => ($total==0) ? 0 : $offset + 1,
                        "end"      => $end,
                        "total"    => $total,
                        "url"      => $url,
                        "columns"  => array(
			0 => array("name"      => $arrLang["Details"],
                                   "property1" => ""),
			1 => array("name"      => $arrLang["Name"],
                                   "property1" => ""),
			2 => array("name"      => $arrLang["Room Name"],
                                   "property1" => ""),
			3 => array("name"      => $arrLang["Extension"],
                                   "property1" => ""),
			4 => array("name"      => $arrLang["Model "],
                                   "property1" => ""),
			5 => array("name"      => $arrLang["Groupe"],
                                   "property1" => ""),
			6 => array("name"      => $arrLang["Transfer"],
                                   "property1" => ""),
			7 => array("name"      => $arrLang["Free"],
                                   "property1" => ""),
			8 => array("name"      => $arrLang["Clean"],
                                   "property1" => ""),
			9 => array("name"      => $arrLang["Mini bar"],
                                   "property1" => ""),
			10 => array("name"      => $arrLang["DND"],
                                   "property1" => ""),
			11 => array("name"      => $arrLang["wakeup"],
                                   "property1" => ""),
                                        )
                    );


    //begin section filter
    $arrFormFilterRoomList = createFieldFilter($arrLang);
    $oFilterForm = new paloForm($smarty, $arrFormFilterRoomList);

    $smarty->assign("SHOW", $arrLang["Show"]);

    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);

    //end section filter

    if($as_csv == 'yes'){
        $name_csv = "RoomList_".date("d-M-Y").".csv";
        header("Cache-Control: private");
        header("Pragma: cache");
        header("Content-Type: application/octec-stream");
        header("Content-disposition: inline; filename={$name_csv}");
        header("Content-Type: application/force-download");
        $content = $oGrid->fetchGridCSV($arrGrid, $arrData);
    }
    else{
        $oGrid->showFilter(trim($htmlFilter));
        $content = "<form  method='POST' style='margin-bottom:0;' action=\"$url\">".$oGrid->fetchGrid($arrGrid, $arrData,$arrLang)."</form>";
    }

    //end grid parameters

    return $content;
}


function createFieldFilter($arrLang){
    $arrFilter = array(
	    "guest_name"=> $arrLang["Name"],
	    "room_name" => $arrLang["Room Name"],
	    "extension" => $arrLang["Extension"],
	    "model" 	  => $arrLang["Model "],
	    "groupe"    => $arrLang["Groupe"],
	    "free" 	  => $arrLang["Free"],
	    "clean" 	  => $arrLang["Clean"],
	    //"mini_bar"  => $arrLang["Mini bar"],
	    //"dnd"     => $arrLang["DND"],
                    );

    $arrFormElements = array(
            "filter_field" => array("LABEL"                  => $arrLang["Search"],
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
    else if(getParameter("action")=="DND")
        return "dnd_status";
    else if(getParameter("action")=="CLEAN")
        return "clean_status";
    else if(getParameter("action")=="Ack_Wakeup")
        return "Ack_Wakeup";
	else if(getParameter("action")=="transfer")
        return "transfer";
    else
        return "report"; //cancel
}
?>