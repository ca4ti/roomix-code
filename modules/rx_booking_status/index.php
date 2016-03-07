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
  $Id: index.php,v 1.1 2011-05-26 06:05:26 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
require_once("libs/jpgraph/jpgraph.php");
require_once("libs/jpgraph/jpgraph_gantt.php");

include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";

function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoBookingStatus.class.php";

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
            $content = saveNewBookingStatus($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default: // view_form
            $content = viewFormBookingStatus($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function viewFormBookingStatus($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pBookingStatus = new paloSantoBookingStatus($pDB);
    $arrFormBookingStatus = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormBookingStatus);

    //begin, Form data persistence to errors and other events.
    $_DATA  	  = $_POST;

    $action     = getParameter("action");
    $id         = getParameter("id");
    $smarty->assign("ID", $id); //persistence id with input hidden in tpl

    if($action=="view")
        $oForm->setViewMode();
    else if($action=="view_edit" || getParameter("save_edit"))
        $oForm->setEditMode();
    //end, Form data persistence to errors and other events.

    if($action=="view" || $action=="view_edit"){ // the action is to view or view_edit.
        $dataBookingStatus = $pBookingStatus->getBookingStatusById($id);
        if(is_array($dataBookingStatus) & count($dataBookingStatus)>0)
            $_DATA = $dataBookingStatus;
        else{
            $smarty->assign("mb_title", _tr("Error get Data"));
            $smarty->assign("mb_message", $pBookingStatus->errMsg);
        }
    }

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("IMG", "images/list.png");
    $smarty->assign("icon","/modules/$module_name/images/icone.png");

    $Cb 		= $pBookingStatus->Clean_booking();
    $arrBookingRooms = $pBookingStatus->getBookingStatus_Once();
    $today 		= $pBookingStatus->ToDay();

    $smarty->assign("BOOKING"," ");
    if (isset($arrBookingRooms[0])){
    	Booking_Cal("","",$arrBookingRooms,"modules/$module_name/images/Booking.png","Booking Rooms Calendar",$today);
    	$smarty->assign("BOOKING","<img src='modules/".$module_name."/images/Booking.png'>");
    }

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Booking Status"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewBookingStatus($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pBookingStatus = new paloSantoBookingStatus($pDB);
    $arrFormBookingStatus = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormBookingStatus);

    $_DATA      = $_POST;
    $date_start = $_DATA['date_start']." 00:00:00";
    $date_end   = $_DATA['date_end']." 23:59:59";

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
        $content = viewFormBookingStatus($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    else{
    	 $smarty->assign("SAVE", _tr("Save"));
    	 $smarty->assign("EDIT", _tr("Edit"));
    	 $smarty->assign("CANCEL", _tr("Cancel"));
    	 $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    	 $smarty->assign("IMG", "images/list.png");
    	 $Cb 		    = $pBookingStatus->Clean_booking();
    	 $arrBookingRooms = $pBookingStatus->getBookingStatus($date_start,$date_end);
    	 $today 	    = $pBookingStatus->ToDay();

        $smarty->assign("BOOKING","");
    	 if (isset($arrBookingRooms[0])){
    	 	Booking_Cal($date_start,$date_end,$arrBookingRooms,"modules/$module_name/images/Booking.png","Booking Rooms Calendar",$today);
    	 	$smarty->assign("BOOKING","<img src='modules/".$module_name."/images/Booking.png'>");
	 }

    	 $htmlForm        = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Booking Status"), $_DATA);
    	 $content         = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";
    }
    return $content;
}

function createFieldForm()
{
    $arrOptions = array('val1' => 'Value 1', 'val2' => 'Value 2', 'val3' => 'Value 3');

    $arrFields = array(
            "date_start"   => array(      "LABEL"                  => _tr("Date Start"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%Y-%m-%d"),
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "date_end"   => array(      "LABEL"                  => _tr("Date End"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%Y-%m-%d"),
                                            "VALIDATION_TYPE"        => "text",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),

            );
    return $arrFields;
}

function date_difference($date1, $date2)  
{
 $s = strtotime($date2)-strtotime($date1);
 $d = intval($s/86400)+1;  
 return "$d";
} 

function Booking_Cal($date_start, $date_end, $booking_rooms, $files_graph, $Title, $today) {

	$dayNumber = date_difference($date_start, $date_end);

	$graph = new GanttGraph(900);
	$graph->SetMarginColor('blue:1.7');

	$graph->SetColor('white');

	$graph->SetBackgroundGradient('navy','white',GRAD_HOR,BGRAD_MARGIN);
	$graph->scale->hour->SetBackgroundColor('lightyellow:1.5');
	$graph->scale->hour->SetFont(FF_FONT1);
	$graph->scale->day->SetBackgroundColor('lightyellow:1.5');
	$graph->scale->day->SetFont(FF_FONT1,FS_BOLD);

	$graph->title->Set($Title);
	$graph->title->SetColor('white');
	//$graph->title->SetFont(FF_FONT1,FS_BOLD,14);
	if ($dayNumber == 1 )
		$graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH );
	if ($dayNumber >= 2 && $dayNumber < 7 )
              $graph->ShowHeaders(GANTT_HHOUR| GANTT_HDAY | GANTT_HMONTH );
	if ($dayNumber >= 8 && $dayNumber < 45 )
              $graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH );
	if ($dayNumber >= 46 )
              $graph->ShowHeaders(GANTT_HWEEK | GANTT_HMONTH );

	$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
	$graph->scale->week->SetFont(FF_FONT1);
	$graph->scale->hour->SetIntervall(12);
	$graph->scale->hour->SetStyle(HOURSTYLE_HM24);
	if ($dayNumber == 1 )
       	$graph->scale->day->SetStyle(DAYSTYLE_SHORTDATE4);
	if ($dayNumber >= 2 && $dayNumber < 7 )
		$graph->scale->day->SetStyle(DAYSTYLE_SHORTDAYDATE1);
	if ($dayNumber >= 8 && $dayNumber < 40 )
       	$graph->scale->day->SetStyle(DAYSTYLE_SHORTDATE4);
	if ($dayNumber >= 41 )
       	$graph->scale->day->SetStyle(DAYSTYLE_ONELETTER);
		
	$vline = new GanttVLine($today);
	$vline->SetDayOffset(0.5);
	$graph->Add($vline);

    	foreach($booking_rooms as $key => $value){
		$data[$key] = array($key,$value['room_name'],$value['date_ci'],$value['date_co']);
    	}

	for($i=0; $i<count($data); ++$i) {
    		$bar = new GanttBar($data[$i][0],$data[$i][1],$data[$i][2],$data[$i][3],"",10);
    		$bar->SetPattern(BAND_RDIAG,"yellow");
    		$bar->SetFillColor("red");
    		$graph->Add($bar);
		}
       //$graph->SetDateRange($date_start,$date_end);
	$graph->Stroke($files_graph);
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