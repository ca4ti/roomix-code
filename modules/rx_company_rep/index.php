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
  $Id: index.php,v 1.1 2011-05-18 04:05:28 Franck Danard franckd@agmp.org Exp $ */
//include elastix framework
require_once("libs/jpgraph/jpgraph.php");
require_once "libs/jpgraph/jpgraph_bar.php";
require_once("libs/jpgraph/jpgraph_pie.php");
require_once("libs/jpgraph/jpgraph_pie3d.php");
require_once "libs/jpgraph/jpgraph_line.php";
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";




function _moduleContent(&$smarty, $module_name)
{
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoCompanyReport.class.php";

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


    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case "save_new":
            $content = saveNewCompanyReport($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        default: // view_form
            $content = viewFormCompanyReport($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}

function viewFormCompanyReport($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pCompanyReport = new paloSantoCompanyReport($pDB);
    $arrFormCompanyReport = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormCompanyReport);

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
        $dataCompanyReport = $pCompanyReport->getCompanyReportById($id);
        if(is_array($dataCompanyReport) & count($dataCompanyReport)>0)
            $_DATA = $dataCompanyReport;
        else{
            $smarty->assign("mb_title", _tr("Error get Data"));
            $smarty->assign("mb_message", $pCompanyReport->errMsg);
        }
    }

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("IMG", "images/list.png");
    //$smarty->assign("Graph", $graph->Stroke());

    $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Company Report"), $_DATA);
    $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    return $content;
}

function saveNewCompanyReport($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf)
{
    $pCompanyReport = new paloSantoCompanyReport($pDB);
    $arrFormCompanyReport = createFieldForm();
    $oForm = new paloForm($smarty,$arrFormCompanyReport);
    $_DATA = $_POST;

    $date_start = $_DATA['date_start'];
    $date_end = $_DATA['date_end'];

    $smarty->assign("SAVE", _tr("Save"));
    $smarty->assign("EDIT", _tr("Edit"));
    $smarty->assign("CANCEL", _tr("Cancel"));
    $smarty->assign("REQUIRED_FIELD", _tr("Required field"));
    $smarty->assign("IMG", "images/list.png");
    
    if ($date_start != "" and $date_end !="" ){
    switch ($_DATA['type_of_report'])
    {
	case 'Checkin Checkout':

		$arrGuestLeave = $pCompanyReport->getCheckInCompanyReport($date_start,$date_end);

		foreach ($arrGuestLeave as $day => $value) {
			foreach($value as $key => $val) {
			switch ($key) {
				case "Num_ci" :
					$data_y_ci[$day] = $val;
					//echo "Day : $day; Valeur : $val <br />\n";	
					break;
				case "DATE" :
					$data_x_ci[$day] = $val;
					//echo "data_x_ci : $day; Valeur : $val <br />\n";	
					break;
			}

			}	
		}

		$arrGuestLeave = $pCompanyReport->getCheckOutCompanyReport($date_start,$date_end);

		foreach ($arrGuestLeave as $day => $value) {
			foreach($value as $key => $val) {
			switch ($key) {
				case "Num_co" :
					$data_y_co[$day] = $val;
					//echo "Day : $day; Valeur : $val <br />\n";	
					break;
				case "DATE" :
					$data_x_co[$day] = $val;
					//echo "data_x_co : $day; Valeur : $val <br />\n";	
					break;
			}

			}	
		}

		CreateTwinGraph($data_x_ci,$data_y_ci,$data_x_co,$data_y_co,"modules/$module_name/images/CheckInOut.png","CheckIn - CheckOut Graph","Date","Rooms");
		$smarty->assign("CheckInOutGraph","modules/$module_name/images/CheckInOut.png");

		break;
    	default:
       	$where = "";
    }
    }

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
        $content = viewFormCompanyReport($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
    }
    else{
        //NO ERROR, HERE IMPLEMENTATION OF SAVE
        $htmlForm = $oForm->fetchForm("$local_templates_dir/form.tpl",_tr("Company Report"), $_DATA);
        $content = "<form  method='POST' style='margin-bottom:0;' action='?menu=$module_name'>".$htmlForm."</form>";

    }

    return $content;
}

function CreateCkeckGraph($data_x,$data_y,$files_graph, $title, $Xlabel, $Ylabel){

		$graph  = new Graph(350, 250);  

		$graph->SetScale("textlin",0,5);
		$bplot  = new BarPlot($data_y);
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
 

		$bplot->SetFillGradient("navy","lightsteelblue",GRAD_HOR);
		$graph->Add($bplot);

		$graph->title->Set($title);
		$graph->xaxis->title->Set($Xlabel);
		$graph->yaxis->title->Set($Ylabel);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

		$graph->xaxis->SetTickLabels($data_x);
		$graph->xaxis->SetLabelAngle(90);

		$graph->Stroke($files_graph);
}

function CreateTwinGraph($data1x,$data1y,$data2x,$data2y,$files_graph, $title, $Xlabel, $Ylabel){

		$graph = new Graph(350,400,"auto");    
		$graph->SetScale("textint");
		
		$graph->SetMarginColor('white');
		$graph->SetFrame(false);
		
		// Create the bar plots
		$b1plot = new BarPlot($data1y);
		$b1plot->SetFillGradient("navy","lightsteelblue",GRAD_HOR);

		$b2plot = new BarPlot($data2y);
		$b2plot->SetFillGradient("orange","yellow",GRAD_HOR);
 
		$bplot = new GroupBarPlot(array($b1plot,$b2plot));

		$graph->Add($bplot);
		$graph->title->Set($title);
		$graph->xaxis->SetTitleSide(SIDE_TOP); 
		$graph->xaxis->title->Set($Xlabel,'low');
		$graph->yaxis->title->Set($Ylabel);
		$graph->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
		$graph->xaxis->SetLabelSide(SIDE_DOWN);
		$graph->xaxis->SetLabelMargin(5);
		$graph->xaxis->SetTickLabels($data1x);
		$graph->xaxis->SetLabelAngle(90);

		$graph->Stroke($files_graph);
}

function createFieldForm()
{
    $arrOptions = array('Checkin Checkout' => 'Checkin Checkout', 
			   'val2' 	   => 'Value 2', 
			   'val3' 	   => 'Value 3');

    $arrFields = array(
            "date_start"   => array(      "LABEL"                  => _tr("Date Start"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%Y-%m-%d","TIMEFORMAT" => "24"),
                                            "VALIDATION_TYPE"        => "",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "date_end"   => array(      "LABEL"                  => _tr("Date End"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "DATE",
                                            "INPUT_EXTRA_PARAM"      => array("TIME" => true, "FORMAT" => "%Y-%m-%d","TIMEFORMAT" => "24"),
                                            "VALIDATION_TYPE"        => "",
                                            "EDITABLE"               => "si",
                                            "VALIDATION_EXTRA_PARAM" => ""
                                            ),
            "type_of_report"   => array(      "LABEL"                  => _tr("Type of Report"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $arrOptions,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => "",
                                            "EDITABLE"               => "si",
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