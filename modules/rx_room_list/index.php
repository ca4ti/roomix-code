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

    //actions
    $action   = getAction();

    $content = "";

    switch($action){
        default:
            $content = reportRoomList($smarty, $module_name, $local_templates_dir, $pDB, $pDB_Trk, $pDB_CDR, $arrConf, $arrLang);
            break;
    }
    return $content;
}

function findCalls($extension, $date_ci, $guest_id, $pDB, $pDB_Trk, $pDB_CDR, $arrConf)
{
    $pRoomList 	= new paloSantoRoomList($pDB);
    $pTrunk  	 	= new paloSantoRoomList($pDB_Trk);
    $pRoomCDR 	= new paloSantoRoomList($pDB_CDR);
    $arrTrk  	 	= $pTrunk->loadTrunk();

        // Find any room calls
        //---------------------------------------------
        
        $dst_info = $dahdi_t = $misdn_t = $capi_t = "";
 	 foreach($arrTrk as $key_trk => $value_trk){
		$trunk = $value_trk['trunk'];
		if(substr($trunk,0,strlen('DAHDI')) == 'DAHDI')
			$dahdi_t = "dstchannel LIKE '%DAHDI%' OR ";
		if(substr($trunk,0,strlen('mISDN')) == 'mISDN')
			$misdn_t = "dstchannel LIKE '%mISDN%' OR ";
		if(substr($trunk,0,strlen('CAPI')) == 'CAPI')
			$capi_t  = "dstchannel LIKE '%CAPI%' OR ";
		$condition = "substr(cdr.dstchannel,1,length('$trunk')) = '".$trunk."'";
		if( $key_trk < (count($arrTrk)-1)){
			$condition = "substr(cdr.dstchannel,1,length('$trunk')) = '".$trunk."' OR ";
		}
		$dst_info = $dst_info.$condition;
	 }
	 $date_co	  = date('Y-m-d H:i:s');

	 $where         = "WHERE channel LIKE '%/".$extension."%' and billsec > '0' and calldate > '".$date_ci."'".
 			    " and calldate < '".$date_co."' and disposition = 'ANSWERED' and accountcode ='".$guest_id."'".
			    " and ( ".$dahdi_t.$misdn_t.$capi_t.$dst_info.");";
        $arrCDR = $pRoomCDR->getCDR($where);

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

    //begin grid parameters
    $oGrid  = new paloSantoGrid($smarty);
    $totalRoomList = $pRoomList->getNumRoomList($filter_field, $filter_value);

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
    $enable  = "<img src='modules/".$module_name."/images/1.png'>";
    $disable = "<img src='modules/".$module_name."/images/0.png'>";

    $ok  = array("0" => $disable, "1" => $enable);

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
           $cmd="asterisk -rx 'sip show peer ".$value['extension']."' | grep Status | grep OK";
	    if (!exec($cmd))
	    	$warning= "<img src='modules/".$module_name."/images/warning.png' border='0'>";

    	    // DND is YES ?
    	    //-------------
    	    $dnd = "<img src='modules/".$module_name."/images/dnd.png'>";

    	    $cmd = "asterisk -rx 'database show DND ".$value['extension']."' | grep YES ";
	    $details 	= $value['room_name'];
	    $ext	= $value['extension'];
    	    if (!exec($cmd))
    		$dnd = "<img src='modules/".$module_name."/images/d.png'>";
    	    $id_room	 = $pRoomList->getRoomListByName($details);
    	    $Register_Det	= $pRoomList->getRegisterByRoomId($id_room['id']);
           $Add_Guest	= $Register_Det['num_guest'];
	    $nb_calls		= findCalls($id_room['extension'], $Register_Det['date_ci'], $Register_Det['guest_id'],$pDB, $pDB_Trk, $pDB_CDR, $arrConf);
	    $Call_info	= "";
	    if($nb_calls > 0)
	    	$Call_info	= $arrLang["The guest has used the phone with "].$nb_calls.$arrLang[" calls"];
	    $msg_add_guest	= $arrLang["Additional guest"]."\\n";
	    if($Add_Guest == 0)
	    	$msg_add_guest= "";
           $msgResponse 	= $arrLang["Guest present from"].$Register_Det['date_ci'].$arrLang[" To "].$Register_Det['date_co']."\\n".$msg_add_guest.$Call_info;

	    if(!isset($Register_Det['date_ci']))
           	$msgResponse 	= $arrLang["None"];
	    $arrTmp[0] 	= "<img src='modules/".$module_name."/images/info.png' border='0' onclick='FindDetails(\"$msgResponse\")'>\n";
	    $arrTmp[1] 	= "<b>".$arrLang['free']."</b>";
 	    if ($value['guest_name'] != "")
	    	$arrTmp[1] 	= $value['guest_name'];
	    $arrTmp[2] 	= $value['room_name'];	
	    $arrTmp[3] 	= $value['extension']." ".$warning;
	    $arrTmp[4] 	= $value['model'];
	    $arrTmp[5] 	= $value['groupe'];
	    $arrTmp[6] 	= $ok[$value['free']];
	    $arrTmp[7] 	= $ok[$value['clean']];
	    $arrTmp[8] 	= $minibar;
	    $arrTmp[9] 	= $dnd;
           $arrData[] 	= $arrTmp;
        }
    }


    $arrGrid = array("title"    => $arrLang["Room List"],
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
			6 => array("name"      => $arrLang["Free"],
                                   "property1" => ""),
			7 => array("name"      => $arrLang["Clean"],
                                   "property1" => ""),
			8 => array("name"      => $arrLang["Mini bar"],
                                   "property1" => ""),
			9 => array("name"      => $arrLang["DND"],
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
	    "guest_name"      => $arrLang["Name"],
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
    else
        return "report"; //cancel
}
?>