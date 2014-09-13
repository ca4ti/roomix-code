<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-22                                             |
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
  $Id: index.php,v 1.1 2010-05-08 11:05:33 Franck Danard franckd@agmp.org Exp $ */

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
include_once "libs/paloSantoCDR.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoCheckOut.class.php";
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
    $pDB_CDR = new paloDB("mysql://root:".obtenerClaveConocidaMySQL('root')."@localhost/asteriskcdrdb");
    $pDB_Set = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/settings.db");
    $pDB_Rat = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/rate.db");
    $pDB_Trk = new paloDB("sqlite3:///$arrConf[elastix_dbdir]/trunk.db");

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = saveNewCheckOut($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Ast, $pDB_CDR, $pDB_Set, $pDB_Rat, $pDB_Trk, $arrConf, $arrLang);
            break;
        default: // view_form
            $content = viewFormCheckOut($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Ast, $pDB_CDR, $pDB_Set, $pDB_Rat, $pDB_Trk, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function check_trunk_billing(&$pDB_Trk, $arrLang)
{
	$pDB_trunk_Billing = new paloSantoCheckOut($pDB_Trk);
	$is_trunk = $pDB_trunk_Billing -> loadtrunk();
	$popup = "";
	$message_trunk = $arrLang["No trunk Billable !!!"];
    if(!isset($is_trunk['0']))
		$popup = '<script type="text/javascript">Popup_Alert("'.$message_trunk.'")</script>';
	return $popup;
}

function viewFormCheckOut($smarty, $module_name, $local_templates_dir, &$pDB, &$pDB_Ast, &$pDB_CDR, &$pDB_Set, &$pDB_Rat, &$pDB_Trk, $arrConf, $arrLang)
{
    $pSQLite = new paloSantoCheckOut($pDB_Rat);
    $arrRate = $pSQLite->loadRates();

    $pCheckOut = new paloSantoCheckOut($pDB);
    $arrFormCheckOut = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormCheckOut);
    $pRat  = new paloSantoCheckOut($pDB_Rat);

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
        $dataCheckOut = $pCheckOut->getCheckOutById($id);
        if(is_array($dataCheckOut) & count($dataCheckOut)>0)
            $_DATA = $dataCheckOut;
        else{
            $smarty->assign("mb_title", $arrLang["Error get Data"]);
            $smarty->assign("mb_message", $pCheckOut->errMsg);
        }
    }

    $_DATA['date'] = date("Y-m-d H:i:s");

    $smarty->assign("SAVE", $arrLang["Save"]);
    $smarty->assign("EDIT", $arrLang["Edit"]);
    $smarty->assign("CANCEL", $arrLang["Cancel"]);
    $smarty->assign("REQUIRED_FIELD", $arrLang["Required field"]);
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("title",_tr("Check Out"));
    $smarty->assign("icon","/modules/$module_name/images/icone.png");
	$smarty->assign("popup",check_trunk_billing($pDB_Trk, $arrLang));

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["CheckOut"], $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function remoteActionControl($url)
{
	if(isset($url)){
		$rac 		= fopen($url, "r");
		$getRac 	= fread($rac,1024);
		fclose($rac);
	}
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

function TimeCall($second)
{
	$temp = $second % 3600;
	$time[0] = ( $second - $temp ) / 3600 ;
	$time[2] = $temp % 60 ;
	$time[1] = ( $temp - $time[2] ) / 60;
	$result = $time[0]." h ".$time[1]." m ".$time[2]." s";
	if ($time[0] == 0)
		$result = $time[1]." m ".$time[2]." s";
       return $result;
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

function saveNewCheckOut($smarty, $module_name, $local_templates_dir, &$pDB, &$pDB_Ast, &$pDB_CDR, &$pDB_Set, &$pDB_Rat, &$pDB_Trk, $arrConf, $arrLang)
{
    include "modules/$module_name/libs/billing_lib.php";
    $pCur    		= new paloSantoCheckOut($pDB_Set);
    $pSQLite 		= new paloSantoCheckOut($pDB_Rat);
    $pTrunk  		= new paloSantoCheckOut($pDB_Trk);
    $curr    		= $pCur->loadCurrency();
    $arrRate 		= $pSQLite->loadRates();
    $arrDef_Rate 	= $pSQLite->load_Def_Rate();
    $arrTrk  		= $pTrunk->loadTrunk();

    $smarty->assign("Call", $arrLang['Call']);
    $smarty->assign("Display", $arrLang['Display']);
    $smarty->assign("Total", $arrLang['Total']);
    $smarty->assign("title",_tr("Check Out"));
    $smarty->assign("icon","/modules/$module_name/images/icone.png");

    $pCheckOut = new paloSantoCheckOut($pDB);
    $pCheckOut_Ast = new paloSantoCheckOut($pDB_Ast);
    $pCheckOut_CDR = new paloSantoCheckOut($pDB_CDR);

    $arrFormCheckOut = createFieldForm($arrLang, $pDB);
    $oForm = new paloForm($smarty,$arrFormCheckOut);
    $_DATA = $_POST;

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
        $content = viewFormCheckOut($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Ast, $pDB_CDR, $pDB_Set, $pDB_Rat, $pDB_Trk, $arrConf, $arrLang);
    }
    else{
        $arrGroup		= $pCheckOut->getGroupCheckOut();

	 $Total_billings = 0;

	 // It's a group or one room?
	 //---------------------------
	 $CheckOutGuest[0]	= $_DATA['room'];
	 if ($_DATA['group'] != 0){
	 	$Group		= $arrGroup[$_DATA['group']-1]['groupe'];
        	$where        = "where groupe = '".$Group."'";
        	$arrGroup     = $pCheckOut->getCheckOut('rooms', $where);
		foreach($arrGroup as $key_Group => $value_Group){
			$CheckOutGuest[$key_Group] = $value_Group['id'];
		}
	 }

        foreach($CheckOutGuest as $key_room => $room_index)
	 {
        $_DATA['room']= $room_index;

        $pRoom 		= new paloSantoCheckOut($pDB); // <------------- inutile  $pRoom = $pCheckOut !!!
        $where 		= "where id = '".$_DATA['room']."'";
        $arrRoom 	= $pRoom->getCheckOut('rooms', $where);
        $arrExt 	= $arrRoom['0'];

	// Sending url to R.A.C.
	//----------------------

	$rac_url	= $arrExt['RACO'];
	remoteActionControl($rac_url);

    // Update room : The room was busy and now it's free and not clean.
	// In same time, deleting the guest name and group.
    //---------------------------------------------
    $value['free'] 		= "'1'";
    $value['clean'] 	= "'0'";
	$value['groupe'] 	= null;
	$value['guest_name']= null;
    $value['mini_bar'] 	= null;
    $where 				= "id = '".$_DATA['room']."'";
    $arrUpdateRoom 		= $pRoom->updateQuery('rooms', $value, $where);

    // Modify the checkout date following 'when' selection.
    //----------------------------------------------------

        if ($_DATA['When'] == '0')
        	$date_co 	= date("Y-m-d H:i:s");	// Today
        if ($_DATA['When'] == '2')
		$date_co 	= $_DATA['date'];		// Other Day
        if ($_DATA['When'] != '1')
			{
				$value_co['date_co']= "'".$date_co."'";    
				$where 				= "room_id = '".$_DATA['room']."' and status = '1'";
				$arrUpdateGuest 	= $pCheckOut->updateQuery('register', $value_co, $where);
			}

        // Lock the extension after checkout or not.
        //---------------------------------------------
        $arrConfig 		= $pRoom->getCheckOut('config', '');
        $arrLock 		= $arrConfig['0'];

        $cmd 			= "/usr/sbin/asterisk -rx 'database put LOCKED ".$arrExt['extension']." 0'";
	 if ( $arrLock['locked'] == "1")
        	$cmd 		= "/usr/sbin/asterisk -rx 'database put LOCKED ".$arrExt['extension']." ".$arrLock['locked']."'";
        exec($cmd);

	 // Remove all Wakeup files 
     //-------------------------
	 rm_wakeup($arrExt['extension']);

	 // Disable DND if the room is still on DND during checkout.
	 //---------------------------------------------------------
    $cmd 		 		= "/usr/sbin/asterisk -rx 'database del DND ".$arrExt['extension']."'";
    exec($cmd);
		
	// Replace the guest name.by the room name 
	//------------------------------------------------------
	$cmd 			= "asterisk -rx 'database put AMPUSER/{$arrExt['extension']} cidname \"{$arrExt['room_name']}\"'";
	exec($cmd);

    // Delete account code extension into Freepbx data
    //----------------------------------------------------------------
    $value_rl['value']  = "'true'";
    $where              = "variable = 'need_reload';";
    $arrReload          = $pCheckOut_Ast->updateQuery('admin',$value_rl, $where);

    $value_ac['data']   = "''";
    $where              = "id = '".$arrExt['extension']."' and keyword = 'accountcode';";
    $arrAccount         = $pCheckOut_Ast->updateQuery('sip',$value_ac, $where);
    $arrAccount         = $pCheckOut_Ast->updateQuery('dahdi',$value_ac, $where);

    $cmd="/var/lib/asterisk/bin/module_admin reload";
    exec($cmd);
		
	 // Delete voicemail 
	 //-----------------------

	 vm_clean($arrExt['extension']);

     // Find any room calls
     //---------------------------------------------
        
	 $where 	  	= "where room_id = '".$_DATA['room']."' and status = '1'";
     $arrConf_Guest = $pCheckOut->getCheckOut('register', $where);
	 $arrGuest      = $arrConf_Guest['0'];

    foreach($arrRate as $idx_prefix => $Rate_parameters)
	
	// Extracting rate parameters
    $dst_info = $dahdi_t = $misdn_t = $capi_t = "";
	foreach($arrTrk as $key_trk => $value_trk){
		$trunk = $value_trk['trunk'];
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

	 /*$where    = "WHERE channel LIKE '%/".$arrExt['extension']."%' and billsec > '0' and calldate > '".$arrGuest['date_ci']."'".
	                          " and calldate < '".$arrGuest['date_co']."' and disposition = 'ANSWERED' and accountcode ='".$arrGuest['guest_id']."'".
			    " and ( ".$dahdi_t.$misdn_t.$capi_t.$dst_info.")"; */
				
	  $where    = "WHERE billsec > '0' and calldate > '".$arrGuest['date_ci']."'".
 			    " and calldate < '".$arrGuest['date_co']."' and disposition = 'ANSWERED' and accountcode ='".$arrGuest['guest_id']."'".
			    " and ( ".$dahdi_t.$misdn_t.$capi_t.$dst_info.")"; 

     $arrCDR   = $pCheckOut_CDR->getCDR($where);

        $i=0;
	 if($arrCDR){
	       foreach($arrCDR as $key_cdr => $value_cdr){
	 		$calldate[$key_cdr] = $value_cdr['calldate'];
              	$dst[$key_cdr]  = $value_cdr['dst'];
			$billsec[$key_cdr]  = TimeCall($value_cdr['billsec']);
              	$i++;		
	 	}
	 }

     $strMsg= $arrLang["Checkout Done"];
	 $cmd	="/usr/sbin/asterisk -rx 'sip show peer ".$arrExt['extension']."' | grep Status | grep OK";

     if (!exec($cmd))
		$strMsg .= "<br><img src='modules/".$module_name."/images/warning.png'><br>".$arrLang['Warning'];

	 // Write the billing into the html file. 
     //-------------------------------------
     $arrConf   = $pCheckOut->getCheckOut('config', '');
     $Config    = $arrConf['0'];

	 $Billing_page = Billing_Header();

     $Bnumber=$arrGuest['id'].date('Ymd');

	 // Take the  billing number from remote instead of RoomX, in the case where remote folio is UP.
	 //-----------------------------------------------------------------------------------------------------------------------
	 if($arrGuest['remote_folio'] != "")
	 	$Bnumber = $arrGuest['remote_folio'];

	 $title 		= $arrLang["Billing at"]." ".date('D j M Y').$arrLang[" Number : "].$Bnumber;
	 $Billing_page 	= $Billing_page."".Title($title);
     $where 		= "where id = '".$arrGuest['guest_id']."'";
     $arrFor 		= $pCheckOut->getCheckOut('guest', $where);
     $For 			= $arrFor[0];
	 $for			= $For['first_name']." ".$For['last_name']."<br>\n".$For['address']."<br>\n".$For['cp']." ".$For['city']."<br>\n".$For['NIF'];
	 $company 		= "<br><b>".nl2br($Config['company'])."</b>";
	 $description	= "{$arrLang["Stayed from"]} {$arrGuest['date_ci']} {$arrLang["to"]} {$arrGuest['date_co']}.";
     $Billing_page 	= $Billing_page.Header_company($company,"<img src='".$Config['logo64']."'>",$for,$description);
     $Billing_page 	= $Billing_page."".Sale_title($arrLang["Sale"]);

	 // How many nights ?
     $arrNight = $pCheckOut->getNightNumber($arrGuest['date_ci'], $arrGuest['date_co'], $arrGuest['id']);
	 foreach($arrNight as $key => $value_night)
	 	$Night = $value_night;

     if( $Night == '0')
	 	$Night = "1";  	// A night should be calculated, even if there's no night. 
						// The room could be took in the day and be free in the same day. 

	 // Line with the number of Nights
	 //-------------------------------
    $where        = "where id = '".$arrExt['model']."'";
    $arrModel     = $pCheckOut->getCheckOut('models', $where);
	if($_DATA['asModel'] != '0')
		$arrExt['model'] = $_DATA['asModel'];
    $where        = "where room_model = '".$arrExt['model']."'";
    $arrModel     = $pCheckOut->getCheckOut('models', $where);
	$Model        = $arrModel[0];
    $add_guest 	  = 0;
	$star		  = "";
    if ( $arrGuest['num_guest'] == '1'){
        $add_guest = strval($Model['room_guest']);
	    $star 	 = " (+)";
	}
    $puht         = strval($Model['room_price']) + $add_guest;
    $patc         = ($puht*$Night)*(1+(strval($Model['room_vat'])/100));
	$vat          = $patc - ($puht*$Night) ;
	$vat_Nights   = $vat;
	$TT_Nights    = $patc; 
    $Billing_page = $Billing_page.Sale($arrLang["Nights with room's model: "].$Model['room_model'].$star, $Night, sprintf("%01.2f", $puht), sprintf("%01.2f", $vat), sprintf("%01.2f", $patc), $curr);


	 // There's a mini-bar?
	 //--------------------
	 if ($arrExt['mini_bar'] != "")
	 {
		$TT_MiniBar	= 0;
		$TT_MiniBar_v = 0;
		$Billing_page = $Billing_page.Sale("<b>".$arrLang['Mini Bar']." :</b>", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;");
		$minibar	= str_replace(" ","",$arrExt['mini_bar']);
		foreach(count_chars($minibar,1) as $val_min => $QT)
		{
		     $where        = "where digit = '".chr($val_min)."'";
        	 $arrMiniBar   = $pCheckOut->getCheckOut('minibar', $where);
			 $MiniBar      = $arrMiniBar[0];
			 $mb_vat	   = strval($QT)*strval($MiniBar['price']);
			 $mb_price	   = $mb_vat*(1+(strval($MiniBar['vat'])/100));
			 $TT_MiniBar   = $TT_MiniBar + $mb_price;
			 $TT_MiniBar_v = $TT_MiniBar_v + ($mb_price - $mb_vat);
			 $Billing_page = $Billing_page.Sale($MiniBar['label'], $QT, sprintf("%01.2f", $MiniBar['price']), sprintf("%01.2f",$mb_price - $mb_vat), sprintf("%01.2f", $mb_price), $curr);
		}
		$Billing_page = $Billing_page."</tbody></table><br>";	
	 }
	 else
	 {
		$Billing_page = $Billing_page.Sale("&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;", "&nbsp;");
	 	$Billing_page = $Billing_page."</tbody></table><br>";
	 }

	// The client has used the phone? 
    //--------------------------------
	 if ($i>0) 
		{
	  	if($_DATA['details'] == 'off')
			{
			$Total_Calls = 0;
	 		foreach($arrCDR as $key => $value_cdr)
				{
				// Ronding billsec.
				$billingsec = $value_cdr['billsec'];
				if ($arrLock['rounded'] == "1" && intval($value_cdr['billsec']/60) < ($value_cdr['billsec']/60) )
					$value_cdr['billsec'] = (1 + intval($value_cdr['billsec']/60))*60;

				for ($Scan_Rate = 0; $Scan_Rate < count($arrRate); $Scan_Rate++)
					{
					if (substr($value_cdr['dst'],0,strlen($arrRate[$Scan_Rate]['prefix'])) == $arrRate[$Scan_Rate]['prefix'] && 
					    substr($value_cdr['lastdata'],0,strlen($arrRate[$Scan_Rate]['trunk'])) == $arrRate[$Scan_Rate]['trunk'])
						{
						$price_rate = (($value_cdr['billsec'] / 60) * $arrRate[$Scan_Rate]['rate']) + $arrRate[$Scan_Rate]['rate_offset'];
						$price_rate = intval($price_rate*100)/100;
						$idx_rate   = $Scan_Rate;
						break;
						}
					else
						{
						$price_rate = (($value_cdr['billsec'] /60) * $arrDef_Rate['rate'] ) + $arrDef_Rate['rate_offset'];
						$price_rate = intval($price_rate*100)/100;
						$idx_rate   = $arrDef_Rate['id'];
						}
					}
				$Total_Calls = $Total_Calls + $price_rate;
	 			}
			$Total_Call_VAT = $Total_Calls / (1+(strval($Config['vat_1'])/100));
			$Total_Call_VAT = intval($Total_Call_VAT*100)/100;
			$Billing_page   = $Billing_page."".Sale_title($arrLang["Total Calls"]);
	 		$Billing_page   = $Billing_page.Sale($arrLang["There is some calls"], $i, " ----- ", sprintf("%01.2f", $Total_Call_VAT) , sprintf("%01.2f", $Total_Calls), $curr);
         		$Billing_page   = $Billing_page."</tbody></table><br>";
	  		}


	  // We want some details ?
	  //-------------------------------
	  	if($_DATA['details'] == 'on')
		{
			$key			= 0;
            $Total_Calls 	= 0; 
			$Billing_page = $Billing_page.Detail_table_Title();
	 		foreach($arrCDR as $key => $value_cdr)
			{
				// Ronding billsec.
				$billingsec = $value_cdr['billsec'];
				if ($arrLock['rounded'] == "1" && intval($value_cdr['billsec']/60) < ($value_cdr['billsec']/60) )
					$value_cdr['billsec'] = (1 + intval($value_cdr['billsec']/60))*60;

				for ($Scan_Rate = 0; $Scan_Rate < count($arrRate); $Scan_Rate++)
				{
					// Compare if the called num is matched with a prefix, from first position
					//------------------------------------------------------------------------
					if (substr($value_cdr['dst'],0,strlen($arrRate[$Scan_Rate]['prefix'])) == $arrRate[$Scan_Rate]['prefix'] && 
					    substr($value_cdr['lastdata'],0,strlen($arrRate[$Scan_Rate]['trunk'])) == $arrRate[$Scan_Rate]['trunk'])
						{
						$price_rate = (($value_cdr['billsec'] / 60) * $arrRate[$Scan_Rate]['rate']) + $arrRate[$Scan_Rate]['rate_offset'];
						$price_rate = intval($price_rate*100)/100;
						$idx_rate   = $Scan_Rate;
						break;
						}
					else
						{
						$price_rate = (($value_cdr['billsec'] / 60) * $arrDef_Rate['rate']) + $arrDef_Rate['rate_offset'];
						$price_rate = intval($price_rate*100)/100;
						$idx_rate   = $arrDef_Rate['id'];
						}
				}

				$Total_Calls    = $Total_Calls + $price_rate;
				$Total_Call_VAT = $Total_Calls / (1+(strval($Config['vat_1'])/100));
				$Total_Call_VAT = intval($Total_Call_VAT*100)/100;
				
				// Hidden digits haddeling
				//--------------------------------
				$Destination    = $value_cdr['dst'];
				if ( $arrRate[$idx_rate]['hided_digits'] > 0 ) {
					$Destination    = substr($value_cdr['dst'],0,-$arrRate[$idx_rate]['hided_digits']);
					$Destination    = $Destination.str_repeat("*",$arrRate[$idx_rate]['hided_digits']);
					}
				$Billing_page   = $Billing_page.Detail_table_Line($value_cdr['calldate']." - ".$arrRate[$idx_rate]['name'], $Destination, TimeCall($billingsec), sprintf("%01.2f", $price_rate), $curr);
	 		}
         	$Billing_page   = $Billing_page."</tbody></table><br>";
        	$Billing_page   = $Billing_page."".Sale_title($arrLang["Total Calls"]);
	 		$Billing_page   = $Billing_page.Sale($arrLang["There is some calls"], $i, " ----- ", sprintf("%01.2f", $Total_Call_VAT) , sprintf("%01.2f", $Total_Calls), $curr);
         	$Billing_page   = $Billing_page."</tbody></table><br>";
	 	}
		else
		{
        		$key=0;
             	 	$Total_Calls = 0; 
	 		foreach($arrCDR as $key => $value_cdr)
			{
				// Ronding billsec.
				if ($arrLock['rounded'] == "1" && intval($value_cdr['billsec']/60) < ($value_cdr['billsec']/60) )
					$value_cdr['billsec'] = (1 + intval($value_cdr['billsec']/60))*60;

				for ($Scan_Rate = 0; $Scan_Rate < count($arrRate); $Scan_Rate++)
				{
					if (substr($value_cdr['dst'],0,strlen($arrRate[$Scan_Rate]['prefix'])) == $arrRate[$Scan_Rate]['prefix'] && 
					    substr($value_cdr['lastdata'],0,strlen($arrRate[$Scan_Rate]['trunk'])) == $arrRate[$Scan_Rate]['trunk'])
						{
						$price_rate = ($value_cdr['billsec'] * ($arrRate[$Scan_Rate]['rate'] / 60)) + $arrRate[$Scan_Rate]['rate_offset'];
						$price_rate = intval($price_rate*100)/100;
						$idx_rate   = $Scan_Rate;
						break;
						}
					else
						{
						$price_rate = ($value_cdr['billsec'] * ($arrDef_Rate['rate'] / 60)) + $arrDef_Rate['rate_offset'];
						$price_rate = intval($price_rate*100)/100;
						$idx_rate   = $arrDef_Rate['id'];
						}
				}
				$Total_Calls    = $Total_Calls + $price_rate;
				$Total_Call_VAT = $Total_Calls / (1+(strval($Config['vat_1'])/100));
				$Total_Call_VAT = intval($Total_Call_VAT*100)/100;
	 		}		
	  	}
	 }

	 $money_advance= $arrGuest['money_advance'];
	 $ht		 = $vat_Nights + $Total_Call_VAT + $TT_MiniBar_v;
	 $total_bill	 = $TT_Nights  + $Total_Calls    + $TT_MiniBar;

    // remains to be paid
    $remains	 = $total_bill - $money_advance;

	$Billing_page = $Billing_page.Total_Billing(
				sprintf("%01.2f", $total_bill - $ht), 
				sprintf("%01.2f", $ht), 
				sprintf("%01.2f", $money_advance),
				sprintf("%01.2f", $total_bill), 
				sprintf("%01.2f", $remains), 
				$curr, 
				$arrLang);

    $name	     	 = $Bnumber.".html";
	 $name_path 	 = "/var/www/html/roomx_billing/".$name;

	 $Billing_file = fopen($name_path, 'w+');
	 fwrite($Billing_file,$Billing_page);
	 fclose($Billing_file);

	 // We want to send the billing by mail? 
	 //-------------------------------------

	 if($_DATA['sending_by_mail'] == 'on' && isset($For['mail']) )
		{
     	 	$headers = "From: ".$For['mail']."\n"
     			   ."Reply-To: ".$Config['mail']."\n"
     		  	   ."Content-Type: text/html; charset='iso-8859-1\n"
     			   ."Content-Transfer-Encoding: 8bit";

	 	$Billing_file = fopen($name_path, 'r');
		$contents = fread($Billing_file, filesize($name_path));
		fclose($Billing_file);
		$message = $contents;

     		if(mail($For['mail'], 'Your Billing', $message, $headers))
     			{
          			$strMsg .= " and mail sent.";
     			}
     			else
     			{
          			$strMsg .= " and <b>mail error!!!</b>";
     			} 
		 }

        // Put the register with the status 0.
        //------------------------------------
	 //

        $value_re['paid']   	= "'0'";		          
        if ($_DATA['paid'] == "on")		         	   
          $value_re['paid'] 	= "'1'";
        $value_re['status'] 	= "'0'";
        $value_re['billing_file']  = "'".$name."'";
        $value_re['total_room']    = "'".$patc."'";
	 if($mb_price != 0)
           $value_re['total_bar']  = "'".$mb_price."'";
	 if($Total_Calls != 0)
           $value_re['total_call'] = "'".$Total_Calls."'";
        $value_re['total_billing'] = "'".$total_bill."'";
        $where 			= "room_id = '".$arrExt['id']."' and status = '1'";
        $arrUpdateRoom 		= $pRoom->updateQuery('register', $value_re, $where);

	 // Making total of billing.
	 $Total_billings = $Total_billings + $total_bill;

	 // creating job to keeping rooms unavailable in the case where 'When' is different of today
	 //-----------------------------------------------------------------------------------------

	 if ($_DATA['When'] != '0') 
	 {
		if ($_DATA['When'] == "1")
			$date_co	   = $arrGuest['date_co'];
		// Formatting job date
		//--------------------
		$arrStr		   = array(' ',':');
	 	$date_job 		   = str_replace($arrStr,'-',$date_co);
              list($Y,$M,$D,$H,$I,$S) = split('-',$date_job);
		$date_co_job		   = $Y.$M.$D.$H.$I;

		// Creating job file.
		//-------------------
              $tmp_job		   = fopen("/var/www/html/roomx_billing/rx_job","a");
		$job_content		   = "/usr/bin/mysql -u root -p".obtenerClaveConocidaMySQL('root')." -s roomx -e \"UPDATE rooms SET free = '1' WHERE "."id = '".$arrExt['id']."';\"";
    		fwrite($tmp_job, $job_content); 
		fclose($tmp_job);		

		// Launching job
		//--------------
		$cmd 			   = "/usr/bin/at -t ".$date_co_job." < /var/www/html/roomx_billing/rx_job";
		exec($cmd);

		// Deleting temp file
		//-------------------
		unlink("/var/www/html/roomx_billing/rx_job");

		// Changing the room status to 0 (busy) until date_co will completed
		//-------------------------------------------------------------------
              $value_rooms['free']    = "'0'";
        	$where 		   = "id = '".$arrExt['id']."'";
        	$arrUpdateRoom 	   = $pRoom->updateQuery('rooms', $value_rooms, $where);		
	 }
		

       }	
        $smarty->assign("mb_message", $strMsg);
        $smarty->assign("call_number", $i);
        $smarty->assign("total", 
	 	sprintf("%01.2f",$Total_billings)." ".$curr." - ".$arrLang["money_advance"]." ".
	 	sprintf("%01.2f",$arrGuest["money_advance"])." ".$curr.", ".$arrLang["Remains to be paid"].": ".
	 	sprintf("%01.2f",$Total_billings - $arrGuest["money_advance"])." ".$curr );
        $smarty->assign("bil", "1");
	 $smarty->assign("bil_link", $name);

        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",$arrLang["CheckOut"], $_DATA);
        $content = viewFormCheckOut($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Ast, $pDB_CDR, $pDB_Set, $pDB_Rat, $pDB_Trk, $arrConf, $arrLang);        

    }
    return $content;
}

function createFieldForm($arrLang, &$pDB)
{
    $pRoom= new paloSantoCheckOut($pDB);
    $where = "where free = '0'";				// The Room is busy?
    $arrRoom=$pRoom->getCheckOut('rooms', $where);	//
    $arrGroup=$pRoom->getGroupCheckOut();
     
    foreach($arrRoom as $kR => $valueR)
    	$arrOptionsR[$valueR['id']] = $valueR['room_name'];

    if (!isset($valueR['room_name']))
    	$arrOptionsR = array( '1' => $arrLang['No Room!'] );

    $arrOptionsG = array( '0' => $arrLang['No Group'] );
    foreach($arrGroup as $kG => $valueG){
       $kG++;
    	$arrOptionsG[$kG] = $valueG['groupe'];
	}

    $arrOptionsDate = array( '0' => $arrLang['Today'],
				 '1' => $arrLang['Scheduled date'],
				 '2' => $arrLang['Other day']);

    $arrModel     = $pRoom->getCheckOut('models', "");
    $arrOptionsAsModel[0] = "-----";
    foreach($arrModel as $km => $vm)
	$arrOptionsAsModel[$vm['room_model']] = $vm['room_model'];

    $arrOptions_payment = array(
				'1' => $arrLang['Credit Card'],
				'2' => $arrLang['Cash'],
				'3' => $arrLang['Bank Check'],
				'4' => $arrLang['Transfer'],
				'5' => $arrLang['PayPal'],
				'6' => $arrLang['Other']
			     );

    $arrFields = array(
            "room"    => array(      "LABEL"                  => $arrLang["Room"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptionsR,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "asModel" => array(      "LABEL"                  => $arrLang["Billed as"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptionsAsModel,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
						  ),
            "When"    => array(      "LABEL"                  => $arrLang["When"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptionsDate,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "group"   => array(      "LABEL"                  => $arrLang["Group"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptionsG,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "paid"   => array(      "LABEL"                  => $arrLang["Paid"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "payment_mode"   => array(    "LABEL"                => $arrLang["payment_mode"],
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptions_payment,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
                                            ),
            "date"   => array(      "LABEL"                  => $arrLang["Date"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%Y-%m-%d %H:%M:%S","TIMEFORMAT" => "24"),
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
						  ),
            "details"   => array(      "LABEL"                  => $arrLang["Details"],
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "sending_by_mail"   => array(   "LABEL"                  => $arrLang["Sending by mail"],
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